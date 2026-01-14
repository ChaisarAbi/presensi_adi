<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\QRController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\UserController;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Debug routes (public for testing)
require __DIR__.'/debug-monitoring.php';

// Tidak ada route registrasi publik - semua user dibuat oleh admin

// Protected routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard routes
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
    Route::get('/dashboard/guru', [DashboardController::class, 'guru'])->name('dashboard.guru');
    Route::get('/dashboard/siswa', [DashboardController::class, 'siswa'])->name('dashboard.siswa');
    
    // Attendance routes
    Route::middleware('role:siswa')->group(function () {
        Route::get('/attendance/scanner', [AttendanceController::class, 'showScanner'])->name('attendance.scanner');
        Route::post('/attendance/scan/masuk', [AttendanceController::class, 'scanMasuk'])->name('attendance.scan.masuk');
        Route::post('/attendance/scan/pulang', [AttendanceController::class, 'scanPulang'])->name('attendance.scan.pulang');
        Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
    });
    
    // Permission routes
    Route::prefix('permission')->group(function () {
        Route::middleware('role:siswa')->group(function () {
            Route::get('/create', [PermissionController::class, 'create'])->name('permission.create');
            Route::post('/store', [PermissionController::class, 'store'])->name('permission.store');
            Route::get('/history', [PermissionController::class, 'history'])->name('permission.history');
        });
        
        Route::middleware('role:admin,guru')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('permission.index');
            Route::post('/{id}/status', [PermissionController::class, 'updateStatus'])->name('permission.updateStatus');
        });
    });
    
    // QR Code routes (admin only)
    Route::middleware('role:admin')->prefix('qr')->group(function () {
        Route::get('/generate', [QRController::class, 'generate'])->name('qr.generate');
        Route::get('/', [QRController::class, 'index'])->name('qr.index');
        Route::post('/validate', [QRController::class, 'validateToken'])->name('qr.validate');
    });
    
    // Monitoring routes (admin & guru)
    Route::middleware('role:admin,guru')->prefix('monitoring')->group(function () {
        Route::get('/', [MonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('/chart-data', [MonitoringController::class, 'chartData'])->name('monitoring.chartData');
        Route::get('/realtime-updates', [MonitoringController::class, 'realtimeUpdates'])->name('monitoring.realtimeUpdates');
    });
    
    // User Management routes (admin only)
    Route::middleware('role:admin')->prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
    });
    
    // Report routes (admin & guru)
    Route::middleware('role:admin,guru')->prefix('report')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('report.index');
        Route::get('/class', [\App\Http\Controllers\ReportController::class, 'classReport'])->name('report.class');
        Route::get('/student', [\App\Http\Controllers\ReportController::class, 'studentReport'])->name('report.student');
        Route::get('/chart-data', [\App\Http\Controllers\ReportController::class, 'chartData'])->name('report.chartData');
        Route::post('/export-pdf', [\App\Http\Controllers\ReportController::class, 'exportPdf'])->name('report.exportPdf');
    });
    
});
require __DIR__.'/test-sync.php';
