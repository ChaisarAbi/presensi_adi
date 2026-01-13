@extends('layouts.app')

@section('title', 'Daftar QR Code')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="h4">Daftar QR Code</h2>
        <p class="text-muted">Manajemen QR Code untuk absensi</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-qr-code"></i> QR Code Aktif</h4>
                    <a href="{{ route('qr.generate') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle"></i> Generate Baru
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($qrCodes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Token</th>
                                    <th>Dibuat</th>
                                    <th>Kadaluarsa</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($qrCodes as $qr)
                                <tr>
                                    <td><code>{{ $qr->token }}</code></td>
                                    <td>{{ $qr->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $qr->expired_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($qr->expired_at->isFuture())
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Kadaluarsa</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="copyToken('{{ $qr->token }}')">
                                            <i class="bi bi-copy"></i> Copy
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center">
                        {{ $qrCodes->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-qr-code display-4 text-muted"></i>
                        <p class="mt-3">Belum ada QR Code yang digenerate.</p>
                        <a href="{{ route('qr.generate') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Generate QR Code Pertama
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Informasi QR Code
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        <strong>QR Code aktif</strong> berlaku selama 2 jam
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-exclamation-triangle text-warning"></i>
                        <strong>QR Code kadaluarsa</strong> tidak dapat digunakan untuk absensi
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-clock text-primary"></i>
                        Waktu mengikuti <strong>server time</strong>
                    </li>
                    <li>
                        <i class="bi bi-shield-check text-info"></i>
                        Token QR Code di-generate secara <strong>random</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning-charge"></i> Aksi Cepat
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('qr.generate') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Generate QR Code Baru
                    </a>
                    <button class="btn btn-outline-secondary" onclick="validateQR()">
                        <i class="bi bi-check-circle"></i> Validasi Token
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyToken(token) {
        navigator.clipboard.writeText(token).then(function() {
            alert('Token berhasil disalin: ' + token);
        });
    }
    
    function validateQR() {
        const token = prompt('Masukkan token QR Code untuk validasi:');
        if (token) {
            $.ajax({
                url: '{{ route("qr.validate") }}',
                method: 'POST',
                data: { token: token },
                success: function(response) {
                    alert(response.message);
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || 'Token tidak valid.');
                }
            });
        }
    }
</script>
@endpush
@endsection