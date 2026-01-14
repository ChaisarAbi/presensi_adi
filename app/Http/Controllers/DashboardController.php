<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Permission;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Dashboard for admin
     */
    public function admin(Request $request)
    {
        // Handle keep-alive requests (just return success)
        if ($request->has('keep_alive')) {
            return response()->json(['status' => 'alive', 'timestamp' => now()->format('H:i:s')]);
        }
        
        $totalStudents = Student::count();
        $totalTeachers = User::where('role', 'guru')->count();
        $todayAttendances = Attendance::whereDate('tanggal', today())->count();
        $pendingPermissions = Permission::where('status', 'Pending')->count();
        
        // Get today's attendance statistics for admin (all classes)
        $todayAttendancesQuery = Attendance::whereDate('tanggal', today());
        
        // Count students who are present (Hadir Masuk or Hadir Pulang)
        $presentToday = (clone $todayAttendancesQuery)
            ->whereIn('status', ['Hadir Masuk', 'Hadir Pulang'])
            ->count();
            
        // Count students who are absent (Tidak Hadir)
        $absentToday = (clone $todayAttendancesQuery)
            ->where('status', 'Tidak Hadir')
            ->count();
            
        // Count students who are late (Terlambat)
        $lateToday = (clone $todayAttendancesQuery)
            ->where('status', 'Terlambat')
            ->count();
            
        // Count students with permission (Izin)
        $permissionToday = (clone $todayAttendancesQuery)
            ->where('status', 'Izin')
            ->count();

        // Get recent activities
        $recentActivities = $this->getRecentActivities();

        // If AJAX request for refresh
        if ($request->ajax() || $request->has('refresh')) {
            return response()->json([
                'totalStudents' => $totalStudents,
                'totalTeachers' => $totalTeachers,
                'todayAttendances' => $todayAttendances,
                'pendingPermissions' => $pendingPermissions,
                'presentToday' => $presentToday,
                'absentToday' => $absentToday,
                'lateToday' => $lateToday,
                'permissionToday' => $permissionToday,
                'timestamp' => now()->format('H:i:s'),
                'recentActivities' => $this->getRecentActivities()
            ]);
        }

        return view('dashboard.admin', compact(
            'totalStudents',
            'totalTeachers',
            'todayAttendances',
            'pendingPermissions',
            'presentToday',
            'absentToday',
            'lateToday',
            'permissionToday',
            'recentActivities'
        ));
    }

    /**
     * Get recent activities for dashboard
     */
    private function getRecentActivities()
    {
        $activities = [];
        
        // Get recent attendances (last 2 hours)
        $recentAttendances = Attendance::with(['student', 'student.user'])
            ->where('created_at', '>=', now()->subHours(2))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        foreach ($recentAttendances as $attendance) {
            $studentName = $attendance->student->user->name ?? 'Siswa';
            $timeAgo = $attendance->created_at->diffForHumans();
            
            $activities[] = [
                'type' => 'attendance',
                'icon' => 'bi-person-check',
                'color' => 'text-success',
                'time' => $timeAgo,
                'text' => "{$studentName} absen {$attendance->status}",
                'timestamp' => $attendance->created_at
            ];
        }
        
        // Get recent permissions (last 2 hours)
        $recentPermissions = Permission::with(['student', 'student.user'])
            ->where('created_at', '>=', now()->subHours(2))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        foreach ($recentPermissions as $permission) {
            $studentName = $permission->student->user->name ?? 'Siswa';
            $timeAgo = $permission->created_at->diffForHumans();
            
            $activities[] = [
                'type' => 'permission',
                'icon' => 'bi-clipboard-check',
                'color' => 'text-warning',
                'time' => $timeAgo,
                'text' => "{$studentName} mengajukan izin",
                'timestamp' => $permission->created_at
            ];
        }
        
        // Get recent QR generations (last 2 hours)
        $recentQRCodes = \App\Models\QrCode::where('created_at', '>=', now()->subHours(2))
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        
        foreach ($recentQRCodes as $qr) {
            $timeAgo = $qr->created_at->diffForHumans();
            
            $activities[] = [
                'type' => 'qr',
                'icon' => 'bi-qr-code',
                'color' => 'text-primary',
                'time' => $timeAgo,
                'text' => "QR Code baru digenerate",
                'timestamp' => $qr->created_at
            ];
        }
        
        // Sort by timestamp (newest first) and limit to 10
        usort($activities, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });
        
        return array_slice($activities, 0, 10);
    }

    /**
     * Dashboard for guru
     */
    public function guru(Request $request)
    {
        $user = Auth::user();
        $kelas = $user->kelas ?? 'Semua Kelas'; // Assuming teacher has kelas field
        
        // Handle keep-alive requests (just return success)
        if ($request->has('keep_alive')) {
            return response()->json(['status' => 'alive', 'timestamp' => now()->format('H:i:s')]);
        }
        
        // Get total students in the class
        $totalStudents = Student::when($kelas !== 'Semua Kelas', function ($query) use ($kelas) {
            return $query->where('kelas', $kelas);
        })->count();
        
        // Get today's attendance statistics
        $todayAttendancesQuery = Attendance::whereDate('tanggal', today())
            ->when($kelas !== 'Semua Kelas', function ($query) use ($kelas) {
                return $query->whereHas('student', function ($q) use ($kelas) {
                    $q->where('kelas', $kelas);
                });
            });
        
        // Count students who are present (Hadir Masuk or Hadir Pulang)
        $presentToday = (clone $todayAttendancesQuery)
            ->whereIn('status', ['Hadir Masuk', 'Hadir Pulang'])
            ->count();
            
        // Count students who are absent (Tidak Hadir)
        $absentToday = (clone $todayAttendancesQuery)
            ->where('status', 'Tidak Hadir')
            ->count();
            
        // Count students who are late (Terlambat)
        $lateToday = (clone $todayAttendancesQuery)
            ->where('status', 'Terlambat')
            ->count();
            
        // Count students with permission (Izin)
        $permissionToday = (clone $todayAttendancesQuery)
            ->where('status', 'Izin')
            ->count();
            
        // Students who haven't checked in yet
        $notCheckedIn = $totalStudents - ($presentToday + $absentToday + $lateToday + $permissionToday);
        
        $pendingPermissions = Permission::where('status', 'Pending')
            ->when($kelas !== 'Semua Kelas', function ($query) use ($kelas) {
                return $query->whereHas('student', function ($q) use ($kelas) {
                    $q->where('kelas', $kelas);
                });
            })
            ->count();

        return view('dashboard.guru', compact(
            'kelas',
            'absentToday',
            'pendingPermissions',
            'totalStudents',
            'presentToday',
            'lateToday',
            'permissionToday',
            'notCheckedIn'
        ));
    }

    /**
     * Dashboard for siswa/orang tua
     */
    public function siswa(Request $request)
    {
        // Handle keep-alive requests (just return success)
        if ($request->has('keep_alive')) {
            return response()->json(['status' => 'alive', 'timestamp' => now()->format('H:i:s')]);
        }
        
        $user = Auth::user();
        $student = $user->student;
        
        if (!$student) {
            abort(403, 'Anda tidak memiliki data siswa.');
        }
        
        // Today's attendance
        $todayAttendance = Attendance::where('student_id', $student->id)
            ->whereDate('tanggal', today())
            ->first();
            
        // Monthly attendance statistics
        $monthlyStats = Attendance::where('student_id', $student->id)
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->get()
            ->groupBy('status')
            ->map->count();
            
        // Pending permissions
        $pendingPermissions = Permission::where('student_id', $student->id)
            ->where('status', 'Pending')
            ->count();
            
        // Recent attendances
        $recentAttendances = Attendance::where('student_id', $student->id)
            ->orderBy('tanggal', 'desc')
            ->limit(10)
            ->get();
            
        // Recent permissions for tracking
        $recentPermissions = Permission::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.siswa', compact(
            'student',
            'todayAttendance',
            'monthlyStats',
            'pendingPermissions',
            'recentAttendances',
            'recentPermissions'
        ));
    }
}
