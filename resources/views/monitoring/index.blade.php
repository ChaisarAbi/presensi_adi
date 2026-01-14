@extends('layouts.app')

@section('title', 'Monitoring Absensi')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="h4">Monitoring Absensi</h2>
        <p class="text-muted">Monitoring real-time kehadiran siswa</p>
    </div>
</div>

<!-- Filter Form -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-funnel"></i> Filter Data
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('monitoring.index') }}" id="filter-form">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="kelas" class="form-label">Kelas</label>
                            <select class="form-select" name="kelas" id="kelas">
                                <option value="all" {{ $kelas == 'all' ? 'selected' : '' }}>Semua Kelas</option>
                                @foreach($kelasList as $k)
                                    <option value="{{ $k }}" {{ $kelas == $k ? 'selected' : '' }}>{{ $k }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tanggal" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" id="tanggal" value="{{ $tanggal }}">
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-filter"></i> Terapkan Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-primary">{{ $hadirMasuk + $hadirPulang }}</div>
            <div class="stat-label">Hadir</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-warning">{{ $izin }}</div>
            <div class="stat-label">Izin</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-danger">{{ $tidakHadir }}</div>
            <div class="stat-label">Tidak Hadir</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-info">{{ $totalStudents }}</div>
            <div class="stat-label">Total Siswa</div>
        </div>
    </div>
</div>

<!-- Simple Statistics -->
<div class="row mb-4">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart"></i> Statistik Kehadiran Hari Ini
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Hadir Masuk</strong></td>
                            <td class="text-end">{{ $hadirMasuk }}</td>
                        </tr>
                        <tr>
                            <td><strong>Hadir Pulang</strong></td>
                            <td class="text-end">{{ $hadirPulang }}</td>
                        </tr>
                        <tr>
                            <td><strong>Izin</strong></td>
                            <td class="text-end">{{ $izin }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tidak Hadir</strong></td>
                            <td class="text-end">{{ $tidakHadir }}</td>
                        </tr>
                        <tr class="table-light">
                            <td><strong>Total Kehadiran</strong></td>
                            <td class="text-end"><strong>{{ $hadirMasuk + $hadirPulang + $izin + $tidakHadir }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Informasi Filter
            </div>
            <div class="card-body">
                <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}</p>
                <p><strong>Kelas:</strong> {{ $kelas == 'all' ? 'Semua Kelas' : $kelas }}</p>
                <p><strong>Total Siswa:</strong> {{ $totalStudents }} siswa</p>
                <p><strong>Persentase Kehadiran:</strong> 
                    @if($totalStudents > 0)
                        {{ round((($hadirMasuk + $hadirPulang) / $totalStudents) * 100, 1) }}%
                    @else
                        0%
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-table"></i> Data Absensi Siswa
            </div>
            <div class="card-body">
                @if($students->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>NIS</th>
                                    <th>Kelas</th>
                                    <th>Status Absensi</th>
                                    <th>Waktu</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $index => $student)
                                    @php
                                        $attendance = $student->attendances->first();
                                        $status = $attendance ? $attendance->status : 'Belum Absen';
                                        
                                        $statusClass = 'secondary';
                                        if ($status == 'Hadir Masuk' || $status == 'Hadir Pulang') $statusClass = 'success';
                                        if ($status == 'Izin') $statusClass = 'warning';
                                        if ($status == 'Tidak Hadir') $statusClass = 'danger';
                                        if ($status == 'Belum Absen') $statusClass = 'secondary';
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $student->user->name ?? 'N/A' }}</td>
                                        <td>{{ $student->nis ?? 'N/A' }}</td>
                                        <td>{{ $student->kelas ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $statusClass }}">
                                                {{ $status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($attendance)
                                                {{ $attendance->waktu }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($attendance)
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewAttendanceDetail({{ $attendance->id }})">
                                                    <i class="bi bi-eye"></i> Detail
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-people display-4 text-muted"></i>
                        <h5 class="mt-3">Tidak ada data siswa</h5>
                        <p class="text-muted">Belum ada siswa yang terdaftar di sistem.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function viewAttendanceDetail(id) {
        alert('Detail absensi ID: ' + id + '\nFitur detail akan dikembangkan lebih lanjut.');
    }
    
    // Auto-refresh page every 60 seconds
    setTimeout(function() {
        location.reload();
    }, 60000);
</script>
@endpush
@endsection