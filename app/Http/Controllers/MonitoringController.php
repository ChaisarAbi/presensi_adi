<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    /**
     * Show real-time monitoring for admin/guru
     */
    public function index(Request $request)
    {
        // Increase memory limit for large data processing
        ini_set('memory_limit', '256M');
        set_time_limit(120);

        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }

        $kelas = $request->get('kelas', 'all');
        $tanggal = $request->get('tanggal', today()->format('Y-m-d'));

        // Get class list for filter (cached)
        $kelasList = Student::select('kelas')->distinct()->pluck('kelas');

        // Get total students count
        $totalStudents = Student::when($kelas !== 'all', function ($q) use ($kelas) {
            $q->where('kelas', $kelas);
        })->count();

        // Get attendance statistics in single query for efficiency
        $attendanceStats = Attendance::whereDate('tanggal', $tanggal)
            ->when($kelas !== 'all', function ($q) use ($kelas) {
                $q->whereHas('student', function ($q2) use ($kelas) {
                    $q2->where('kelas', $kelas);
                });
            })
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $hadirMasuk = $attendanceStats['Hadir Masuk'] ?? 0;
        $terlambat = $attendanceStats['Terlambat'] ?? 0;
        $hadirPulang = $attendanceStats['Hadir Pulang'] ?? 0;
        $izin = $attendanceStats['Izin'] ?? 0;
        $tidakHadir = $attendanceStats['Tidak Hadir'] ?? 0;

        // Get students with attendance data (paginated for large classes)
        $students = Student::with(['user' => function($query) {
                $query->select('id', 'name');
            }])
            ->when($kelas !== 'all', function ($q) use ($kelas) {
                $q->where('kelas', $kelas);
            })
            ->select('id', 'user_id', 'nis', 'kelas')
            ->orderBy('nis')
            ->paginate(50); // Paginate to reduce memory usage

        // Get attendance data for these students
        $studentIds = $students->pluck('id')->toArray();
        $studentAttendances = Attendance::whereIn('student_id', $studentIds)
            ->whereDate('tanggal', $tanggal)
            ->select('student_id', 'status', 'waktu')
            ->get()
            ->keyBy('student_id');

        // Attach attendance data to students
        $students->getCollection()->transform(function ($student) use ($studentAttendances) {
            $attendance = $studentAttendances->get($student->id);
            $student->attendance_today = $attendance;
            $student->status = $attendance ? $attendance->status : 'Belum Absen';
            $student->waktu = $attendance ? $attendance->waktu : null;
            return $student;
        });

        return view('monitoring.index', compact(
            'students',
            'kelasList',
            'kelas',
            'tanggal',
            'totalStudents',
            'hadirMasuk',
            'terlambat',
            'hadirPulang',
            'izin',
            'tidakHadir'
        ));
    }

    /**
     * Get attendance data for chart (API)
     */
    public function chartData(Request $request)
    {
        // Increase memory limit for large data processing
        ini_set('memory_limit', '256M');
        set_time_limit(120);

        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'guru'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $kelas = $request->get('kelas', 'all');
        $tanggal = $request->get('tanggal', today()->format('Y-m-d'));
        $status = $request->get('status', '');

        // Get attendance statistics in single query for efficiency
        $attendanceStats = Attendance::whereDate('tanggal', $tanggal)
            ->when($kelas !== 'all', function ($q) use ($kelas) {
                $q->whereHas('student', function ($q2) use ($kelas) {
                    $q2->where('kelas', $kelas);
                });
            })
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $hadirMasuk = $attendanceStats['Hadir Masuk'] ?? 0;
        $terlambat = $attendanceStats['Terlambat'] ?? 0;
        $hadirPulang = $attendanceStats['Hadir Pulang'] ?? 0;
        $izin = $attendanceStats['Izin'] ?? 0;
        $tidakHadir = $attendanceStats['Tidak Hadir'] ?? 0;
        
        $totalHadir = $hadirMasuk + $terlambat + $hadirPulang;
        
        // Get total students
        $totalStudents = Student::when($kelas !== 'all', function ($q) use ($kelas) {
                $q->where('kelas', $kelas);
            })
            ->count();
        
        // Get attendance data for table with optimized query
        $query = Attendance::whereDate('tanggal', $tanggal)
            ->when($kelas !== 'all', function ($q) use ($kelas) {
                $q->whereHas('student', function ($q2) use ($kelas) {
                    $q2->where('kelas', $kelas);
                });
            })
            ->when($status !== '', function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->with(['student' => function($q) {
                $q->select('id', 'nis', 'kelas', 'user_id')
                  ->with(['user' => function($q2) {
                      $q2->select('id', 'name');
                  }]);
            }])
            ->select('id', 'student_id', 'tanggal', 'waktu', 'status')
            ->orderBy('waktu', 'desc')
            ->limit(100); // Limit to 100 records for performance

        $attendances = $query->get()->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'student_name' => $attendance->student->user->name ?? 'N/A',
                'nis' => $attendance->student->nis ?? 'N/A',
                'kelas' => $attendance->student->kelas ?? 'N/A',
                'tanggal' => $attendance->tanggal->format('d/m/Y'),
                'waktu' => $attendance->waktu,
                'status' => $attendance->status,
            ];
        });
        
        // Weekly data for chart (last 7 days) with optimized batch query
        $days = 7;
        $dates = [];
        $weeklyStats = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = date('d/m', strtotime($date));
            $weeklyStats[$date] = ['hadir' => 0, 'izin' => 0];
        }

        // Get weekly stats in batch query
        $weeklyData = Attendance::whereDate('tanggal', '>=', now()->subDays($days - 1)->format('Y-m-d'))
            ->when($kelas !== 'all', function ($q) use ($kelas) {
                $q->whereHas('student', function ($q2) use ($kelas) {
                    $q2->where('kelas', $kelas);
                });
            })
            ->selectRaw('DATE(tanggal) as date, status, COUNT(*) as count')
            ->groupBy('date', 'status')
            ->get();

        foreach ($weeklyData as $data) {
            $date = $data->date;
            if (isset($weeklyStats[$date])) {
                if (in_array($data->status, ['Hadir Masuk', 'Hadir Pulang'])) {
                    $weeklyStats[$date]['hadir'] += $data->count;
                } elseif ($data->status === 'Izin') {
                    $weeklyStats[$date]['izin'] += $data->count;
                }
            }
        }

        $weeklyHadir = [];
        $weeklyIzin = [];
        foreach ($weeklyStats as $date => $stats) {
            $weeklyHadir[] = $stats['hadir'];
            $weeklyIzin[] = $stats['izin'];
        }

        return response()->json([
            'stats' => [
                'hadir' => $totalHadir,
                'izin' => $izin,
                'tidak_hadir' => $tidakHadir,
                'total' => $totalStudents,
            ],
            'chartData' => [
                'hadir' => $totalHadir,
                'izin' => $izin,
                'tidak_hadir' => $tidakHadir,
                'weekly_labels' => $dates,
                'weekly_hadir' => $weeklyHadir,
                'weekly_izin' => $weeklyIzin,
            ],
            'attendances' => $attendances,
        ]);
    }

    /**
     * Get real-time attendance updates (AJAX polling)
     */
    public function realtimeUpdates(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'guru'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lastUpdate = $request->get('last_update', now()->subMinutes(5));

        $newAttendances = Attendance::where('created_at', '>', $lastUpdate)
            ->with('student')
            ->get();

        $newPermissions = Permission::where('created_at', '>', $lastUpdate)
            ->with('student')
            ->get();

        return response()->json([
            'last_update' => now()->toDateTimeString(),
            'attendances' => $newAttendances,
            'permissions' => $newPermissions,
        ]);
    }
}
