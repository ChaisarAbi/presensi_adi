@extends('layouts.app')

@section('title', 'Dashboard Admin')

@push('styles')
<style>
    /* Mobile optimization */
    .stat-card {
        padding: 1rem;
        border-radius: 10px;
        transition: transform 0.2s;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
    }
    
    .stat-number {
        font-size: 1.8rem;
        font-weight: bold;
        line-height: 1.2;
    }
    
    .stat-label {
        font-size: 0.9rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }
    
    /* Fix overflow on mobile */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Better padding for mobile */
    @media (max-width: 768px) {
        .card {
            margin-bottom: 1rem;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .stat-number {
            font-size: 1.5rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
    }
    
    /* Activity list styling */
    .activity-item {
        border-left: 3px solid #0d6efd;
        padding-left: 1rem;
        margin-bottom: 1rem;
    }
    
    .activity-time {
        font-size: 0.8rem;
        color: #6c757d;
    }
    
    .activity-text {
        margin-bottom: 0.25rem;
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="h4">Dashboard Admin</h2>
        <p class="text-muted">Selamat datang, {{ Auth::user()->name }}!</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-primary" id="stat-total-students">{{ $totalStudents }}</div>
            <div class="stat-label">Total Siswa</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-success" id="stat-total-teachers">{{ $totalTeachers }}</div>
            <div class="stat-label">Total Guru</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-warning" id="stat-today-attendances">{{ $todayAttendances }}</div>
            <div class="stat-label">Absensi Hari Ini</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-danger" id="stat-pending-permissions">{{ $pendingPermissions }}</div>
            <div class="stat-label">Izin Pending</div>
        </div>
    </div>
</div>

<!-- Attendance Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-success" id="stat-present-today">{{ $presentToday }}</div>
            <div class="stat-label">Hadir Hari Ini</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-danger" id="stat-absent-today">{{ $absentToday }}</div>
            <div class="stat-label">Tidak Hadir</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-warning" id="stat-late-today">{{ $lateToday }}</div>
            <div class="stat-label">Terlambat</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-info" id="stat-permission-today">{{ $permissionToday }}</div>
            <div class="stat-label">Izin</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning-charge"></i> Aksi Cepat Admin
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <a href="{{ route('qr.generate') }}" class="btn btn-primary w-100">
                            <i class="bi bi-qr-code"></i> Generate QR
                        </a>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <a href="{{ route('monitoring.index') }}" class="btn btn-success w-100">
                            <i class="bi bi-graph-up"></i> Monitoring
                        </a>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <a href="{{ route('permission.index') }}" class="btn btn-warning w-100">
                            <i class="bi bi-clipboard-check"></i> Verifikasi Izin
                        </a>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <a href="{{ route('users.index') }}" class="btn btn-info w-100 text-white">
                            <i class="bi bi-people"></i> Kelola User
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-clock-history"></i> Aktivitas Terbaru
                </div>
                <button class="btn btn-sm btn-outline-secondary" onclick="refreshActivities()">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
            <div class="card-body">
                <div id="activities-container">
                    @if(count($recentActivities) > 0)
                        @foreach($recentActivities as $activity)
                            <div class="activity-item">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <i class="bi {{ $activity['icon'] }} {{ $activity['color'] }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="activity-text">{{ $activity['text'] }}</div>
                                        <div class="activity-time">
                                            <i class="bi bi-clock"></i> {{ $activity['time'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-activity display-4 text-muted"></i>
                            <p class="mt-2 text-muted">Belum ada aktivitas terbaru</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-bar-chart"></i> Statistik Kehadiran Hari Ini
                </div>
                <div class="text-muted small" id="chart-updated-time">
                    <i class="bi bi-clock"></i> {{ now()->format('H:i:s') }}
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 250px;">
                    <canvas id="attendanceChart"></canvas>
                </div>
                <div class="mt-3">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="stat-mini-card bg-success text-white rounded p-2">
                                <div class="stat-mini-number">{{ $presentToday }}</div>
                                <div class="stat-mini-label">Hadir</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-mini-card bg-danger text-white rounded p-2">
                                <div class="stat-mini-number">{{ $absentToday }}</div>
                                <div class="stat-mini-label">Tidak Hadir</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-mini-card bg-warning text-white rounded p-2">
                                <div class="stat-mini-number">{{ $lateToday }}</div>
                                <div class="stat-mini-label">Terlambat</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-mini-card bg-info text-white rounded p-2">
                                <div class="stat-mini-number">{{ $permissionToday }}</div>
                                <div class="stat-mini-label">Izin</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Info -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Informasi Sistem
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Versi Sistem</strong></td>
                                <td>1.0.0</td>
                            </tr>
                            <tr>
                                <td><strong>Database</strong></td>
                                <td>SQLite (Development)</td>
                            </tr>
                            <tr>
                                <td><strong>Framework</strong></td>
                                <td>Laravel 11</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Total Pengguna</strong></td>
                                <td id="total-users">{{ $totalStudents + $totalTeachers + 1 }}</td>
                            </tr>
                            <tr>
                                <td><strong>Server Time</strong></td>
                                <td id="server-time">{{ now()->format('H:i:s') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
                                <td><span class="badge bg-success">Online</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Mini stat cards */
    .stat-mini-card {
        transition: transform 0.2s;
    }
    
    .stat-mini-card:hover {
        transform: translateY(-2px);
    }
    
    .stat-mini-number {
        font-size: 1.2rem;
        font-weight: bold;
        line-height: 1.2;
    }
    
    .stat-mini-label {
        font-size: 0.75rem;
        opacity: 0.9;
    }
    
    /* Chart container */
    .chart-container {
        position: relative;
        height: 250px;
    }
</style>
@endpush

@push('scripts')
<script>
    // Attendance chart
    let attendanceChart = null;
    
    function initAttendanceChart() {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        
        if (attendanceChart) {
            attendanceChart.destroy();
        }
        
        attendanceChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Tidak Hadir', 'Terlambat', 'Izin'],
                datasets: [{
                    data: [
                        {{ $presentToday }},
                        {{ $absentToday }},
                        {{ $lateToday }},
                        {{ $permissionToday }}
                    ],
                    backgroundColor: [
                        '#198754', // Hadir - hijau
                        '#dc3545', // Tidak Hadir - merah
                        '#ffc107', // Terlambat - kuning
                        '#0dcaf0'  // Izin - biru
                    ],
                    borderWidth: 1,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                const value = context.raw;
                                const total = {{ $presentToday + $absentToday + $lateToday + $permissionToday }};
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                label += value + ' siswa (' + percentage + '%)';
                                return label;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }
    
    // Initialize charts
    $(document).ready(function() {
        initAttendanceChart();
        
        // Auto-refresh stats every 30 seconds
        setInterval(refreshDashboardStats, 30000);
        
        // If new user was just created, show special notification
        @if(session('new_user'))
        setTimeout(() => {
            showNewUserNotification();
        }, 1000);
        @endif
    });
    
    function refreshDashboardStats() {
        $.ajax({
            url: '{{ route("dashboard.admin") }}',
            method: 'GET',
            data: { refresh: true },
            success: function(response) {
                // Update stats cards
                updateStatsCards(response);
                
                // Update attendance chart
                updateAttendanceChart(response);
                
                // Show update notification
                showUpdateNotification(response.timestamp);
            },
            error: function() {
                console.log('Failed to refresh stats');
            }
        });
    }
    
    function updateStatsCards(data) {
        // Update all stat cards
        $('#stat-total-students').text(data.totalStudents);
        $('#stat-total-teachers').text(data.totalTeachers);
        $('#stat-today-attendances').text(data.todayAttendances);
        $('#stat-pending-permissions').text(data.pendingPermissions);
        
        // Update attendance stats cards
        $('#stat-present-today').text(data.presentToday || 0);
        $('#stat-absent-today').text(data.absentToday || 0);
        $('#stat-late-today').text(data.lateToday || 0);
        $('#stat-permission-today').text(data.permissionToday || 0);
        
        // Update system info
        $('#server-time').text(data.timestamp);
        $('#total-users').text(data.totalStudents + data.totalTeachers + 1);
    }
    
    function showUpdateNotification(timestamp) {
        // Show small notification that stats were updated
        const notification = $(`
            <div class="toast align-items-center text-bg-info border-0 position-fixed bottom-0 start-0 m-3" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-arrow-clockwise"></i> Statistik diperbarui: ${timestamp}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        
        $('body').append(notification);
        const toast = new bootstrap.Toast(notification[0]);
        toast.show();
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    function updateAttendanceChart(data) {
        // Update attendance chart data
        if (attendanceChart) {
            attendanceChart.data.datasets[0].data = [
                data.presentToday || 0,
                data.absentToday || 0,
                data.lateToday || 0,
                data.permissionToday || 0
            ];
            attendanceChart.update();
            
            // Update mini cards
            $('.stat-mini-card:nth-child(1) .stat-mini-number').text(data.presentToday || 0);
            $('.stat-mini-card:nth-child(2) .stat-mini-number').text(data.absentToday || 0);
            $('.stat-mini-card:nth-child(3) .stat-mini-number').text(data.lateToday || 0);
            $('.stat-mini-card:nth-child(4) .stat-mini-number').text(data.permissionToday || 0);
            
            // Update chart timestamp
            $('#chart-updated-time').html('<i class="bi bi-clock"></i> ' + data.timestamp);
        }
    }
    
    function showNewUserNotification() {
        const notification = $(`
            <div class="toast align-items-center text-bg-success border-0 position-fixed bottom-0 end-0 m-3" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-person-check-fill"></i> {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        
        $('body').append(notification);
        const toast = new bootstrap.Toast(notification[0]);
        toast.show();
        
        // Remove after 10 seconds
        setTimeout(() => {
            notification.remove();
        }, 10000);
    }
    
    // Manual refresh button
    function refreshStats() {
        location.reload();
    }
    
    // Refresh activities
    function refreshActivities() {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i>';
        btn.disabled = true;
        
        $.ajax({
            url: '{{ route("dashboard.admin") }}',
            method: 'GET',
            data: { refresh: true },
            success: function(response) {
                // Update activities
                updateActivities(response.recentActivities);
                
                // Re-enable button
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }, 500);
                
                // Show notification
                showActivityUpdateNotification();
            },
            error: function() {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                console.log('Failed to refresh activities');
            }
        });
    }
    
    function updateActivities(activities) {
        const container = $('#activities-container');
        
        if (activities.length === 0) {
            container.html(`
                <div class="text-center py-4">
                    <i class="bi bi-activity display-4 text-muted"></i>
                    <p class="mt-2 text-muted">Belum ada aktivitas terbaru</p>
                </div>
            `);
            return;
        }
        
        let html = '';
        activities.forEach(function(activity) {
            html += `
                <div class="activity-item">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            <i class="bi ${activity.icon} ${activity.color}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="activity-text">${activity.text}</div>
                            <div class="activity-time">
                                <i class="bi bi-clock"></i> ${activity.time}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.html(html);
    }
    
    function showActivityUpdateNotification() {
        const notification = $(`
            <div class="toast align-items-center text-bg-success border-0 position-fixed bottom-0 start-0 m-3" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-check-circle"></i> Aktivitas diperbarui
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        
        $('body').append(notification);
        const toast = new bootstrap.Toast(notification[0]);
        toast.show();
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    // Auto-refresh activities every 60 seconds
    setInterval(refreshActivities, 60000);
</script>
@endpush
@endsection
