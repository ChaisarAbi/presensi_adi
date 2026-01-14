@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="h4">Dashboard Guru</h2>
        <p class="text-muted">Selamat datang, {{ Auth::user()->name }}!</p>
        <p class="mb-0"><strong>Kelas:</strong> {{ $kelas }}</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-primary">{{ $presentToday }}</div>
            <div class="stat-label">Hadir Hari Ini</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-warning">{{ $absentToday }}</div>
            <div class="stat-label">Tidak Hadir</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-danger">{{ $lateToday }}</div>
            <div class="stat-label">Terlambat</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-info">{{ $permissionToday }}</div>
            <div class="stat-label">Izin</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-success">{{ $pendingPermissions }}</div>
            <div class="stat-label">Izin Pending</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-info">
                @php
                    $totalStudents = \App\Models\Student::when($kelas !== 'Semua Kelas', function($q) use ($kelas) {
                        $q->where('kelas', $kelas);
                    })->count();
                @endphp
                {{ $totalStudents }}
            </div>
            <div class="stat-label">Total Siswa</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning-charge"></i> Aksi Cepat Guru
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="{{ route('monitoring.index') }}" class="btn btn-primary w-100">
                            <i class="bi bi-graph-up"></i> Monitoring
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="{{ route('permission.index') }}" class="btn btn-warning w-100">
                            <i class="bi bi-clipboard-check"></i> Verifikasi Izin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Attendance -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-day"></i> Statistik Hari Ini
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="todayChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Aktivitas Terbaru
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @php
                        $recentAttendances = \App\Models\Attendance::with('student')
                            ->whereDate('tanggal', today())
                            ->when($kelas !== 'Semua Kelas', function($q) use ($kelas) {
                                $q->whereHas('student', function($q2) use ($kelas) {
                                    $q2->where('kelas', $kelas);
                                });
                            })
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp
                    
                    @if($recentAttendances->count() > 0)
                        @foreach($recentAttendances as $attendance)
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $attendance->student->user->name ?? 'N/A' }}</h6>
                                <small>{{ $attendance->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">
                                <span class="attendance-status 
                                    @if(in_array($attendance->status, ['Hadir Masuk', 'Hadir Pulang'])) status-hadir
                                    @elseif($attendance->status === 'Izin') status-izin
                                    @else status-tidak-hadir
                                    @endif">
                                    {{ $attendance->status }}
                                </span>
                            </p>
                            <small class="text-muted">Kelas: {{ $attendance->student->kelas ?? 'N/A' }}</small>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-info-circle display-4 text-muted"></i>
                            <p class="mt-2">Belum ada aktivitas hari ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Today's statistics chart
    const ctx = document.getElementById('todayChart').getContext('2d');
    const todayChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Hadir', 'Tidak Hadir', 'Terlambat', 'Izin', 'Belum Absen'],
            datasets: [{
                data: [
                    {{ $presentToday }}, 
                    {{ $absentToday }}, 
                    {{ $lateToday }}, 
                    {{ $permissionToday }},
                    {{ $notCheckedIn }}
                ],
                backgroundColor: [
                    '#198754', // Hadir - hijau
                    '#dc3545', // Tidak Hadir - merah
                    '#ffc107', // Terlambat - kuning
                    '#fd7e14', // Izin - orange
                    '#6c757d'  // Belum Absen - abu-abu
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.raw + ' siswa';
                            return label;
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection                                
    });
