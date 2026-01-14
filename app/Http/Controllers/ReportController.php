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

        // Get students in the class
        $students = Student::with('user')
            ->where('kelas', $kelas)
            ->get();

        $reportData = [];
        $totalDays = $startDate->diffInDays($endDate) + 1;

        foreach ($students as $student) {
            // Get attendance data for the date range
            $attendances = Attendance::where('student_id', $student->id)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->get();

            // Count statuses
            $hadirMasuk = $attendances->where('status', 'Hadir Masuk')->count();
            $terlambat = $attendances->where('status', 'Terlambat')->count();
            $hadirPulang = $attendances->where('status', 'Hadir Pulang')->count();
            $izin = $attendances->where('status', 'Izin')->count();
            $tidakHadir = $attendances->where('status', 'Tidak Hadir')->count();
            
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

        // Class summary
        $classSummary = [
            'total_students' => $students->count(),
            'total_days' => $totalDays,
            'total_hadir_masuk' => collect($reportData)->sum('hadir_masuk'),
            'total_terlambat' => collect($reportData)->sum('terlambat'),
            'total_hadir_pulang' => collect($reportData)->sum('hadir_pulang'),
            'total_izin' => collect($reportData)->sum('izin'),
            'total_tidak_hadir' => collect($reportData)->sum('tidak_hadir'),
            'average_attendance_percentage' => $students->count() > 0 ? 
                round(collect($reportData)->avg('attendance_percentage'), 1) : 0,
        ];

        // Log the report generation
        try {
            Report::logReport(
                'class',
                'Laporan Kelas ' . $kelas,
                [
                    'kelas' => $kelas,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'total_students' => $students->count(),
                    'total_days' => $totalDays,
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

        // Get student data
        $student = Student::with('user')->findOrFail($studentId);

        // Get attendance data for the date range
        $attendances = Attendance::where('student_id', $studentId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->get();

        // Get permissions for the date range
        $permissions = Permission::where('student_id', $studentId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->get();

        // Calculate statistics
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        $hadirMasuk = $attendances->where('status', 'Hadir Masuk')->count();
        $terlambat = $attendances->where('status', 'Terlambat')->count();
        $hadirPulang = $attendances->where('status', 'Hadir Pulang')->count();
        $izin = $attendances->where('status', 'Izin')->count();
        $tidakHadir = $attendances->where('status', 'Tidak Hadir')->count();
        
        $totalAttendance = $hadirMasuk + $terlambat + $hadirPulang + $izin + $tidakHadir;
        $attendancePercentage = $totalDays > 0 ? round(($totalAttendance / $totalDays) * 100, 1) : 0;

        // Monthly statistics
        $monthlyStats = [];
        $currentMonth = $startDate->copy();
        
        while ($currentMonth <= $endDate) {
            $monthStart = $currentMonth->copy()->startOfMonth();
            $monthEnd = $currentMonth->copy()->endOfMonth();
            
            $monthAttendances = Attendance::where('student_id', $studentId)
                ->whereBetween('tanggal', [$monthStart, $monthEnd])
                ->get();
            
            $monthHadirMasuk = $monthAttendances->where('status', 'Hadir Masuk')->count();
            $monthTerlambat = $monthAttendances->where('status', 'Terlambat')->count();
            $monthHadirPulang = $monthAttendances->where('status', 'Hadir Pulang')->count();
            $monthIzin = $monthAttendances->where('status', 'Izin')->count();
            $monthTidakHadir = $monthAttendances->where('status', 'Tidak Hadir')->count();
            
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

        // Log the report generation
        try {
            Report::logReport(
                'student',
                'Laporan Siswa ' . ($student->user->name ?? 'N/A'),
                [
                    'student_id' => $studentId,
                    'student_name' => $student->user->name ?? 'N/A',
                    'student_nis' => $student->nis ?? 'N/A',
                    'student_kelas' => $student->kelas ?? 'N/A',
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'total_days' => $totalDays,
                    'attendance_percentage' => $attendancePercentage,
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
