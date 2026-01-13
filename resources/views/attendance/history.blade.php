@extends('layouts.app')

@section('title', 'Riwayat Absensi')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="h4">Riwayat Absensi</h2>
        <p class="text-muted">Riwayat absensi {{ $student->user->name }}</p>
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
                        <label for="filter-bulan" class="form-label">Bulan</label>
                        <select class="form-select" id="filter-bulan">
                            <option value="">Semua Bulan</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $i == now()->month ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="filter-tahun" class="form-label">Tahun</label>
                        <select class="form-select" id="filter-tahun">
                            <option value="">Semua Tahun</option>
                            @for($year = now()->year; $year >= now()->year - 2; $year--)
                                <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
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
            <div class="stat-number text-success">{{ $stats['hadir'] }}</div>
            <div class="stat-label">Hadir</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-warning">{{ $stats['izin'] }}</div>
            <div class="stat-label">Izin</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-danger">{{ $stats['tidak_hadir'] }}</div>
            <div class="stat-label">Tidak Hadir</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-info">{{ $stats['total'] }}</div>
            <div class="stat-label">Total</div>
        </div>
    </div>
</div>

<!-- Attendance History -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Daftar Riwayat Absensi
            </div>
            <div class="card-body">
                @if($attendances->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendances as $attendance)
                                <tr>
                                    <td>{{ $attendance->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $attendance->waktu }}</td>
                                    <td>
                                        <span class="attendance-status 
                                            @if(in_array($attendance->status, ['Hadir Masuk', 'Hadir Pulang'])) status-hadir
                                            @elseif($attendance->status === 'Izin') status-izin
                                            @else status-tidak-hadir
                                            @endif">
                                            {{ $attendance->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($attendance->status === 'Izin')
                                            <small class="text-muted">Dengan izin</small>
                                        @elseif($attendance->status === 'Tidak Hadir')
                                            <small class="text-muted">Tanpa keterangan</small>
                                        @else
                                            <small class="text-success">Hadir</small>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center">
                        {{ $attendances->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-x display-4 text-muted"></i>
                        <p class="mt-3">Belum ada riwayat absensi.</p>
                        <a href="{{ route('attendance.scanner') }}" class="btn btn-primary">
                            <i class="bi bi-qr-code-scan"></i> Scan QR Absensi
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Monthly Chart -->
<div class="row mt-4">
    <div class="col-12">
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
</div>

@push('scripts')
<script>
    function applyFilters() {
        const bulan = $('#filter-bulan').val();
        const tahun = $('#filter-tahun').val();
        const status = $('#filter-status').val();
        
        let url = new URL(window.location.href);
        let params = new URLSearchParams(url.search);
        
        if (bulan) params.set('bulan', bulan);
        if (tahun) params.set('tahun', tahun);
        if (status) params.set('status', status);
        
        window.location.href = url.pathname + '?' + params.toString();
    }
    
    // Initialize filters from URL
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        $('#filter-bulan').val(urlParams.get('bulan') || '');
        $('#filter-tahun').val(urlParams.get('tahun') || '');
        $('#filter-status').val(urlParams.get('status') || '');
        
        // Monthly attendance chart
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Hadir',
                    data: {!! json_encode($chartData['hadir']) !!},
                    backgroundColor: '#198754',
                    borderColor: '#198754',
                    borderWidth: 1
                }, {
                    label: 'Izin',
                    data: {!! json_encode($chartData['izin']) !!},
                    backgroundColor: '#ffc107',
                    borderColor: '#ffc107',
                    borderWidth: 1
                }, {
                    label: 'Tidak Hadir',
                    data: {!! json_encode($chartData['tidak_hadir']) !!},
                    backgroundColor: '#dc3545',
                    borderColor: '#dc3545',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection