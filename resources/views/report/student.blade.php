@extends('layouts.app')

@section('title', 'Laporan Absensi Siswa - ' . $student->user->name)

@push('styles')
<style>
    .report-header {
        background: linear-gradient(135deg, #198754 0%, #157347 100%);
        color: white;
        border-radius: 10px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .student-info-card {
        border-radius: 10px;
        transition: transform 0.2s;
        height: 100%;
    }
    
    .student-info-card:hover {
        transform: translateY(-3px);
    }
    
    .stat-card {
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        line-height: 1.2;
    }
    
    .stat-label {
        font-size: 0.9rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }
    
    .attendance-badge {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
    
    .badge-hadir {
        background-color: #28a745;
        color: white;
    }
    
    .badge-terlambat {
        background-color: #ffc107;
        color: #212529;
    }
    
    .badge-izin {
        background-color: #fd7e14;
        color: white;
    }
    
    .badge-tidak-hadir {
        background-color: #dc3545;
        color: white;
    }
    
    .attendance-row {
        transition: background-color 0.2s;
    }
    
    .attendance-row:hover {
        background-color: #f8f9fa;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    
    @media print {
        .no-print {
            display: none !important;
        }
        
        .report-header {
            background: #198754 !important;
            -webkit-print-color-adjust: exact;
        }
        
        .table {
            border-collapse: collapse;
        }
        
        .table th, .table td {
            border: 1px solid #dee2e6;
        }
    }
    
    @media (max-width: 768px) {
        .stat-card {
            padding: 1rem;
        }
        
        .stat-number {
            font-size: 1.5rem;
        }
        
        .report-header {
            padding: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="report-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="h3 mb-2">Laporan Absensi Siswa</h1>
            <h2 class="h1 mb-3">{{ $student->user->name ?? 'N/A' }}</h2>
            <p class="mb-1">
                <i class="bi bi-person-badge"></i> 
                NIS: {{ $student->nis ?? 'N/A' }} | 
                Kelas: {{ $student->kelas ?? 'N/A' }}
            </p>
            <p class="mb-0">
                <i class="bi bi-calendar-range"></i> 
                {{ $startDate->format('d F Y') }} - {{ $endDate->format('d F Y') }}
            </p>
        </div>
        <div class="col-md-4 text-end">
            <div class="d-flex justify-content-end gap-2 no-print">
                <button onclick="window.print()" class="btn btn-light">
                    <i class="bi bi-printer"></i> Cetak
                </button>
                <a href="{{ route('report.index') }}" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <p class="mt-3 mb-0">
                <small>Dibuat pada: {{ now()->format('d F Y H:i') }}</small>
            </p>
        </div>
    </div>
</div>

<!-- Student Info & Statistics -->
<div class="row mb-4">
    <div class="col-md-4 mb-4">
        <div class="card student-info-card">
            <div class="card-header">
                <i class="bi bi-person-circle"></i> Informasi Siswa
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Nama</strong></td>
                        <td>{{ $student->user->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>NIS</strong></td>
                        <td>{{ $student->nis ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Kelas</strong></td>
                        <td>{{ $student->kelas ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Orang Tua</strong></td>
                        <td>{{ $student->nama_ortu ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Kontak</strong></td>
                        <td>{{ $student->kontak_ortu ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart"></i> Statistik Kehadiran
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="stat-card border-success">
                            <div class="stat-number text-success">{{ $statistics['hadir_masuk'] + $statistics['hadir_pulang'] }}</div>
                            <div class="stat-label">Total Hadir</div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-6 mb-3">
                        <div class="stat-card border-warning">
                            <div class="stat-number text-warning">{{ $statistics['terlambat'] }}</div>
                            <div class="stat-label">Terlambat</div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-6 mb-3">
                        <div class="stat-card border-warning">
                            <div class="stat-number text-warning">{{ $statistics['izin'] }}</div>
                            <div class="stat-label">Izin</div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-6 mb-3">
                        <div class="stat-card border-danger">
                            <div class="stat-number text-danger">{{ $statistics['tidak_hadir'] }}</div>
                            <div class="stat-label">Tidak Hadir</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="stat-card border-primary">
                            <div class="stat-number text-primary">{{ $statistics['total_attendance'] }}/{{ $statistics['total_days'] }}</div>
                            <div class="stat-label">Kehadiran (Hari)</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="stat-card border-info">
                            <div class="stat-number text-info">{{ $statistics['attendance_percentage'] }}%</div>
                            <div class="stat-label">Persentase Kehadiran</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance History -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-calendar-week"></i> Riwayat Absensi Harian
                </div>
                <div class="no-print">
                    <span class="badge bg-info">
                        Periode: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                @if($attendances->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Hari</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendances as $attendance)
                                    @php
                                        $badgeClass = 'badge-hadir';
                                        if ($attendance->status === 'Terlambat') {
                                            $badgeClass = 'badge-terlambat';
                                        } elseif ($attendance->status === 'Izin') {
                                            $badgeClass = 'badge-izin';
                                        } elseif ($attendance->status === 'Tidak Hadir') {
                                            $badgeClass = 'badge-tidak-hadir';
                                        }
                                    @endphp
                                    <tr class="attendance-row">
                                        <td>{{ $attendance->tanggal->format('d/m/Y') }}</td>
                                        <td>{{ $attendance->tanggal->translatedFormat('l') }}</td>
                                        <td>{{ $attendance->waktu ?? '-' }}</td>
                                        <td>
                                            <span class="badge attendance-badge {{ $badgeClass }}">
                                                {{ $attendance->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($attendance->status === 'Terlambat')
                                                <small class="text-muted">Terlambat {{ $attendance->terlambat ?? '0' }} menit</small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-x display-4 text-muted"></i>
                        <h5 class="mt-3">Tidak ada data absensi</h5>
                        <p class="text-muted">Belum ada catatan absensi dalam periode ini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Permissions History -->
@if($permissions->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clipboard-check"></i> Riwayat Izin
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Alasan</th>
                                <th>Status</th>
                                <th>Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissions as $permission)
                                @php
                                    $statusClass = 'badge-warning';
                                    if ($permission->status === 'Disetujui') {
                                        $statusClass = 'badge-success';
                                    } elseif ($permission->status === 'Ditolak') {
                                        $statusClass = 'badge-danger';
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $permission->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ Str::limit($permission->alasan, 50) }}</td>
                                    <td>
                                        <span class="badge {{ $statusClass }}">
                                            {{ $permission->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($permission->foto_bukti)
                                            <a href="{{ asset('storage/' . $permission->foto_bukti) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-image"></i> Lihat
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Monthly Statistics -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-graph-up"></i> Statistik Bulanan
            </div>
            <div class="card-body">
                @if(count($monthlyStats) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Bulan</th>
                                    <th class="text-center">Hadir Masuk</th>
                                    <th class="text-center">Terlambat</th>
                                    <th class="text-center">Hadir Pulang</th>
                                    <th class="text-center">Izin</th>
                                    <th class="text-center">Tidak Hadir</th>
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthlyStats as $month)
                                    <tr>
                                        <td><strong>{{ $month['month'] }}</strong></td>
                                        <td class="text-center">{{ $month['hadir_masuk'] }}</td>
                                        <td class="text-center">{{ $month['terlambat'] }}</td>
                                        <td class="text-center">{{ $month['hadir_pulang'] }}</td>
                                        <td class="text-center">{{ $month['izin'] }}</td>
                                        <td class="text-center">{{ $month['tidak_hadir'] }}</td>
                                        <td class="text-center"><strong>{{ $month['total'] }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-graph-up display-4 text-muted"></i>
                        <p class="mt-2 text-muted">Belum ada data statistik bulanan</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Summary & Notes -->
<div class="row mt-4 no-print">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="bi bi-info-circle"></i> Ringkasan:</h6>
                        <ul class="mb-0">
                            <li>Total hari dalam periode: <strong>{{ $statistics['total_days'] }} hari</strong></li>
                            <li>Total kehadiran: <strong>{{ $statistics['total_attendance'] }} hari</strong></li>
                            <li>Persentase kehadiran: <strong>{{ $statistics['attendance_percentage'] }}%</strong></li>
                            <li>Rata-rata kehadiran per bulan: <strong>{{ count($monthlyStats) > 0 ? round(collect($monthlyStats)->avg('total'), 1) : 0 }} hari</strong></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-lightbulb"></i> Rekomendasi:</h6>
                        <ul class="mb-0">
                            @if($statistics['attendance_percentage'] >= 85)
                                <li class="text-success">✅ Kehadiran sangat baik, pertahankan!</li>
                            @elseif($statistics['attendance_percentage'] >= 70)
                                <li class="text-warning">⚠️ Kehadiran cukup, perlu ditingkatkan</li>
                            @else
                                <li class="text-danger">❌ Kehadiran rendah, perlu perhatian khusus</li>
                            @endif
                            
                            @if($statistics['terlambat'] > 0)
                                <li class="text-warning">⚠️ Terlambat {{ $statistics['terlambat'] }} kali, perlu perbaikan</li>
                            @endif
                            
                            @if($statistics['izin'] > 0)
                                <li class="text-info">ℹ️ Izin {{ $statistics['izin'] }} kali, perlu konfirmasi orang tua</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-print option
    @if(request()->has('print'))
    window.onload = function() {
        window.print();
    };
    @endif
    
    // Add print button functionality
    document.addEventListener('DOMContentLoaded', function() {
        const printBtn = document.querySelector('[onclick="window.print()"]');
        if (printBtn) {
            printBtn.addEventListener('click', function() {
                window.print();
            });
        }
    });
</script>
@endpush
@endsection