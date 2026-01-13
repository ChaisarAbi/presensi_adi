@extends('layouts.app')

@section('title', 'Scan QR Code')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-qr-code-scan"></i> Scan QR Code Absensi</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Pilih jenis absensi dan scan QR Code yang tersedia. Pastikan kamera HP Anda diizinkan.
                </div>
                
                <!-- Attendance Type Selection -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-sun-fill display-4 text-warning mb-3"></i>
                                <h5>Absensi Masuk</h5>
                                <p class="text-muted">Jam: 07:00 WIB</p>
                                <button id="btn-scan-masuk" class="btn btn-warning w-100">
                                    <i class="bi bi-camera"></i> Scan untuk Masuk
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-moon-fill display-4 text-primary mb-3"></i>
                                <h5>Absensi Pulang</h5>
                                <p class="text-muted">Jam: 14:00 WIB</p>
                                <button id="btn-scan-pulang" class="btn btn-primary w-100">
                                    <i class="bi bi-camera"></i> Scan untuk Pulang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- QR Scanner -->
                <div id="qr-reader" style="display: none;"></div>
                <div id="qr-reader-results"></div>
                
                <!-- Status Info -->
                <div id="attendance-status" class="mt-4" style="display: none;">
                    <div class="card">
                        <div class="card-body text-center">
                            <div id="status-icon" class="display-4 mb-3"></div>
                            <h4 id="status-title"></h4>
                            <p id="status-message" class="mb-0"></p>
                        </div>
                    </div>
                </div>
                
                <!-- Manual Input (Fallback) -->
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-keyboard"></i> Input Manual Token QR
                    </div>
                    <div class="card-body">
                        <form id="manual-form">
                            <div class="mb-3">
                                <label for="token-input" class="form-label">Token QR Code</label>
                                <input type="text" class="form-control" id="token-input" 
                                       placeholder="Masukkan token QR Code">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <button type="button" onclick="submitManual('masuk')" 
                                            class="btn btn-warning w-100">
                                        <i class="bi bi-sun-fill"></i> Absensi Masuk
                                    </button>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <button type="button" onclick="submitManual('pulang')" 
                                            class="btn btn-primary w-100">
                                        <i class="bi bi-moon-fill"></i> Absensi Pulang
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    #qr-reader {
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
    }
    
    #qr-reader__dashboard_section {
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    /* Mobile optimization for scanner */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }
        
        .display-4 {
            font-size: 2.5rem;
        }
        
        #qr-reader {
            max-width: 100%;
        }
        
        #qr-reader__dashboard_section {
            padding: 0.75rem;
        }
        
        .row.mb-4 {
            margin-left: -0.5rem;
            margin-right: -0.5rem;
        }
        
        .row.mb-4 > [class*="col-"] {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        
        .card.text-center {
            margin-bottom: 0.75rem;
        }
        
        .card.text-center .card-body {
            padding: 1rem 0.75rem;
        }
        
        .btn {
            padding: 0.625rem 0.75rem;
            font-size: 0.95rem;
        }
        
        .alert {
            margin-bottom: 1rem;
            padding: 0.75rem;
            font-size: 0.9rem;
        }
        
        .modal-dialog {
            margin: 0.5rem;
        }
        
        .modal-content {
            border-radius: 12px;
        }
        
        #qr-reader__scan_region video {
            width: 100% !important;
            height: auto !important;
            max-height: 300px;
        }
        
        .html5-qrcode-element {
            padding: 0.625rem 1rem;
            font-size: 0.95rem;
        }
    }
    
    /* Extra small devices */
    @media (max-width: 575.98px) {
        .display-4 {
            font-size: 2rem;
        }
        
        h5 {
            font-size: 1.1rem;
        }
        
        .card.text-center .card-body {
            padding: 0.75rem 0.5rem;
        }
        
        #qr-reader__scan_region video {
            max-height: 250px;
        }
        
        .html5-qrcode-element {
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
        }
    }
</style>
@endpush

@push('scripts')
<!-- HTML5 QR Code Scanner -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
    let currentScanType = '';
    let html5QrcodeScanner = null;
    
    // Initialize scanner
    function initScanner(type) {
        currentScanType = type;
        
        // Hide buttons, show scanner
        document.querySelector('.row.mb-4').style.display = 'none';
        document.getElementById('qr-reader').style.display = 'block';
        
        // Initialize scanner
        html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", 
            { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            },
            false
        );
        
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    }
    
    // Scan success handler
    function onScanSuccess(decodedText, decodedResult) {
        // Stop scanner
        html5QrcodeScanner.clear();
        document.getElementById('qr-reader').style.display = 'none';
        
        // Process attendance
        processAttendance(decodedText, currentScanType);
    }
    
    // Scan failure handler
    function onScanFailure(error) {
        // console.warn(`QR scan failed: ${error}`);
    }
    
    // Process attendance
    function processAttendance(token, type) {
        const url = type === 'masuk' 
            ? '{{ route("attendance.scan.masuk") }}'
            : '{{ route("attendance.scan.pulang") }}';
        
        showLoading();
        
        $.ajax({
            url: url,
            method: 'POST',
            data: { token: token },
            success: function(response) {
                showSuccess(response.message);
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                showError(error);
            }
        });
    }
    
    // Manual submission
    function submitManual(type) {
        const token = document.getElementById('token-input').value.trim();
        
        if (!token) {
            alert('Masukkan token QR Code terlebih dahulu.');
            return;
        }
        
        processAttendance(token, type);
    }
    
    // UI Helpers
    function showLoading() {
        document.getElementById('attendance-status').style.display = 'block';
        document.getElementById('status-icon').innerHTML = '<i class="bi bi-hourglass-split text-warning"></i>';
        document.getElementById('status-title').textContent = 'Memproses...';
        document.getElementById('status-message').textContent = 'Sedang memvalidasi QR Code...';
    }
    
    function showSuccess(message) {
        document.getElementById('status-icon').innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
        document.getElementById('status-title').textContent = 'Berhasil!';
        document.getElementById('status-message').textContent = message;
        
        // Reset after 3 seconds
        setTimeout(resetScanner, 3000);
    }
    
    function showError(message) {
        document.getElementById('status-icon').innerHTML = '<i class="bi bi-exclamation-triangle-fill text-danger"></i>';
        document.getElementById('status-title').textContent = 'Gagal!';
        document.getElementById('status-message').textContent = message;
        
        // Reset after 5 seconds
        setTimeout(resetScanner, 5000);
    }
    
    function resetScanner() {
        document.getElementById('attendance-status').style.display = 'none';
        document.querySelector('.row.mb-4').style.display = 'flex';
        document.getElementById('qr-reader').style.display = 'none';
        document.getElementById('token-input').value = '';
        
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
        }
    }
    
    // Event Listeners
    document.getElementById('btn-scan-masuk').addEventListener('click', function() {
        initScanner('masuk');
    });
    
    document.getElementById('btn-scan-pulang').addEventListener('click', function() {
        initScanner('pulang');
    });
    
    // Check camera permission
    document.addEventListener('DOMContentLoaded', function() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            document.querySelector('.alert-info').innerHTML += 
                '<br><strong class="text-danger">Kamera tidak didukung di browser ini. Gunakan input manual.</strong>';
        }
    });
</script>
@endpush
@endsection