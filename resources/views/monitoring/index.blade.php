@extends('layouts.app')

@section('title', 'Monitoring Absensi')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="h4">Monitoring Absensi</h2>
        <p class="text-muted">Monitoring real-time kehadiran siswa</p>
    </div>
</div>

<!-- Filter Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-funnel"></i> Filter Data
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="filter-kelas" class="form-label">Kelas</label>
                        <select class="form-select" id="filter-kelas">
                            <option value="">Semua Kelas</option>
                            <option value="X IPA 1">X IPA 1</option>
                            <option value="X IPA 2">X IPA 2</option>
                            <option value="X IPA 3">X IPA 3</option>
                            <option value="XI IPA 1">XI IPA 1</option>
                            <option value="XI IPA 2">XI IPA 2</option>
                            <option value="XII IPA 1">XII IPA 1</option>
                            <option value="XII IPA 2">XII IPA 2</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="filter-tanggal" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="filter-tanggal" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="filter-status" class="form-label">Status</label>
                        <select class="form-select" id="filter-status">
                            <option value="">Semua Status</option>
                            <option value="Hadir Masuk">Hadir Masuk</option>
                            <option value="Hadir Pulang">Hadir Pulang</option>
                            <option value="Izin">Izin</option>
                            <option value="Tidak Hadir">Tidak Hadir</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" onclick="applyFilters()">
                        <i class="bi bi-filter"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-primary" id="stat-hadir">0</div>
            <div class="stat-label">Hadir</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-warning" id="stat-izin">0</div>
            <div class="stat-label">Izin</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-danger" id="stat-tidak-hadir">0</div>
            <div class="stat-label">Tidak Hadir</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-info" id="stat-total">0</div>
            <div class="stat-label">Total Siswa</div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row mb-4">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart"></i> Statistik Kehadiran
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-week"></i> Kehadiran 7 Hari Terakhir
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-table"></i> Data Absensi
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover data-table" id="attendance-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>NIS</th>
                                <th>Kelas</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let attendanceChart = null;
    let weeklyChart = null;
    let lastUpdate = '{{ now()->toDateTimeString() }}';
    
    // Initialize on page load
    $(document).ready(function() {
        loadData();
        setInterval(loadData, 30000); // Refresh every 30 seconds
        setInterval(checkRealtimeUpdates, 10000); // Check updates every 10 seconds
    });
    
    function loadData() {
        const kelas = $('#filter-kelas').val();
        const tanggal = $('#filter-tanggal').val();
        const status = $('#filter-status').val();
        
        $.ajax({
            url: '{{ route("monitoring.chartData") }}',
            method: 'GET',
            data: { kelas, tanggal, status },
            success: function(response) {
                updateStats(response.stats);
                updateCharts(response.chartData);
                updateTable(response.attendances);
            }
        });
    }
    
    function applyFilters() {
        loadData();
    }
    
    function updateStats(stats) {
        $('#stat-hadir').text(stats.hadir);
        $('#stat-izin').text(stats.izin);
        $('#stat-tidak-hadir').text(stats.tidak_hadir);
        $('#stat-total').text(stats.total);
    }
    
    function updateCharts(chartData) {
        // Update attendance chart
        if (attendanceChart) {
            attendanceChart.destroy();
        }
        
        const ctx1 = document.getElementById('attendanceChart').getContext('2d');
        attendanceChart = new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Izin', 'Tidak Hadir'],
                datasets: [{
                    data: [chartData.hadir, chartData.izin, chartData.tidak_hadir],
                    backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
        
        // Update weekly chart
        if (weeklyChart) {
            weeklyChart.destroy();
        }
        
        const ctx2 = document.getElementById('weeklyChart').getContext('2d');
        weeklyChart = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: chartData.weekly_labels,
                datasets: [{
                    label: 'Hadir',
                    data: chartData.weekly_hadir,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    fill: true
                }, {
                    label: 'Izin',
                    data: chartData.weekly_izin,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
    
    function updateTable(attendances) {
        const tbody = $('#attendance-table tbody');
        tbody.empty();
        
        if (attendances.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="bi bi-calendar-x display-4 text-muted"></i>
                        <p class="mt-2">Tidak ada data absensi</p>
                    </td>
                </tr>
            `);
            return;
        }
        
        attendances.forEach(function(attendance) {
            let statusClass = 'status-hadir';
            if (attendance.status === 'Izin') statusClass = 'status-izin';
            if (attendance.status === 'Tidak Hadir') statusClass = 'status-tidak-hadir';
            
            tbody.append(`
                <tr>
                    <td>${attendance.student_name}</td>
                    <td>${attendance.nis}</td>
                    <td>${attendance.kelas}</td>
                    <td>${attendance.tanggal}</td>
                    <td>${attendance.waktu}</td>
                    <td><span class="attendance-status ${statusClass}">${attendance.status}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewDetail(${attendance.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
            `);
        });
    }
    
    function viewDetail(id) {
        alert('Detail absensi ID: ' + id);
        // Implement detail view as needed
    }
    
    function checkRealtimeUpdates() {
        $.ajax({
            url: '{{ route("monitoring.realtimeUpdates") }}',
            method: 'GET',
            data: { last_update: lastUpdate },
            success: function(response) {
                lastUpdate = response.last_update;
                
                if (response.attendances.length > 0 || response.permissions.length > 0) {
                    // Show notification
                    showNotification('Ada pembaruan data absensi');
                    // Reload data
                    loadData();
                }
            }
        });
    }
    
    function showNotification(message) {
        // Create notification element
        const notification = $(`
            <div class="toast align-items-center text-bg-primary border-0 position-fixed bottom-0 end-0 m-3" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-bell-fill"></i> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        
        $('body').append(notification);
        const toast = new bootstrap.Toast(notification[0]);
        toast.show();
        
        // Remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
</script>
@endpush
@endsection