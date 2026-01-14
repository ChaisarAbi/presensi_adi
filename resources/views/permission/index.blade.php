@extends('layouts.app')

@section('title', 'Verifikasi Izin Siswa')

@push('styles')
<style>
    /* Fix overflow and padding issues */
    body {
        overflow-x: hidden;
    }
    
    .container-fluid, .container {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    @media (min-width: 768px) {
        .container-fluid, .container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }
    
    /* Mobile optimization for permission table */
    .permission-card {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        margin-bottom: 1rem;
        padding: 1rem;
        transition: all 0.2s;
        overflow: hidden;
    }
    
    .permission-card:hover {
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .permission-status {
        font-size: 0.8rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
    }
    
    .permission-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    
    /* Fix table overflow */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 0 -0.5rem;
        padding: 0 0.5rem;
    }
    
    .table-responsive table {
        min-width: 800px;
        margin-bottom: 0;
    }
    
    /* Mobile view */
    @media (max-width: 768px) {
        .table-responsive {
            display: none;
        }
        
        .mobile-permissions {
            display: block;
        }
        
        .permission-card {
            padding: 0.75rem;
            margin-left: -0.5rem;
            margin-right: -0.5rem;
            border-radius: 0;
            border-left: none;
            border-right: none;
        }
        
        .permission-info {
            font-size: 0.9rem;
        }
        
        /* Fix card body padding on mobile */
        .card-body {
            padding: 0.75rem;
        }
        
        /* Fix quick navigation on mobile */
        .quick-nav {
            margin-left: -0.5rem;
            margin-right: -0.5rem;
            padding: 0 0.5rem;
        }
        
        .quick-nav .btn {
            min-width: 100px;
            padding: 0.5rem 0.25rem;
            font-size: 0.8rem;
        }
        
        /* Fix filter form on mobile */
        .filter-form .row {
            margin-left: -0.25rem;
            margin-right: -0.25rem;
        }
        
        .filter-form .col-md-4 {
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }
    }
    
    /* Desktop view */
    @media (min-width: 769px) {
        .mobile-permissions {
            display: none;
        }
        
        .table-responsive {
            display: block;
        }
        
        /* Fix table width */
        .table-responsive table {
            width: 100%;
            min-width: auto;
        }
    }
    
    /* Filter form styling */
    .filter-form .form-control,
    .filter-form .form-select {
        margin-bottom: 0.5rem;
    }
    
    /* Quick navigation */
    .quick-nav {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    
    .quick-nav .btn {
        flex: 1;
        min-width: 120px;
    }
    
    /* Fix pagination overflow */
    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .page-item {
        margin-bottom: 0.25rem;
    }
    
    /* Fix modal overflow */
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
    
    /* Fix image in modal */
    #photoImage {
        max-width: 100%;
        height: auto;
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="h4">Verifikasi Izin Siswa</h2>
        <p class="text-muted">Verifikasi pengajuan izin siswa</p>
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
                <form method="GET" action="{{ route('permission.index') }}" class="filter-form">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="kelas" class="form-label">Kelas</label>
                            <select class="form-select" name="kelas" id="kelas">
                                <option value="">Semua Kelas</option>
                                @foreach($kelasList as $kelas)
                                    <option value="{{ $kelas }}" {{ request('kelas') == $kelas ? 'selected' : '' }}>
                                        {{ $kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status" id="status">
                                <option value="">Semua Status</option>
                                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Disetujui" {{ request('status') == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                                <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tanggal" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" id="tanggal" 
                                   value="{{ request('tanggal') }}">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between gap-3">
                        <a href="{{ route('permission.index') }}" class="btn btn-outline-secondary flex-fill">
                            <i class="bi bi-arrow-clockwise"></i> Reset Filter
                        </a>
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-filter"></i> Terapkan Filter
                        </button>
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

<!-- Desktop Table View -->
<div class="row d-none d-md-block">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-clipboard-check"></i> Daftar Pengajuan Izin
                    <span class="badge bg-primary ms-2">{{ $permissions->total() }}</span>
                </div>
                <div class="text-muted">
                    Menampilkan {{ $permissions->firstItem() ?? 0 }}-{{ $permissions->lastItem() ?? 0 }} dari {{ $permissions->total() }}
                </div>
            </div>
            <div class="card-body">
                @if($permissions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
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
                                @foreach($permissions as $index => $permission)
                                <tr>
                                    <td>{{ $permissions->firstItem() + $index }}</td>
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
                                                        onclick="updateStatus({{ $permission->id }}, 'Disetujui')"
                                                        title="Setujui">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                                <button class="btn btn-danger" 
                                                        onclick="updateStatus({{ $permission->id }}, 'Ditolak')"
                                                        title="Tolak">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                        
                                        @if($permission->foto_bukti)
                                            <button class="btn btn-sm btn-outline-primary mt-1" 
                                                    onclick="showPhoto('{{ $permission->foto_bukti }}')"
                                                    title="Lihat Foto">
                                                <i class="bi bi-image"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-3">
                        {{ $permissions->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-clipboard-x display-4 text-muted"></i>
                        <p class="mt-3">Tidak ada pengajuan izin.</p>
                        <a href="{{ route('permission.index') }}" class="btn btn-primary mt-2">
                            <i class="bi bi-arrow-clockwise"></i> Reset Filter
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Mobile Card View -->
<div class="row d-md-none mobile-permissions">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clipboard-check"></i> Daftar Pengajuan Izin
                <span class="badge bg-primary ms-2">{{ $permissions->total() }}</span>
            </div>
            <div class="card-body">
                @if($permissions->count() > 0)
                    @foreach($permissions as $permission)
                    <div class="permission-card">
                        <div class="permission-info">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong>{{ $permission->student->user->name ?? 'N/A' }}</strong>
                                    <div class="text-muted small">
                                        NIS: {{ $permission->student->nis ?? 'N/A' }} | 
                                        Kelas: {{ $permission->student->kelas ?? 'N/A' }}
                                    </div>
                                </div>
                                <div>
                                    @if($permission->status === 'Pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($permission->status === 'Disetujui')
                                        <span class="badge bg-success">Disetujui</span>
                                    @else
                                        <span class="badge bg-danger">Ditolak</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <strong>Tanggal:</strong> {{ $permission->tanggal->format('d/m/Y') }}
                            </div>
                            
                            <div class="mb-2">
                                <strong>Alasan:</strong>
                                <p class="mb-1 text-truncate">{{ Str::limit($permission->alasan, 100) }}</p>
                                <button class="btn btn-sm btn-outline-info" 
                                        onclick="showReason('{{ $permission->alasan }}')">
                                    <i class="bi bi-eye"></i> Lihat Lengkap
                                </button>
                            </div>
                            
                            <div class="permission-actions">
                                @if($permission->status === 'Pending')
                                    <button class="btn btn-success btn-sm" 
                                            onclick="updateStatus({{ $permission->id }}, 'Disetujui')">
                                        <i class="bi bi-check"></i> Setujui
                                    </button>
                                    <button class="btn btn-danger btn-sm" 
                                            onclick="updateStatus({{ $permission->id }}, 'Ditolak')">
                                        <i class="bi bi-x"></i> Tolak
                                    </button>
                                @endif
                                
                                @if($permission->foto_bukti)
                                    <button class="btn btn-outline-primary btn-sm" 
                                            onclick="showPhoto('{{ $permission->foto_bukti }}')">
                                        <i class="bi bi-image"></i> Foto
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
                    <div class="d-flex justify-content-center mt-3">
                        {{ $permissions->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-clipboard-x display-4 text-muted"></i>
                        <p class="mt-3">Tidak ada pengajuan izin.</p>
                        <a href="{{ route('permission.index') }}" class="btn btn-primary mt-2">
                            <i class="bi bi-arrow-clockwise"></i> Reset Filter
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
                <img id="photoImage" src="" alt="Foto Bukti" class="img-fluid rounded">
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
    
    function updateStatus(permissionId, status) {
        const action = status === 'Disetujui' ? 'menyetujui' : 'menolak';
        if (!confirm(`Apakah Anda yakin ingin ${action} izin ini?`)) {
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
    
    // Auto-refresh page every 2 minutes to check for new permissions
    setTimeout(function() {
        location.reload();
    }, 120000);
</script>
@endpush
@endsection                       