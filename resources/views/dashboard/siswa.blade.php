@extends('layouts.app')

@section('title', 'Dashboard Siswa')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="h4">Selamat Datang, {{ $student->user->name }}!</h2>
        <p class="text-muted">NIS: {{ $student->nis }} | Kelas: {{ $student->kelas }}</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4 g-2">
    <div class="col-6 col-sm-3 mb-2">
        <div class="card stat-card h-100">
            <div class="stat-number text-primary">
                @if($todayAttendance && $todayAttendance->status === 'Hadir Masuk')
                    <i class="bi bi-check-circle-fill"></i>
                @elseif($todayAttendance && $todayAttendance->status === 'Hadir Pulang')
                    <i class="bi bi-check-all"></i>
                @else
                    <i class="bi bi-clock"></i>
                @endif
            </div>
            <div class="stat-label">Status Hari Ini</div>
            @if($todayAttendance)
                <span class="attendance-status status-hadir d-block mt-1">{{ $todayAttendance->status }}</span>
            @else
                <span class="attendance-status status-tidak-hadir d-block mt-1">Belum Absen</span>
            @endif
        </div>
    </div>
    
    <div class="col-6 col-sm-3 mb-2">
        <div class="card stat-card h-100">
            <div class="stat-number text-success">
                {{ $monthlyStats->get('Hadir Masuk', 0) + $monthlyStats->get('Hadir Pulang', 0) }}
            </div>
            <div class="stat-label">Hadir Bulan Ini</div>
        </div>
    </div>
    
    <div class="col-6 col-sm-3 mb-2">
        <div class="card stat-card h-100">
            <div class="stat-number text-warning">
                {{ $monthlyStats->get('Izin', 0) }}
            </div>
            <div class="stat-label">Izin Bulan Ini</div>
        </div>
    </div>
    
    <div class="col-6 col-sm-3 mb-2">
        <div class="card stat-card h-100">
            <div class="stat-number text-danger">
                {{ $pendingPermissions }}
            </div>
            <div class="stat-label">Izin Pending</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning-charge"></i> Aksi Cepat
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('attendance.scanner') }}" class="btn btn-primary w-100">
                            <i class="bi bi-qr-code-scan"></i> Scan QR Absensi
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('permission.create') }}" class="btn btn-warning w-100">
                            <i class="bi bi-clipboard-plus"></i> Ajukan Izin
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('attendance.history') }}" class="btn btn-info w-100 text-white">
                            <i class="bi bi-clock-history"></i> Riwayat Absensi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tracking Historis Izin dan Absen -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-clock-history"></i> Tracking Historis Izin
            </div>
            <div class="card-body">
                @if($recentPermissions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Alasan</th>
                                    <th>Status</th>
                                    <th>Foto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPermissions as $permission)
                                <tr>
                                    <td>{{ $permission->tanggal->format('d/m/Y') }}</td>
                                    <td>
                                        <small class="text-truncate d-block" style="max-width: 150px;" 
                                               title="{{ $permission->alasan }}">
                                            {{ Str::limit($permission->alasan, 30) }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($permission->status === 'Pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($permission->status === 'Disetujui')
                                            <span class="badge bg-success">Disetujui</span>
                                        @else
                                            <span class="badge bg-danger">Ditolak</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($permission->foto_bukti)
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="showPhoto('{{ $permission->foto_bukti }}')">
                                                <i class="bi bi-image"></i>
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
                    <div class="text-center mt-3">
                        <a href="{{ route('permission.history') }}" class="btn btn-outline-info">
                            <i class="bi bi-list-ul"></i> Lihat Semua Izin
                        </a>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-clipboard-x display-4 text-muted"></i>
                        <p class="mt-3">Belum ada riwayat izin.</p>
                        <a href="{{ route('permission.create') }}" class="btn btn-info text-white">
                            <i class="bi bi-plus-circle"></i> Ajukan Izin
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-calendar-week"></i> Tracking Historis Absensi
            </div>
            <div class="card-body">
                @if($recentAttendances->count() > 0)
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
                                @foreach($recentAttendances as $attendance)
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
                    <div class="text-center mt-3">
                        <a href="{{ route('attendance.history') }}" class="btn btn-outline-primary">
                            <i class="bi bi-list-ul"></i> Lihat Semua Absensi
                        </a>
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

<!-- Modal for Photo -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Foto Bukti Izin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="photoImage" src="" alt="Foto Bukti" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- Monthly Chart -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart"></i> Statistik Kehadiran Bulan Ini
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
    // Function to show photo in modal
    function showPhoto(photoPath) {
        $('#photoImage').attr('src', '/storage/' + photoPath);
        new bootstrap.Modal(document.getElementById('photoModal')).show();
    }
    
    // Monthly attendance chart
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Izin', 'Tidak Hadir'],
            datasets: [{
                data: [
                    {{ $monthlyStats->get('Hadir Masuk', 0) + $monthlyStats->get('Hadir Pulang', 0) }},
                    {{ $monthlyStats->get('Izin', 0) }},
                    {{ $monthlyStats->get('Tidak Hadir', 0) }}
                ],
                backgroundColor: [
                    '#198754', // Success green
                    '#ffc107', // Warning yellow
                    '#dc3545'  // Danger red
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
                }
            }
        }
    });
</script>
@endpush
@endsection