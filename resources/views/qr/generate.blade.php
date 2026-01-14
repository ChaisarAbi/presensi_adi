@extends('layouts.app')

@section('title', 'Generate QR Code')

@push('styles')
<style>
    /* Fix padding overflow for QR generate page */
    .container {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        max-width: 100%;
        overflow-x: hidden;
    }
    
    @media (min-width: 768px) {
        .container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }
    
    /* Mobile optimization */
    @media (max-width: 768px) {
        .container {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        
        .card {
            margin-left: -0.5rem;
            margin-right: -0.5rem;
            border-radius: 0;
            border-left: none;
            border-right: none;
        }
        
        .card-header {
            border-radius: 0 !important;
        }
        
        .card-body {
            padding: 0.75rem;
        }
        
        .row {
            margin-left: -0.5rem;
            margin-right: -0.5rem;
        }
        
        .row > [class*="col-"] {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        
        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
        
        .d-flex.gap-2 {
            flex-wrap: wrap;
        }
        
        .d-flex.gap-2 .btn {
            flex: 1;
            min-width: 120px;
        }
        
        svg {
            max-width: 100%;
            height: auto;
        }
        
        code {
            word-break: break-all;
            font-size: 0.85rem;
            padding: 0.25rem 0.5rem;
            background: #f8f9fa;
            border-radius: 4px;
            display: inline-block;
            max-width: 100%;
            overflow-x: auto;
        }
    }
    
    /* Extra small devices */
    @media (max-width: 575.98px) {
        .container {
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }
        
        .card-body {
            padding: 0.5rem;
        }
        
        .alert {
            margin: 0.5rem -0.5rem;
            border-radius: 0;
        }
        
        .d-flex.gap-2 .btn {
            min-width: 100px;
            font-size: 0.85rem;
            padding: 0.5rem 0.25rem;
        }
        
        h4.mb-0 {
            font-size: 1.1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-qr-code"></i> Generate QR Code Baru</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> QR Code ini akan berlaku selama 2 jam dari waktu generate.
                </div>
                
                <div class="text-center mb-4">
                    <div class="mb-3">
                        {!! $qrCode !!}
                    </div>
                    <div class="mb-3">
                        <p class="mb-1"><strong>Token:</strong> <code>{{ $token }}</code></p>
                        <p class="mb-0"><strong>Kadaluarsa:</strong> {{ $expiredAt->format('d/m/Y H:i:s') }}</p>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-2">
                        <button onclick="window.print()" class="btn btn-outline-primary">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <button onclick="downloadQR()" class="btn btn-outline-success">
                            <i class="bi bi-download"></i> Download
                        </button>
                        <a href="{{ route('qr.index') }}" class="btn btn-outline-info">
                            <i class="bi bi-list-ul"></i> Lihat Semua QR
                        </a>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-clock-history"></i> QR Code Aktif
                            </div>
                            <div class="card-body">
                                @php
                                    $activeQRCodes = \App\Models\QrCode::where('expired_at', '>', now())->count();
                                @endphp
                                <h3 class="text-center">{{ $activeQRCodes }}</h3>
                                <p class="text-center text-muted">QR Code aktif</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-calendar-check"></i> Generate Baru
                            </div>
                            <div class="card-body">
                                <a href="{{ route('qr.generate') }}" class="btn btn-primary w-100">
                                    <i class="bi bi-plus-circle"></i> Generate QR Baru
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function downloadQR() {
        const svg = document.querySelector('svg');
        const serializer = new XMLSerializer();
        const source = serializer.serializeToString(svg);
        
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        
        img.onload = function() {
            canvas.width = img.width;
            canvas.height = img.height;
            ctx.drawImage(img, 0, 0);
            
            const link = document.createElement('a');
            link.download = 'qr-code-{{ $token }}.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        };
        
        img.src = 'data:image/svg+xml;base64,' + btoa(source);
    }
</script>
@endpush
@endsection</script>
