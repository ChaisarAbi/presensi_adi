@extends('layouts.app')

@section('title', 'Laporan Absensi Kelas ' . $kelas)

@push('styles')
<style>
    .report-header {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        color: white;
        border-radius: 10px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .summary-card {
        border-radius: 10px;
        transition: transform 0.2s;
        height: 100%;
    }
    
    .summary-card:hover {
        transform: translateY(-3px);
    }
    
    .summary-number {
        font-size: 2rem;
        font-weight: bold;
        line-height: 1.2;
    }
    
    .summary-label {
        font-size: 0.9rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }
    
    .student-row {
        transition: background-color 0.2s;
    }
    
    .student-row:hover {
        background-color: #f8f9fa;
    }
    
    .attendance-percentage {
        font-weight: bold;
    }
    
    .percentage-good {
        color: #28a745;
    }
    
    .percentage-warning {
        color: #ffc107;
    }
    
    .percentage-danger {
        color: #dc3545;
    }
    
    @media print {
        .no-print {
            display: none !important;
        }
        
        .report-header {
            background: #0d6efd !important;
            -webkit-print-color-adjust: exact;
        }
        
        .table {
            border-collapse: collapse;
        }
        
        .table th, .table td {
            border: 1px solid #dee2e6;
        }
    }
</style>
@endpush

@section('content')
<div class="report-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="h3 mb-2">Laporan Absensi Kelas</h1>
            <h2 class="h1 mb-3">{{ $kelas }}</h2>
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

<!-- Class Summary -->
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="card summary-card">
            <div class="card-body text-center">
                <div class="summary-number text-primary">{{ $classSummary['total_students'] }}</div>
                <div class="summary-label">Total Siswa</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card summary-card">
            <div class="card-body text-center">
                <div class="summary-number text-success">{{ $classSummary['total_hadir_masuk'] + $classSummary['total_hadir_pulang'] }}</div>
                <div class="summary-label">Total Hadir</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card summary-card">
            <div class="card-body text-center">
                <div class="summary-number text-warning">{{ $classSummary['total_terlambat'] + $classSummary['total_izin'] }}</div>
                <div class="summary-label">Terlambat & Izin</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card summary-card">
            <div class="card-body text-center">
                <div class="summary-number text-danger">{{ $classSummary['total_tidak_hadir'] }}</div>
                <div class="summary-label">Tidak Hadir</div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Statistics -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart"></i> Statistik Detail Kelas
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Status</th>
                                <th class="text-end">Jumlah</th>
                                <th class="text-end">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-primary">Hadir Masuk</span></td>
                                <td class="text-end">{{ $classSummary['total_hadir_masuk'] }}</td>
                                <td class="text-end">
                                    @php
                                        $totalRecords = $classSummary['total_hadir_masuk'] + $classSummary['total_terlambat'] + 
                                                       $classSummary['total_hadir_pulang'] + $classSummary['total_izin'] + 
                                                       $classSummary['total_tidak_hadir'];
                                        $percentage = $totalRecords > 0 ? round(($classSummary['total_hadir_masuk'] / $totalRecords) * 100, 1) : 0;
                                    @endphp
                                    {{ $percentage }}%
                                </td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning">Terlambat</span></td>
                                <td class="text-end">{{ $classSummary['total_terlambat'] }}</td>
                                <td class="text-end">
                                    @php
                                        $percentage = $totalRecords > 0 ? round(($classSummary['total_terlambat'] / $totalRecords) * 100, 1) : 0;
                                    @endphp
                                    {{ $percentage }}%
                                </td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">Hadir Pulang</span></td>
                                <td class="text-end">{{ $classSummary['total_hadir_pulang'] }}</td>
                                <td class="text-end">
                                    @php
                                        $percentage = $totalRecords > 0 ? round(($classSummary['total_hadir_pulang'] / $totalRecords) * 100, 1) : 0;
                                    @endphp
                                    {{ $percentage }}%
                                </td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning">Izin</span></td>
                                <td class="text-end">{{ $classSummary['total_izin'] }}</td>
                                <td class="text-end">
                                    @php
                                        $percentage = $totalRecords > 0 ? round(($classSummary['total_izin'] / $totalRecords) * 100, 1) : 0;
                                    @endphp
                                    {{ $percentage }}%
                                </td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-danger">Tidak Hadir</span></td>
                                <td class="text-end">{{ $classSummary['total_tidak_hadir'] }}</td>
                                <td class="text-end">
                                    @php
                                        $percentage = $totalRecords > 0 ? round(($classSummary['total_tidak_hadir'] / $totalRecords) * 100, 1) : 0;
                                    @endphp
                                    {{ $percentage }}%
                                </td>
                            </tr>
                            <tr class="table-light">
                                <td><strong>Total</strong></td>
                                <td class="text-end"><strong>{{ $totalRecords }}</strong></td>
                                <td class="text-end"><strong>100%</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Student List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-table"></i> Data Absensi Per Siswa
                </div>
                <div class="no-print">
                    <span class="badge bg-info">
                        Rata-rata Kehadiran: {{ $classSummary['average_attendance_percentage'] }}%
                    </span>
                </div>
            </div>
            <div class="card-body">
                @if(count($reportData) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>NIS</th>
                                    <th class="text-center">Hadir Masuk</th>
                                    <th class="text-center">Terlambat</th>
                                    <th class="text-center">Hadir Pulang</th>
                                    <th class="text-center">Izin</th>
                                    <th class="text-center">Tidak Hadir</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData as $index => $student)
                                    @php
                                        $percentageClass = 'percentage-good';
                                        if ($student['attendance_percentage'] < 70) {
                                            $percentageClass = 'percentage-danger';
                                        } elseif ($student['attendance_percentage'] < 85) {
                                            $percentageClass = 'percentage-warning';
                                        }
                                    @endphp
                                    <tr class="student-row">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $student['name'] }}</td>
                                        <td>{{ $student['nis'] }}</td>
                                        <td class="text-center">{{ $student['hadir_masuk'] }}</td>
                                        <td class="text-center">{{ $student['terlambat'] }}</td>
                                        <td class="text-center">{{ $student['hadir_pulang'] }}</td>
                                        <td class="text-center">{{ $student['izin'] }}</td>
                                        <td class="text-center">{{ $student['tidak_hadir'] }}</td>
                                        <td class="text-center">{{ $student['total_attendance'] }}</td>
                                        <td class="text-center attendance-percentage {{ $percentageClass }}">
                                            {{ $student['attendance_percentage'] }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3"><strong>Total</strong></td>
                                    <td class="text-center"><strong>{{ $classSummary['total_hadir_masuk'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $classSummary['total_terlambat'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $classSummary['total_hadir_pulang'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $classSummary['total_izin'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $classSummary['total_tidak_hadir'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $totalRecords }}</strong></td>
                                    <td class="text-center"><strong>{{ $classSummary['average_attendance_percentage'] }}%</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-people display-4 text-muted"></i>
                        <h5 class="mt-3">Tidak ada data siswa</h5>
                        <p class="text-muted">Belum ada siswa yang terdaftar di kelas ini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Footer Notes -->
<div class="row mt-4 no-print">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="bi bi-info-circle"></i> Keterangan:</h6>
                        <ul class="mb-0">
                            <li><span class="badge bg-primary">Hadir Masuk</span>: Absen sebelum jam 07:00</li>
                            <li><span class="badge bg-warning">Terlambat</span>: Absen antara 07:01 - 09:00</li>
                            <li><span class="badge bg-success">Hadir Pulang</span>: Absen pulang setelah jam 14:00</li>
                            <li><span class="badge bg-warning">Izin</span>: Izin dengan bukti foto</li>
                            <li><span class="badge bg-danger">Tidak Hadir</span>: Tidak ada absensi</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-graph-up"></i> Interpretasi Persentase:</h6>
                        <ul class="mb-0">
                            <li><span class="percentage-good">≥ 85%</span>: Kehadiran Baik</li>
                            <li><span class="percentage-warning">70-84%</span>: Perlu Perhatian</li>
                            <li><span class="percentage-danger">< 70%</span>: Perlu Tindakan</li>
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