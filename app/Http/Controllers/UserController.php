<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with('student');
        
        // Apply filters
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('student', function($q2) use ($search) {
                      $q2->where('nis', 'like', "%{$search}%")
                         ->orWhere('kelas', 'like', "%{$search}%");
                  });
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Statistics
        $totalUsers = User::count();
        $adminCount = User::where('role', 'admin')->count();
        $guruCount = User::where('role', 'guru')->count();
        $siswaCount = User::where('role', 'siswa')->count();
        
        return view('users.index', compact(
            'users',
            'totalUsers',
            'adminCount',
            'guruCount',
            'siswaCount'
        ));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Determine user type from hidden field
        $userType = $request->input('user_type', 'admin_guru');
        
        if ($userType === 'siswa') {
            // Validation for siswa
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'nis' => 'required|string|unique:students,nis',
                'kelas' => 'required|string',
                'nama_ortu' => 'required|string',
                'kontak_ortu' => 'required|string',
            ], [
                'name.required' => 'Nama lengkap wajib diisi.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.unique' => 'Email sudah terdaftar.',
                'password.required' => 'Password wajib diisi.',
                'password.min' => 'Password minimal 8 karakter.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
                'nis.required' => 'NIS wajib diisi.',
                'nis.unique' => 'NIS sudah terdaftar.',
                'kelas.required' => 'Kelas wajib diisi.',
                'nama_ortu.required' => 'Nama orang tua wajib diisi.',
                'kontak_ortu.required' => 'Kontak orang tua wajib diisi.',
            ]);
        } else {
            // Validation for admin/guru
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required|in:admin,guru',
            ], [
                'name.required' => 'Nama lengkap wajib diisi.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.unique' => 'Email sudah terdaftar.',
                'password.required' => 'Password wajib diisi.',
                'password.min' => 'Password minimal 8 karakter.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
                'role.required' => 'Role wajib dipilih.',
                'role.in' => 'Role tidak valid.',
            ]);
        }

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $userType === 'siswa' ? 'siswa' : $request->role,
        ]);

        // If student, create student record
        if ($userType === 'siswa') {
            Student::create([
                'user_id' => $user->id,
                'nis' => $request->nis,
                'kelas' => $request->kelas,
                'nama_ortu' => $request->nama_ortu,
                'kontak_ortu' => $request->kontak_ortu,
            ]);
        }

        $roleName = $user->role === 'admin' ? 'Admin' : ($user->role === 'guru' ? 'Guru' : 'Siswa');
        
        return redirect()->route('users.index')
            ->with('success', "User $roleName '$request->name' berhasil ditambahkan.");
    }

    /**
     * Show the form for editing a user
     */
    public function edit($id)
    {
        $user = User::with('student')->findOrFail($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,guru,siswa',
            'nis' => 'sometimes|required_if:role,siswa|string|unique:students,nis,' . ($user->student ? $user->student->id : 'NULL') . ',id',
            'kelas' => 'sometimes|required_if:role,siswa|string',
            'nama_ortu' => 'sometimes|required_if:role,siswa|string',
            'kontak_ortu' => 'sometimes|required_if:role,siswa|string',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
            'nis.required_if' => 'NIS wajib diisi untuk siswa.',
            'nis.unique' => 'NIS sudah terdaftar.',
            'kelas.required_if' => 'Kelas wajib diisi untuk siswa.',
            'nama_ortu.required_if' => 'Nama orang tua wajib diisi untuk siswa.',
            'kontak_ortu.required_if' => 'Kontak orang tua wajib diisi untuk siswa.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Update user
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        // Update password if provided
        if ($request->password) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Handle student record
        if ($request->role === 'siswa') {
            if ($user->student) {
                $user->student->update([
                    'nis' => $request->nis,
                    'kelas' => $request->kelas,
                    'nama_ortu' => $request->nama_ortu,
                    'kontak_ortu' => $request->kontak_ortu,
                ]);
            } else {
                Student::create([
                    'user_id' => $user->id,
                    'nis' => $request->nis,
                    'kelas' => $request->kelas,
                    'nama_ortu' => $request->nama_ortu,
                    'kontak_ortu' => $request->kontak_ortu,
                ]);
            }
        } else {
            // If role changed from siswa to non-siswa, delete student record
            if ($user->student) {
                $user->student->delete();
            }
        }

        return redirect()->route('users.index')
            ->with('success', "User {$request->name} berhasil diperbarui.");
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $userName = $user->name;
        
        // Delete student record if exists
        if ($user->student) {
            $user->student->delete();
        }
        
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "User {$userName} berhasil dihapus.");
    }

    /**
     * Toggle user status (active/inactive)
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "User {$user->name} berhasil {$status}.");
    }
}