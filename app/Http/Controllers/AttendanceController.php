<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\QrCode;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Show QR scanner for attendance
     */
    public function showScanner()
    {
        return view('attendance.scanner');
    }

    /**
     * Handle QR scan for attendance masuk
     */
    public function scanMasuk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid.',
            ], 400);
        }

        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Anda bukan siswa.',
            ], 403);
        }

        // Validate QR token
        $qrCode = QrCode::where('token', $request->token)
            ->where('expired_at', '>', now())
            ->first();

        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau sudah kadaluarsa.',
            ], 400);
        }

        // Check if already attended today for masuk (including Terlambat)
        $existingAttendance = Attendance::where('student_id', $student->id)
            ->whereDate('tanggal', today())
            ->whereIn('status', ['Hadir Masuk', 'Terlambat'])
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi masuk hari ini.',
            ], 400);
        }

        // Check time (jam masuk: 07:00, batas keterlambatan: 09:00)
        $jamMasuk = '07:00:00';
        $batasKeterlambatan = '09:00:00';
        $currentTime = now()->format('H:i:s');

        $status = 'Hadir Masuk';
        
        if ($currentTime > $jamMasuk && $currentTime <= $batasKeterlambatan) {
            // Terlambat tapi masih dalam batas waktu
            $status = 'Terlambat';
        } elseif ($currentTime > $batasKeterlambatan) {
            // Sudah lewat batas keterlambatan
            return response()->json([
                'success' => false,
                'message' => 'Waktu absensi masuk sudah lewat. Batas keterlambatan sampai jam 09:00.',
            ], 400);
        }

        // Create attendance record
        Attendance::create([
            'student_id' => $student->id,
            'tanggal' => today(),
            'waktu' => now()->format('H:i:s'),
            'status' => $status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi masuk berhasil.',
        ]);
    }

    /**
     * Handle QR scan for attendance pulang
     */
    public function scanPulang(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid.',
            ], 400);
        }

        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Anda bukan siswa.',
            ], 403);
        }

        // Validate QR token
        $qrCode = QrCode::where('token', $request->token)
            ->where('expired_at', '>', now())
            ->first();

        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau sudah kadaluarsa.',
            ], 400);
        }

        // Check if already attended today for pulang
        $existingAttendance = Attendance::where('student_id', $student->id)
            ->whereDate('tanggal', today())
            ->where('status', 'Hadir Pulang')
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi pulang hari ini.',
            ], 400);
        }

        // Check time (jam pulang: 14:00)
        $jamPulang = '14:00:00';
        $currentTime = now()->format('H:i:s');

        if ($currentTime < $jamPulang) {
            return response()->json([
                'success' => false,
                'message' => 'Belum waktunya absensi pulang.',
            ], 400);
        }

        // Create attendance record
        Attendance::create([
            'student_id' => $student->id,
            'tanggal' => today(),
            'waktu' => now()->format('H:i:s'),
            'status' => 'Hadir Pulang',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi pulang berhasil.',
        ]);
    }

    /**
     * Show attendance history
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(403, 'Anda bukan siswa.');
        }

        // Query with filters
        $query = Attendance::where('student_id', $student->id);
        
        // Apply filters
        if ($request->has('bulan') && $request->bulan) {
            $query->whereMonth('tanggal', $request->bulan);
        }
        
        if ($request->has('tahun') && $request->tahun) {
            $query->whereYear('tanggal', $request->tahun);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $attendances = $query->orderBy('tanggal', 'desc')->paginate(20);
        
        // Calculate statistics
        $totalAttendances = Attendance::where('student_id', $student->id)->count();
        $hadirCount = Attendance::where('student_id', $student->id)
            ->whereIn('status', ['Hadir Masuk', 'Hadir Pulang'])
            ->count();
        $terlambatCount = Attendance::where('student_id', $student->id)
            ->where('status', 'Terlambat')
            ->count();
        $izinCount = Attendance::where('student_id', $student->id)
            ->where('status', 'Izin')
            ->count();
        $tidakHadirCount = Attendance::where('student_id', $student->id)
            ->where('status', 'Tidak Hadir')
            ->count();
        
        $stats = [
            'hadir' => $hadirCount,
            'terlambat' => $terlambatCount,
            'izin' => $izinCount,
            'tidak_hadir' => $tidakHadirCount,
            'total' => $totalAttendances,
        ];
        
        // Prepare chart data (last 6 months)
        $chartLabels = [];
        $chartData = [
            'hadir' => [],
            'terlambat' => [],
            'izin' => [],
            'tidak_hadir' => [],
        ];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('F');
            $year = $date->format('Y');
            
            $chartLabels[] = substr($month, 0, 3) . ' ' . $year;
            
            // Hadir count for this month
            $hadirMonth = Attendance::where('student_id', $student->id)
                ->whereMonth('tanggal', $date->month)
                ->whereYear('tanggal', $date->year)
                ->whereIn('status', ['Hadir Masuk', 'Hadir Pulang'])
                ->count();
            $chartData['hadir'][] = $hadirMonth;
            
            // Terlambat count for this month
            $terlambatMonth = Attendance::where('student_id', $student->id)
                ->whereMonth('tanggal', $date->month)
                ->whereYear('tanggal', $date->year)
                ->where('status', 'Terlambat')
                ->count();
            $chartData['terlambat'][] = $terlambatMonth;
            
            // Izin count for this month
            $izinMonth = Attendance::where('student_id', $student->id)
                ->whereMonth('tanggal', $date->month)
                ->whereYear('tanggal', $date->year)
                ->where('status', 'Izin')
                ->count();
            $chartData['izin'][] = $izinMonth;
            
            // Tidak hadir count for this month
            $tidakHadirMonth = Attendance::where('student_id', $student->id)
                ->whereMonth('tanggal', $date->month)
                ->whereYear('tanggal', $date->year)
                ->where('status', 'Tidak Hadir')
                ->count();
            $chartData['tidak_hadir'][] = $tidakHadirMonth;
        }

        return view('attendance.history', compact(
            'student',
            'attendances',
            'stats',
            'chartLabels',
            'chartData'
        ));
    }
}
