<?php

use App\Models\Student;
use App\Models\Attendance;
use App\Models\Permission;
use Illuminate\Support\Facades\Route;

Route::get('/debug-monitoring', function () {
    // Check total students
    $totalStudents = Student::count();
    
    // Check today's attendance
    $today = today()->format('Y-m-d');
    $todayAttendances = Attendance::whereDate('tanggal', $today)->get();
    
    // Check permissions
    $permissions = Permission::all();
    
    // Check if there are any approved permissions
    $approvedPermissions = Permission::where('status', 'Disetujui')->get();
    
    // Check attendance records for approved permissions
    $attendanceForApproved = [];
    foreach ($approvedPermissions as $permission) {
        $attendance = Attendance::where('student_id', $permission->student_id)
            ->whereDate('tanggal', $permission->tanggal)
            ->first();
        
        $attendanceForApproved[] = [
            'permission_id' => $permission->id,
            'student_id' => $permission->student_id,
            'tanggal' => $permission->tanggal,
            'status' => $permission->status,
            'attendance_exists' => $attendance ? true : false,
            'attendance_status' => $attendance ? $attendance->status : null,
        ];
    }
    
    return response()->json([
        'system_info' => [
            'current_date' => $today,
            'total_students' => $totalStudents,
            'total_attendance_today' => $todayAttendances->count(),
            'total_permissions' => $permissions->count(),
            'approved_permissions' => $approvedPermissions->count(),
        ],
        'today_attendances' => $todayAttendances->map(function ($att) {
            return [
                'id' => $att->id,
                'student_id' => $att->student_id,
                'tanggal' => $att->tanggal,
                'status' => $att->status,
                'waktu' => $att->waktu,
            ];
        }),
        'approved_permissions_sync' => $attendanceForApproved,
        'monitoring_url_test' => url('/monitoring'),
        'suggestion' => $totalStudents === 0 ? 'Tidak ada siswa di database. Tambahkan siswa terlebih dahulu.' : 'OK',
    ]);
});