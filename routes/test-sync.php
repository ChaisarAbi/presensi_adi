<?php

use App\Models\Permission;
use App\Models\Attendance;
use Illuminate\Support\Facades\Route;

Route::get('/test-sync-permission/{id}', function ($id) {
    $permission = Permission::find($id);
    
    if (!$permission) {
        return response()->json(['error' => 'Permission not found'], 404);
    }
    
    // Check if attendance record exists
    $existingAttendance = Attendance::where('student_id', $permission->student_id)
        ->whereDate('tanggal', $permission->tanggal)
        ->first();
    
    $data = [
        'permission' => [
            'id' => $permission->id,
            'student_id' => $permission->student_id,
            'tanggal' => $permission->tanggal,
            'status' => $permission->status,
        ],
        'attendance_exists' => $existingAttendance ? true : false,
        'attendance' => $existingAttendance,
    ];
    
    return response()->json($data);
});

Route::get('/test-create-attendance/{permissionId}', function ($permissionId) {
    $permission = Permission::find($permissionId);
    
    if (!$permission) {
        return response()->json(['error' => 'Permission not found'], 404);
    }
    
    // Check if attendance record already exists
    $existingAttendance = Attendance::where('student_id', $permission->student_id)
        ->whereDate('tanggal', $permission->tanggal)
        ->first();
    
    if ($existingAttendance) {
        return response()->json([
            'message' => 'Attendance already exists',
            'attendance' => $existingAttendance,
        ]);
    }
    
    // Create attendance based on permission status
    $status = $permission->status === 'Disetujui' ? 'Izin' : 'Tidak Hadir';
    
    $attendance = Attendance::create([
        'student_id' => $permission->student_id,
        'tanggal' => $permission->tanggal,
        'waktu' => '00:00:00',
        'status' => $status,
    ]);
    
    return response()->json([
        'message' => 'Attendance created successfully',
        'attendance' => $attendance,
    ]);
});