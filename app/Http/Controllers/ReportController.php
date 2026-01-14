<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Permission;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

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

    /**
     * Generate and download class report as PDF (Ultra Optimized)
     */
    public function generateClassPdf(Request $request)
    {
        // Set memory limit for PDF generation
        ini_set('memory_limit', '256M');
        set_time_limit(180); // 3 minutes

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
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Ultra optimized query - single query dengan minimal columns
        $stats = DB::select("
            SELECT 
                s.nis,
                u.name,
                COALESCE(SUM(CASE WHEN a.status = 'Hadir Masuk' THEN 1 ELSE 0 END), 0) as hadir_masuk,
                COALESCE(SUM(CASE WHEN a.status = 'Terlambat' THEN 1 ELSE 0 END), 0) as terlambat,
                COALESCE(SUM(CASE WHEN a.status = 'Hadir Pulang' THEN 1 ELSE 0 END), 0) as hadir_pulang,
                COALESCE(SUM(CASE WHEN a.status = 'Izin' THEN 1 ELSE 0 END), 0) as izin,
                COALESCE(SUM(CASE WHEN a.status = 'Tidak Hadir' THEN 1 ELSE 0 END), 0) as tidak_hadir
            FROM students s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN attendances a ON s.id = a.student_id 
                AND a.tanggal BETWEEN ? AND ?
            WHERE s.kelas = ?
            GROUP BY s.nis, u.name
            ORDER BY s.nis
            LIMIT 100 -- Limit untuk mencegah data terlalu besar
        ", [$startDate->format('Y-m-d'), $endDate->format('Y-m-d'), $kelas]);

        // Calculate totals dengan streaming untuk mengurangi memory
        $totalStudents = count($stats);
        $totalHadirMasuk = 0;
        $totalTerlambat = 0;
        $totalHadirPulang = 0;
        $totalIzin = 0;
        $totalTidakHadir = 0;

        foreach ($stats as $row) {
            $totalHadirMasuk += $row->hadir_masuk;
            $totalTerlambat += $row->terlambat;
            $totalHadirPulang += $row->hadir_pulang;
            $totalIzin += $row->izin;
            $totalTidakHadir += $row->tidak_hadir;
        }

        $summary = [
            'total_students' => $totalStudents,
            'total_days' => $totalDays,
            'total_hadir_masuk' => $totalHadirMasuk,
            'total_terlambat' => $totalTerlambat,
            'total_hadir_pulang' => $totalHadirPulang,
            'total_izin' => $totalIzin,
            'total_tidak_hadir' => $totalTidakHadir,
        ];

        // Generate PDF dengan try-catch untuk error handling
        try {
            $pdf = Pdf::loadView('report.pdf.class', [
                'stats' => $stats,
                'summary' => $summary,
                'kelas' => $kelas,
                'start_date' => $startDate->format('d/m/Y'),
                'end_date' => $endDate->format('d/m/Y'),
                'generated_at' => now()->format('d/m/Y H:i:s'),
            ]);
            
            return $pdf->download("laporan-kelas-{$kelas}-{$startDate->format('Ymd')}-{$endDate->format('Ymd')}.pdf");
            
        } catch (\Exception $e) {
            // Jika PDF generation gagal, return error
            return back()->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Generate and download student report as PDF (Ultra Optimized)
     */
    public function generateStudentPdf(Request $request)
    {
        // Set memory limit for PDF generation
        ini_set('memory_limit', '256M');
        set_time_limit(180); // 3 minutes

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
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Get student data with minimal columns
        $student = Student::select('id', 'user_id', 'nis', 'kelas', 'nama_ortu', 'kontak_ortu')
            ->with(['user' => function($query) {
                $query->select('id', 'name');
            }])
            ->findOrFail($studentId);

        // Ultra optimized query for attendance statistics - use raw SQL with minimal memory
        $attendanceStats = DB::select("
            SELECT 
                COALESCE(SUM(CASE WHEN status = 'Hadir Masuk' THEN 1 ELSE 0 END), 0) as hadir_masuk,
                COALESCE(SUM(CASE WHEN status = 'Terlambat' THEN 1 ELSE 0 END), 0) as terlambat,
                COALESCE(SUM(CASE WHEN status = 'Hadir Pulang' THEN 1 ELSE 0 END), 0) as hadir_pulang,
                COALESCE(SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END), 0) as izin,
                COALESCE(SUM(CASE WHEN status = 'Tidak Hadir' THEN 1 ELSE 0 END), 0) as tidak_hadir
            FROM attendances 
            WHERE student_id = ? 
                AND tanggal BETWEEN ? AND ?
        ", [$studentId, $startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        $hadirMasuk = $attendanceStats[0]->hadir_masuk ?? 0;
        $terlambat = $attendanceStats[0]->terlambat ?? 0;
        $hadirPulang = $attendanceStats[0]->hadir_pulang ?? 0;
        $izin = $attendanceStats[0]->izin ?? 0;
        $tidakHadir = $attendanceStats[0]->tidak_hadir ?? 0;
        
        $totalAttendance = $hadirMasuk + $terlambat + $hadirPulang + $izin + $tidakHadir;
        $attendancePercentage = $totalDays > 0 ? round(($totalAttendance / $totalDays) * 100, 1) : 0;

        // Get recent attendances with chunk processing
        $recentAttendances = [];
        Attendance::where('student_id', $studentId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu', 'desc')
            ->select('tanggal', 'waktu', 'status')
            ->chunk(100, function ($chunk) use (&$recentAttendances) {
                foreach ($chunk as $attendance) {
                    if (count($recentAttendances) < 15) { // Limit to 15 records only
                        $recentAttendances[] = $attendance;
                    } else {
                        return false; // Stop chunking
                    }
                }
            });

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

        // Generate PDF with minimal data
        try {
            $pdf = Pdf::loadView('report.pdf.student', [
                'student' => $student,
                'statistics' => $statistics,
                'recent_attendances' => collect($recentAttendances),
                'start_date' => $startDate->format('d/m/Y'),
                'end_date' => $endDate->format('d/m/Y'),
                'generated_at' => now()->format('d/m/Y H:i:s'),
            ]);
            
            $fileName = "laporan-siswa-{$student->nis}-{$startDate->format('Ymd')}-{$endDate->format('Ymd')}.pdf";
            return $pdf->download($fileName);
            
        } catch (\Exception $e) {
            // If PDF generation fails, return error
            return back()->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Simple form for PDF generation
     */
    public function pdfForm()
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }

        // Get class list for filter
        $kelasList = Student::select('kelas')->distinct()->pluck('kelas');
        
        // Get student list for per-student report
        $students = Student::with('user')->get();

        return view('report.pdf-form', compact('kelasList', 'students'));
    }
}
