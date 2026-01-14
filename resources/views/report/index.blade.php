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
        <p class="text-muted">Generate laporan absensi dalam format PDF</p>
    </div>
</div>

<!-- PDF Generation Card -->
<div class="row mb-4 justify-content-center">
    <div class="col-md-6 mb-4">
        <div class="card report-card border-warning">
            <div class="card-body text-center">
                <div class="report-icon text-warning">
                    <i class="bi bi-file-earmark-pdf"></i>
                </div>
                <h4 class="card-title">Generate Laporan PDF</h4>
                <p class="card-text">Generate laporan absensi dalam format PDF untuk download langsung. Pilih antara laporan per kelas atau per siswa.</p>
                <a href="{{ route('report.pdf.form') }}" class="btn btn-warning btn-lg">
                    <i class="bi bi-file-earmark-pdf"></i> Buat Laporan PDF
                </a>
                <div class="mt-3">
                    <small class="text-muted">File PDF akan otomatis terdownload setelah proses generate selesai.</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection        
