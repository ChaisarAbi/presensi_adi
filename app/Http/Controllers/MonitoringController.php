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
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }

        $kelas = $request->get('kelas', 'all');
        $tanggal = $request->get('tanggal', today()->format('Y-m-d'));

        // Query students with today's attendance
        $query = Student::with(['user', 'attendances' => function ($q) use ($tanggal) {
            $q->whereDate('tanggal', $tanggal);
        }]);

        if ($kelas !== 'all') {
            $query->where('kelas', $kelas);
        }

        $students = $query->get();

        // Get class list for filter
        $kelasList = Student::select('kelas')->distinct()->pluck('kelas');

        // Statistics - Use attendance table for consistency
        $totalStudents = $students->count();
        
        // Count from attendance table
        $hadirMasuk = Attendance::whereDate('tanggal', $tanggal)
            ->where('status', 'Hadir Masuk')
            ->when($kelas !== 'all', function ($q) use ($kelas) {
                $q->whereHas('student', function ($q2) use ($kelas) {
                    $q2->where('kelas', $kelas);
                });
            })
            ->count();
        
        $terlambat = Attendance::whereDate('tanggal', $tanggal)
            ->where('status', 'Terlambat')
            ->when($kelas !== 'all', function ($q) use ($kelas) {
                $q->whereHas('student', function ($q2) use ($kelas) {
                    $q2->where('kelas', $kelas);
                });
            })
            ->count();
        
        $hadirPulang = Attendance::whereDate('tanggal', $tanggal)
            ->where('status', 'Hadir Pulang')
            ->when($kelas !== 'all', function ($q) use ($kelas) {
                $q->whereHas('student', function ($q2) use ($kelas) {
                    $q2->where('kelas', $kelas);
                });
            })
            ->count();
        
        $izin = Attendance::whereDate('tanggal', $tanggal)
            ->where('status', 'Izin')
            ->when($kelas !== 'all', function ($q) use ($kelas) {
                $q->whereHas('student', function ($q2) use ($kelas) {
                    $q2->where('kelas', $kelas);
                });
            })
            ->count();
        
        $tidakHadir = Attendance::whereDate('tanggal', $tanggal)
            ->where('status', 'Tidak Hadir')
            ->when($kelas !== 'all', function ($q) use ($kelas) {
                $q->whereHas('student', function ($q2) use ($kelas) {
                    $q2->where('kelas', $kelas);
                });
            })
            ->count();
        
        // Calculate students without any attendance record
        $studentsWithAttendance = Attendance::whereDate('tanggal', $tanggal)
            ->when($kelas !== 'all', function ($q) use ($kelas) {
                $q->whereHas('student', function ($q2) use ($kelas) {
                    $q2->where('kelas', $kelas);
                });
            })
            ->distinct('student_id')
            ->count('student_id');
            
        $belumAbsen = $totalStudents - $studentsWithAttendance;

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
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'guru'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $kelas = $request->get('kelas', 'all');
        $tanggal = $request->get('tanggal', today()->format('Y-m-d'));
        $status = $request->get('status', '');

        // Get statistics for the selected date
        $hadirMasuk = Attendance::whereDate('tanggal', $tanggal)
            ->where('status', 'Hadir Masuk')
            ->when($kelas !== 'all', function ($q) use ($kelas) {
                $q->whereHas('student', function ($q2) use ($kelas) {
                    $q2->where('kelas', $kelas);
                });
            })
            ->count();
        
        $terlambat = Attendance::whereDate('tanggal', $tanggal)
            ->where('status', 'Terlambat')
            ->when($kelas !== 'all', function ($q) use ($kelas) {
                $q->whereHas('student', function ($q2) use ($kelas) {
                    $q2->where('kelas', $kelas);
                });
            })
            ->count();
        
        $hadirPulang = Attendance::whereDate('tanggal', $tanggal)
            ->where('status', 'Hadir Pulang')
            ->when($kelas !== 'all', function ($q) use ($kelas) {
                $q->whereHas('student', function ($q2) use ($kelas) {
                    $q2->where('kelas', $kelas);
                });
            })
            ->count();
        
        $izin = Attendance::whereDate('tanggal', $tanggal)
            ->where('status', 'Izin')
            ->when($kelas !== 'all', function ($q) use ($kelas) {
                $q->whereHas('student', function ($q2) use ($kelas) {
                    $q2->where('kelas', $kelas);
                });
            })
            ->count();
        
        $tidakHadir = Attendance::whereDate('tanggal', $tanggal)
            ->where('status', 'Tidak Hadir')
            ->when($kelas !== 'all', function ($q) use ($kelas) {
                $q->whereHas('student', function ($q2) use ($kelas) {
                    $q2->where('kelas', $kelas);
                });
            })
            ->count();
        
        $totalHadir = $hadirMasuk + $terlambat + $hadirPulang;
        
        // Get total students
        $totalStudents = Student::when($kelas !== 'all', function ($q) use ($kelas) {
                $q->where('kelas', $kelas);
            })
            ->count();
        
        // Get attendance data for table
        $query = Attendance::with(['student', 'student.user'])
            ->whereDate('tanggal', $tanggal)
            ->when($kelas !== 'all', function ($q) use ($kelas) {
                $q->whereHas('student', function ($q2) use ($kelas) {
                    $q2->where('kelas', $kelas);
                });
            })
            ->when($status !== '', function ($q) use ($status) {
                $q->where('status', $status);
            });
        
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
        
        // Weekly data for chart (last 7 days)
        $days = 7;
        $dates = [];
        $weeklyHadir = [];
        $weeklyIzin = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = date('d/m', strtotime($date));
            
            $hadirDay = Attendance::whereDate('tanggal', $date)
                ->whereIn('status', ['Hadir Masuk', 'Hadir Pulang'])
                ->when($kelas !== 'all', function ($q) use ($kelas) {
                    $q->whereHas('student', function ($q2) use ($kelas) {
                        $q2->where('kelas', $kelas);
                    });
                })
                ->count();
            $weeklyHadir[] = $hadirDay;
            
            $izinDay = Attendance::whereDate('tanggal', $date)
                ->where('status', 'Izin')
                ->when($kelas !== 'all', function ($q) use ($kelas) {
                    $q->whereHas('student', function ($q2) use ($kelas) {
                        $q2->where('kelas', $kelas);
                    });
                })
                ->count();
            $weeklyIzin[] = $izinDay;
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
