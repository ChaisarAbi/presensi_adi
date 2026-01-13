<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    /**
     * Show permission form
     */
    public function create()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(403, 'Anda bukan siswa.');
        }

        return view('permission.create', compact('student'));
    }

    /**
     * Store permission request
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return back()->with('error', 'Anda bukan siswa.');
        }

        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'alasan' => 'required|string|min:10',
            'foto_bukti' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'tanggal.required' => 'Tanggal izin wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'alasan.required' => 'Alasan dan keterangan wajib diisi.',
            'alasan.min' => 'Alasan dan keterangan minimal 10 karakter.',
            'foto_bukti.required' => 'Foto bukti wajib diupload.',
            'foto_bukti.image' => 'File harus berupa gambar.',
            'foto_bukti.mimes' => 'Format gambar harus JPG, PNG, JPEG, atau GIF.',
            'foto_bukti.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Pengajuan izin gagal. Periksa form Anda.');
        }

        // Upload foto bukti
        if ($request->hasFile('foto_bukti')) {
            $path = $request->file('foto_bukti')->store('permissions', 'public');
        }

        // Create permission
        Permission::create([
            'student_id' => $student->id,
            'tanggal' => $request->tanggal,
            'alasan' => $request->alasan,
            'foto_bukti' => $path,
            'status' => 'Pending',
        ]);

        return redirect()->route('dashboard.siswa')
            ->with('success', 'Pengajuan izin berhasil dikirim! Status: Pending. Tunggu verifikasi dari guru.');
    }

    /**
     * Show permission history
     */
    public function history()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(403, 'Anda bukan siswa.');
        }

        $permissions = Permission::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('permission.history', compact('permissions'));
    }

    /**
     * Show permissions for admin/guru (to approve/reject)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }

        $query = Permission::with(['student', 'student.user']);
        
        // Apply filters
        if ($request->has('kelas') && $request->kelas) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('kelas', $request->kelas);
            });
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('tanggal') && $request->tanggal) {
            $query->whereDate('tanggal', $request->tanggal);
        }
        
        $permissions = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Statistics
        $pendingCount = Permission::where('status', 'Pending')->count();
        $approvedCount = Permission::where('status', 'Disetujui')->count();
        $rejectedCount = Permission::where('status', 'Ditolak')->count();
        $totalCount = Permission::count();

        return view('permission.index', compact(
            'permissions',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'totalCount'
        ));
    }

    /**
     * Update permission status (approve/reject)
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Disetujui,Ditolak',
            'catatan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $permission = Permission::findOrFail($id);
        $oldStatus = $permission->status;
        $permission->update([
            'status' => $request->status,
        ]);

        // Import Attendance model
        $attendanceModel = new \App\Models\Attendance();
        
        // Check if attendance record already exists for this permission
        $existingAttendance = $attendanceModel::where('student_id', $permission->student_id)
            ->whereDate('tanggal', $permission->tanggal)
            ->first();
        
        if ($request->status === 'Disetujui') {
            // Create or update attendance record for "Izin"
            if ($existingAttendance) {
                $existingAttendance->update([
                    'status' => 'Izin',
                    'waktu' => $existingAttendance->waktu ?? '00:00:00',
                ]);
            } else {
                $attendanceModel::create([
                    'student_id' => $permission->student_id,
                    'tanggal' => $permission->tanggal,
                    'waktu' => '00:00:00',
                    'status' => 'Izin',
                ]);
            }
        } elseif ($request->status === 'Ditolak') {
            // Create or update attendance record for "Tidak Hadir"
            if ($existingAttendance) {
                $existingAttendance->update([
                    'status' => 'Tidak Hadir',
                    'waktu' => $existingAttendance->waktu ?? '00:00:00',
                ]);
            } else {
                $attendanceModel::create([
                    'student_id' => $permission->student_id,
                    'tanggal' => $permission->tanggal,
                    'waktu' => '00:00:00',
                    'status' => 'Tidak Hadir',
                ]);
            }
        }

        $statusText = $request->status === 'Disetujui' ? 'disetujui' : 'ditolak';
        return back()->with('success', "Izin berhasil $statusText!");
    }
}
