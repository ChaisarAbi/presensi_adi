@extends('layouts.app')

@section('title', 'Laporan Absensi')

@push('styles')
<style>
    .report-card {
        border-radius: 10px;
        transition: transform 0.2s;
        height: 100%;
    }
    
    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .report-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }
    
    .date-range-picker {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
    }
    
    @media (max-width: 768px) {
        .report-card {
            margin-bottom: 1rem;
        }
        
        .date-range-picker {
            padding: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="h4">Laporan Absensi</h2>
        <p class="text-muted">Generate laporan absensi per siswa atau per kelas</p>
    </div>
</div>

<!-- Report Type Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-4">
        <div class="card report-card border-primary">
            <div class="card-body text-center">
                <div class="report-icon text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
                <h4 class="card-title">Laporan Per Kelas</h4>
                <p class="card-text">Generate laporan absensi untuk seluruh siswa dalam satu kelas.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#classReportModal">
                    <i class="bi bi-file-earmark-text"></i> Buat Laporan
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card report-card border-success">
            <div class="card-body text-center">
                <div class="report-icon text-success">
                    <i class="bi bi-person-fill"></i>
                </div>
                <h4 class="card-title">Laporan Per Siswa</h4>
                <p class="card-text">Generate laporan detail absensi untuk satu siswa tertentu.</p>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#studentReportModal">
                    <i class="bi bi-file-earmark-person"></i> Buat Laporan
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card report-card border-warning">
            <div class="card-body text-center">
                <div class="report-icon text-warning">
                    <i class="bi bi-file-earmark-pdf"></i>
                </div>
                <h4 class="card-title">Generate PDF</h4>
                <p class="card-text">Generate laporan dalam format PDF untuk download langsung.</p>
                <a href="{{ route('report.pdf.form') }}" class="btn btn-warning">
                    <i class="bi bi-file-earmark-pdf"></i> Buat PDF
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Class Report Modal -->
<div class="modal fade" id="classReportModal" tabindex="-1" aria-labelledby="classReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="classReportModalLabel">
                    <i class="bi bi-people-fill"></i> Laporan Per Kelas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('report.class') }}" method="GET">
                <div class="modal-body">
                    <div class="date-range-picker mb-4">
                        <h6 class="mb-3"><i class="bi bi-calendar-range"></i> Rentang Tanggal</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="class_start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="class_start_date" name="start_date" 
                                       value="{{ date('Y-m-d', strtotime('-7 days')) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="class_end_date" class="form-label">Tanggal Akhir</label>
                                <input type="date" class="form-control" id="class_end_date" name="end_date" 
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="kelas" class="form-label">Pilih Kelas</label>
                        <select class="form-select" id="kelas" name="kelas" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas }}">{{ $kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Laporan akan menampilkan:
                        <ul class="mb-0 mt-2">
                            <li>Data absensi semua siswa dalam kelas terpilih</li>
                            <li>Statistik per siswa (Hadir, Terlambat, Izin, Tidak Hadir)</li>
                            <li>Ringkasan statistik kelas</li>
                            <li>Persentase kehadiran per siswa</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-file-earmark-text"></i> Generate Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Student Report Modal -->
<div class="modal fade" id="studentReportModal" tabindex="-1" aria-labelledby="studentReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentReportModalLabel">
                    <i class="bi bi-person-fill"></i> Laporan Per Siswa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('report.student') }}" method="GET">
                <div class="modal-body">
                    <div class="date-range-picker mb-4">
                        <h6 class="mb-3"><i class="bi bi-calendar-range"></i> Rentang Tanggal</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="student_start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="student_start_date" name="start_date" 
                                       value="{{ date('Y-m-d', strtotime('-30 days')) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="student_end_date" class="form-label">Tanggal Akhir</label>
                                <input type="date" class="form-control" id="student_end_date" name="end_date" 
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Pilih Siswa</label>
                        <select class="form-select" id="student_id" name="student_id" required>
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">
                                    {{ $student->user->name ?? 'N/A' }} ({{ $student->nis ?? 'N/A' }}) - {{ $student->kelas ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Laporan akan menampilkan:
                        <ul class="mb-0 mt-2">
                            <li>Detail absensi harian siswa</li>
                            <li>Statistik kehadiran (Hadir, Terlambat, Izin, Tidak Hadir)</li>
                            <li>Data izin yang diajukan</li>
                            <li>Grafik kehadiran bulanan</li>
                            <li>Persentase kehadiran keseluruhan</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-file-earmark-person"></i> Generate Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Set default dates for modals
    document.addEventListener('DOMContentLoaded', function() {
        // Set class report date range (last 7 days)
        const classStartDate = new Date();
        classStartDate.setDate(classStartDate.getDate() - 7);
        document.getElementById('class_start_date').valueAsDate = classStartDate;
        document.getElementById('class_end_date').valueAsDate = new Date();
        
        // Set student report date range (last 30 days)
        const studentStartDate = new Date();
        studentStartDate.setDate(studentStartDate.getDate() - 30);
        document.getElementById('student_start_date').valueAsDate = studentStartDate;
        document.getElementById('student_end_date').valueAsDate = new Date();
        
        // Validate date ranges
        const validateDateRange = (startId, endId) => {
            const startDate = new Date(document.getElementById(startId).value);
            const endDate = new Date(document.getElementById(endId).value);
            
            if (startDate > endDate) {
                alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir!');
                document.getElementById(startId).value = '';
                document.getElementById(endId).value = '';
                return false;
            }
            return true;
        };
        
        // Add validation to date inputs
        document.getElementById('class_start_date').addEventListener('change', function() {
            validateDateRange('class_start_date', 'class_end_date');
        });
        
        document.getElementById('class_end_date').addEventListener('change', function() {
            validateDateRange('class_start_date', 'class_end_date');
        });
        
        document.getElementById('student_start_date').addEventListener('change', function() {
            validateDateRange('student_start_date', 'student_end_date');
        });
        
        document.getElementById('student_end_date').addEventListener('change', function() {
            validateDateRange('student_start_date', 'student_end_date');
        });
        
        // Close modal after form submit
        const classReportForm = document.querySelector('#classReportModal form');
        const studentReportForm = document.querySelector('#studentReportModal form');
        
        if (classReportForm) {
            classReportForm.addEventListener('submit', function() {
                const modal = bootstrap.Modal.getInstance(document.getElementById('classReportModal'));
                if (modal) {
                    modal.hide();
                }
            });
        }
        
        if (studentReportForm) {
            studentReportForm.addEventListener('submit', function() {
                const modal = bootstrap.Modal.getInstance(document.getElementById('studentReportModal'));
                if (modal) {
                    modal.hide();
                }
            });
        }
    });
</script>
@endpush
@endsection             
