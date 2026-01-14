<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Permission;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Show report index page
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }

        // Get class list for filter
        $kelasList = Student::select('kelas')->distinct()->pluck('kelas');
        
        // Get student list for per-student report
        $students = Student::with('user')->get();

        return view('report.index', compact('kelasList', 'students'));
    }

    /**
     * Generate class report
     */
    public function classReport(Request $request)
    {
        // Increase memory limit for large data processing
        ini_set('memory_limit', '256M');
        set_time_limit(120);

        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'kelas' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $kelas = $request->kelas;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Get students in the class with only necessary columns
        $students = Student::where('kelas', $kelas)
            ->with(['user' => function($query) {
                $query->select('id', 'name'); // Only get necessary columns
            }])
            ->select('id', 'user_id', 'nis', 'kelas')
            ->get();

        $reportData = [];
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Get all student IDs for batch query
        $studentIds = $students->pluck('id')->toArray();

        // Get attendance counts in batch to reduce queries
        $attendanceCounts = Attendance::whereIn('student_id', $studentIds)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->selectRaw('student_id, status, COUNT(*) as count')
            ->groupBy('student_id', 'status')
            ->get()
            ->groupBy('student_id');

        foreach ($students as $student) {
            $studentAttendances = $attendanceCounts->get($student->id, collect());
            
            // Initialize counts
            $hadirMasuk = 0;
            $terlambat = 0;
            $hadirPulang = 0;
            $izin = 0;
            $tidakHadir = 0;

            // Sum counts from grouped data
            foreach ($studentAttendances as $attendance) {
                switch ($attendance->status) {
                    case 'Hadir Masuk':
                        $hadirMasuk = $attendance->count;
                        break;
                    case 'Terlambat':
                        $terlambat = $attendance->count;
                        break;
                    case 'Hadir Pulang':
                        $hadirPulang = $attendance->count;
                        break;
                    case 'Izin':
                        $izin = $attendance->count;
                        break;
                    case 'Tidak Hadir':
                        $tidakHadir = $attendance->count;
                        break;
                }
            }

            $totalAttendance = $hadirMasuk + $terlambat + $hadirPulang + $izin + $tidakHadir;
            $attendancePercentage = $totalDays > 0 ? round(($totalAttendance / $totalDays) * 100, 1) : 0;

            $reportData[] = [
                'id' => $student->id,
                'name' => $student->user->name ?? 'N/A',
                'nis' => $student->nis ?? 'N/A',
                'hadir_masuk' => $hadirMasuk,
                'terlambat' => $terlambat,
                'hadir_pulang' => $hadirPulang,
                'izin' => $izin,
                'tidak_hadir' => $tidakHadir,
                'total_attendance' => $totalAttendance,
                'attendance_percentage' => $attendancePercentage,
            ];
        }

        // Calculate class summary efficiently
        $totalHadirMasuk = 0;
        $totalTerlambat = 0;
        $totalHadirPulang = 0;
        $totalIzin = 0;
        $totalTidakHadir = 0;
        $totalAttendancePercentage = 0;

        foreach ($reportData as $studentData) {
            $totalHadirMasuk += $studentData['hadir_masuk'];
            $totalTerlambat += $studentData['terlambat'];
            $totalHadirPulang += $studentData['hadir_pulang'];
            $totalIzin += $studentData['izin'];
            $totalTidakHadir += $studentData['tidak_hadir'];
            $totalAttendancePercentage += $studentData['attendance_percentage'];
        }

        $classSummary = [
            'total_students' => $students->count(),
            'total_days' => $totalDays,
            'total_hadir_masuk' => $totalHadirMasuk,
            'total_terlambat' => $totalTerlambat,
            'total_hadir_pulang' => $totalHadirPulang,
            'total_izin' => $totalIzin,
            'total_tidak_hadir' => $totalTidakHadir,
            'average_attendance_percentage' => $students->count() > 0 ? 
                round($totalAttendancePercentage / $students->count(), 1) : 0,
        ];

        // Log the report generation with minimal data
        try {
            Report::logReport(
                'class',
                'Laporan Kelas ' . $kelas,
                [
                    'kelas' => $kelas,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    // Removed heavy data to reduce memory usage
                ],
                $students->count()
            );
        } catch (\Exception $e) {
            // Log error but don't break the report generation
            \Log::error('Failed to log class report: ' . $e->getMessage());
        }

        return view('report.class', compact(
            'kelas',
            'startDate',
            'endDate',
            'reportData',
            'classSummary'
        ));
    }

    /**
     * Generate student report
     */
    public function studentReport(Request $request)
    {
        // Increase memory limit for large data processing
        ini_set('memory_limit', '256M');
        set_time_limit(120);

        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $studentId = $request->student_id;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Get student data with only necessary columns
        $student = Student::with(['user' => function($query) {
            $query->select('id', 'name');
        }])->findOrFail($studentId);

        // Get attendance data for the date range with pagination for large datasets
        $attendances = Attendance::where('student_id', $studentId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->select('id', 'tanggal', 'waktu', 'status')
            ->get();

        // Get permissions for the date range
        $permissions = Permission::where('student_id', $studentId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->select('id', 'tanggal', 'alasan', 'foto_bukti', 'status')
            ->get();

        // Calculate statistics using database aggregation for efficiency
        $attendanceStats = Attendance::where('student_id', $studentId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        $hadirMasuk = $attendanceStats['Hadir Masuk'] ?? 0;
        $terlambat = $attendanceStats['Terlambat'] ?? 0;
        $hadirPulang = $attendanceStats['Hadir Pulang'] ?? 0;
        $izin = $attendanceStats['Izin'] ?? 0;
        $tidakHadir = $attendanceStats['Tidak Hadir'] ?? 0;
        
        $totalAttendance = $hadirMasuk + $terlambat + $hadirPulang + $izin + $tidakHadir;
        $attendancePercentage = $totalDays > 0 ? round(($totalAttendance / $totalDays) * 100, 1) : 0;

        // Monthly statistics with optimized queries
        $monthlyStats = [];
        $currentMonth = $startDate->copy();
        
        while ($currentMonth <= $endDate) {
            $monthStart = $currentMonth->copy()->startOfMonth();
            $monthEnd = $currentMonth->copy()->endOfMonth();
            
            $monthStats = Attendance::where('student_id', $studentId)
                ->whereBetween('tanggal', [$monthStart, $monthEnd])
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
            
            $monthHadirMasuk = $monthStats['Hadir Masuk'] ?? 0;
            $monthTerlambat = $monthStats['Terlambat'] ?? 0;
            $monthHadirPulang = $monthStats['Hadir Pulang'] ?? 0;
            $monthIzin = $monthStats['Izin'] ?? 0;
            $monthTidakHadir = $monthStats['Tidak Hadir'] ?? 0;
            
            $monthlyStats[] = [
                'month' => $currentMonth->format('F Y'),
                'hadir_masuk' => $monthHadirMasuk,
                'terlambat' => $monthTerlambat,
                'hadir_pulang' => $monthHadirPulang,
                'izin' => $monthIzin,
                'tidak_hadir' => $monthTidakHadir,
                'total' => $monthHadirMasuk + $monthTerlambat + $monthHadirPulang + $monthIzin + $monthTidakHadir,
            ];
            
            $currentMonth->addMonth();
        }

        $statistics = [
            'total_days' => $totalDays,
            'hadir_masuk' => $hadirMasuk,
            'terlambat' => $terlambat,
            'hadir_pulang' => $hadirPulang,
            'izin' => $izin,
            'tidak_hadir' => $tidakHadir,
            'total_attendance' => $totalAttendance,
            'attendance_percentage' => $attendancePercentage,
        ];

        // Log the report generation with minimal data
        try {
            Report::logReport(
                'student',
                'Laporan Siswa ' . ($student->user->name ?? 'N/A'),
                [
                    'student_id' => $studentId,
                    'student_name' => $student->user->name ?? 'N/A',
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    // Removed heavy data to reduce memory usage
                ],
                $attendances->count()
            );
        } catch (\Exception $e) {
            // Log error but don't break the report generation
            \Log::error('Failed to log student report: ' . $e->getMessage());
        }

        return view('report.student', compact(
            'student',
            'startDate',
            'endDate',
            'attendances',
            'permissions',
            'statistics',
            'monthlyStats'
        ));
    }

    /**
     * Export report to PDF
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }

        $type = $request->type; // 'class' or 'student'
        $data = $request->all();

        // In a real application, you would generate PDF here
        // For now, we'll return a view with print styles
        
        if ($type === 'class') {
            return view('report.export.class-pdf', $data);
        } else {
            return view('report.export.student-pdf', $data);
        }
    }

    /**
     * Get attendance chart data for student
     */
    public function chartData(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'guru'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $studentId = $request->student_id;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $labels = [];
        $hadirData = [];
        $terlambatData = [];
        $izinData = [];
        $tidakHadirData = [];

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $labels[] = $currentDate->format('d/m');
            
            $attendance = Attendance::where('student_id', $studentId)
                ->whereDate('tanggal', $currentDate)
                ->first();
            
            if ($attendance) {
                $hadirData[] = in_array($attendance->status, ['Hadir Masuk', 'Hadir Pulang']) ? 1 : 0;
                $terlambatData[] = $attendance->status === 'Terlambat' ? 1 : 0;
                $izinData[] = $attendance->status === 'Izin' ? 1 : 0;
                $tidakHadirData[] = $attendance->status === 'Tidak Hadir' ? 1 : 0;
            } else {
                $hadirData[] = 0;
                $terlambatData[] = 0;
                $izinData[] = 0;
                $tidakHadirData[] = 0;
            }
            
            $currentDate->addDay();
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Hadir',
                    'data' => $hadirData,
                    'backgroundColor' => '#28a745',
                ],
                [
                    'label' => 'Terlambat',
                    'data' => $terlambatData,
                    'backgroundColor' => '#ffc107',
                ],
                [
                    'label' => 'Izin',
                    'data' => $izinData,
                    'backgroundColor' => '#fd7e14',
                ],
                [
                    'label' => 'Tidak Hadir',
                    'data' => $tidakHadirData,
                    'backgroundColor' => '#dc3545',
                ],
            ],
        ]);
    }
}
