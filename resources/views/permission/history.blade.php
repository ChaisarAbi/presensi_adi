@extends('layouts.app')

@section('title', 'Riwayat Izin')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="h4">Riwayat Pengajuan Izin</h2>
        <p class="text-muted">Riwayat pengajuan izin Anda</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Izin</h4>
                    <a href="{{ route('permission.create') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle"></i> Ajukan Izin Baru
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($permissions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Alasan</th>
                                    <th>Status</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permissions as $permission)
                                <tr>
                                    <td>{{ $permission->tanggal->format('d/m/Y') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="showReason('{{ $permission->alasan }}')">
                                            <i class="bi bi-eye"></i> Lihat
                                        </button>
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
                                    <td>{{ $permission->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($permission->foto_bukti)
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="showPhoto('{{ $permission->foto_bukti }}')">
                                                <i class="bi bi-image"></i> Foto
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center">
                        {{ $permissions->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-clipboard-x display-4 text-muted"></i>
                        <p class="mt-3">Belum ada riwayat pengajuan izin.</p>
                        <a href="{{ route('permission.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Ajukan Izin Pertama
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal for Reason -->
<div class="modal fade" id="reasonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alasan Izin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="reasonText"></p>
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

@push('scripts')
<script>
    function showReason(reason) {
        $('#reasonText').text(reason);
        new bootstrap.Modal(document.getElementById('reasonModal')).show();
    }
    
    function showPhoto(photoPath) {
        $('#photoImage').attr('src', '/storage/' + photoPath);
        new bootstrap.Modal(document.getElementById('photoModal')).show();
    }
</script>
@endpush
@endsection