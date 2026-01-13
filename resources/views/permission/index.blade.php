@extends('layouts.app')

@section('title', 'Verifikasi Izin Siswa')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="h4">Verifikasi Izin Siswa</h2>
        <p class="text-muted">Verifikasi pengajuan izin siswa</p>
    </div>
</div>

<!-- Filter Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-funnel"></i> Filter Data
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="filter-kelas" class="form-label">Kelas</label>
                        <select class="form-select" id="filter-kelas">
                            <option value="">Semua Kelas</option>
                            <option value="X IPA 1">X IPA 1</option>
                            <option value="X IPA 2">X IPA 2</option>
                            <option value="X IPA 3">X IPA 3</option>
                            <option value="XI IPA 1">XI IPA 1</option>
                            <option value="XI IPA 2">XI IPA 2</option>
                            <option value="XII IPA 1">XII IPA 1</option>
                            <option value="XII IPA 2">XII IPA 2</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="filter-status" class="form-label">Status</label>
                        <select class="form-select" id="filter-status">
                            <option value="">Semua Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Disetujui">Disetujui</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="filter-tanggal" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="filter-tanggal">
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" onclick="applyFilters()">
                        <i class="bi bi-filter"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-warning">{{ $pendingCount }}</div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-success">{{ $approvedCount }}</div>
            <div class="stat-label">Disetujui</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-danger">{{ $rejectedCount }}</div>
            <div class="stat-label">Ditolak</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-info">{{ $totalCount }}</div>
            <div class="stat-label">Total</div>
        </div>
    </div>
</div>

<!-- Permissions Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clipboard-check"></i> Daftar Pengajuan Izin
            </div>
            <div class="card-body">
                @if($permissions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Siswa</th>
                                    <th>NIS</th>
                                    <th>Kelas</th>
                                    <th>Tanggal</th>
                                    <th>Alasan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permissions as $permission)
                                <tr>
                                    <td>{{ $permission->student->user->name ?? 'N/A' }}</td>
                                    <td>{{ $permission->student->nis ?? 'N/A' }}</td>
                                    <td>{{ $permission->student->kelas ?? 'N/A' }}</td>
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
                                    <td>
                                        @if($permission->status === 'Pending')
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-success" 
                                                        onclick="updateStatus({{ $permission->id }}, 'Disetujui')">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                                <button class="btn btn-danger" 
                                                        onclick="updateStatus({{ $permission->id }}, 'Ditolak')">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                        @endif
                                        
                                        @if($permission->foto_bukti)
                                            <button class="btn btn-sm btn-outline-primary mt-1" 
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
                        <p class="mt-3">Tidak ada pengajuan izin.</p>
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
    function applyFilters() {
        const kelas = $('#filter-kelas').val();
        const status = $('#filter-status').val();
        const tanggal = $('#filter-tanggal').val();
        
        let url = new URL(window.location.href);
        let params = new URLSearchParams(url.search);
        
        if (kelas) params.set('kelas', kelas);
        if (status) params.set('status', status);
        if (tanggal) params.set('tanggal', tanggal);
        
        window.location.href = url.pathname + '?' + params.toString();
    }
    
    function showReason(reason) {
        $('#reasonText').text(reason);
        new bootstrap.Modal(document.getElementById('reasonModal')).show();
    }
    
    function showPhoto(photoPath) {
        $('#photoImage').attr('src', '/storage/' + photoPath);
        new bootstrap.Modal(document.getElementById('photoModal')).show();
    }
    
    function updateStatus(permissionId, status) {
        if (!confirm(`Apakah Anda yakin ingin ${status.toLowerCase()} izin ini?`)) {
            return;
        }
        
        $.ajax({
            url: '{{ route("permission.updateStatus", ":id") }}'.replace(':id', permissionId),
            method: 'POST',
            data: {
                status: status,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                alert(response.message);
                location.reload();
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Terjadi kesalahan.');
            }
        });
    }
    
    // Initialize filters from URL
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        $('#filter-kelas').val(urlParams.get('kelas') || '');
        $('#filter-status').val(urlParams.get('status') || '');
        $('#filter-tanggal').val(urlParams.get('tanggal') || '');
    });
</script>
@endpush
@endsection