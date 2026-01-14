@extends('layouts.app')

@section('title', 'Kelola User')

@push('styles')
<style>
    /* Mobile optimization for user table */
    .user-card {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        margin-bottom: 1rem;
        padding: 1rem;
        transition: all 0.2s;
    }
    
    .user-card:hover {
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #0d6efd;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
    }
    
    .user-actions {
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
        
        .mobile-users {
            display: block;
        }
        
        .user-card {
            padding: 0.75rem;
            margin-left: -0.5rem;
            margin-right: -0.5rem;
            border-radius: 0;
            border-left: none;
            border-right: none;
        }
        
        .user-info {
            font-size: 0.9rem;
        }
        
        /* Fix card body padding on mobile */
        .card-body {
            padding: 0.75rem;
        }
        
        /* Fix filter form on mobile */
        .filter-form .row {
            margin-left: -0.25rem;
            margin-right: -0.25rem;
        }
        
        .filter-form .col-md-4,
        .filter-form .col-md-6,
        .filter-form .col-md-2 {
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }
        
        /* Fix stats cards on mobile */
        .stat-card {
            padding: 0.75rem;
        }
        
        .stat-number {
            font-size: 1.5rem;
        }
    }
    
    /* Desktop view */
    @media (min-width: 769px) {
        .mobile-users {
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
    
    /* Fix pagination overflow */
    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .page-item {
        margin-bottom: 0.25rem;
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="h4">Kelola User</h2>
        <p class="text-muted">Kelola semua user sistem (Admin, Guru, Siswa)</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-primary">{{ $totalUsers }}</div>
            <div class="stat-label">Total User</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-success">{{ $adminCount }}</div>
            <div class="stat-label">Admin</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-warning">{{ $guruCount }}</div>
            <div class="stat-label">Guru</div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="stat-number text-danger">{{ $siswaCount }}</div>
            <div class="stat-label">Siswa</div>
        </div>
    </div>
</div>

<!-- Filter & Search -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-funnel"></i> Filter & Pencarian
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('users.index') }}" class="filter-form">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role">
                                <option value="">Semua Role</option>
                                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                                <option value="siswa" {{ request('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="search" class="form-label">Pencarian</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Cari nama, email, NIS, atau kelas...">
                        </div>
                        
                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <div class="d-grid w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Desktop Table View -->
<div class="row d-none d-md-block">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-people"></i> Daftar User
                    <span class="badge bg-primary ms-2">{{ $users->total() }}</span>
                </div>
                <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Tambah User
                </a>
            </div>
            <div class="card-body">
                @if($users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>NIS/Kelas</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>{{ $loop->iteration + (($users->currentPage() - 1) * $users->perPage()) }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <strong>{{ $user->name }}</strong>
                                                @if(!$user->is_active)
                                                    <span class="badge bg-danger ms-1">Nonaktif</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->role === 'admin')
                                            <span class="badge bg-success">Admin</span>
                                        @elseif($user->role === 'guru')
                                            <span class="badge bg-warning">Guru</span>
                                        @else
                                            <span class="badge bg-info">Siswa</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->student)
                                            <div>
                                                <small class="text-muted">NIS:</small> {{ $user->student->nis }}
                                            </div>
                                            <div>
                                                <small class="text-muted">Kelas:</small> {{ $user->student->kelas }}
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            
                                            @if($user->id !== Auth::id())
                                            <form method="POST" action="{{ route('users.destroy', $user->id) }}" 
                                                  class="d-inline" onsubmit="return confirmDelete()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" action="{{ route('users.toggleStatus', $user->id) }}" 
                                                  class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-{{ $user->is_active ? 'warning' : 'success' }}" 
                                                        title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                    <i class="bi bi-power"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-3">
                        {{ $users->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-people display-4 text-muted"></i>
                        <p class="mt-3">Tidak ada user ditemukan.</p>
                        <a href="{{ route('users.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Tambah User Pertama
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Mobile Card View -->
<div class="row d-md-none mobile-users">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-people"></i> Daftar User
                    <span class="badge bg-primary ms-2">{{ $users->total() }}</span>
                </div>
                <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Tambah
                </a>
            </div>
            <div class="card-body">
                @if($users->count() > 0)
                    @foreach($users as $user)
                    <div class="user-card">
                        <div class="user-info">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        <div class="text-muted small">{{ $user->email }}</div>
                                    </div>
                                </div>
                                <div>
                                    @if($user->role === 'admin')
                                        <span class="badge bg-success">Admin</span>
                                    @elseif($user->role === 'guru')
                                        <span class="badge bg-warning">Guru</span>
                                    @else
                                        <span class="badge bg-info">Siswa</span>
                                    @endif
                                    
                                    @if(!$user->is_active)
                                        <span class="badge bg-danger ms-1">Nonaktif</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                @if($user->student)
                                    <div>
                                        <strong>NIS:</strong> {{ $user->student->nis }}
                                    </div>
                                    <div>
                                        <strong>Kelas:</strong> {{ $user->student->kelas }}
                                    </div>
                                @else
                                    <div class="text-muted">-</div>
                                @endif
                            </div>
                            
                            <div class="mb-2">
                                <strong>Daftar:</strong> {{ $user->created_at->format('d/m/Y') }}
                            </div>
                            
                            <div class="user-actions">
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                
                                @if($user->id !== Auth::id())
                                <form method="POST" action="{{ route('users.destroy', $user->id) }}" 
                                      class="d-inline" onsubmit="return confirmDelete()">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                                
                                <form method="POST" action="{{ route('users.toggleStatus', $user->id) }}" 
                                      class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-{{ $user->is_active ? 'warning' : 'success' }} btn-sm">
                                        <i class="bi bi-power"></i> {{ $user->is_active ? 'Nonaktif' : 'Aktif' }}
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
                    <div class="d-flex justify-content-center mt-3">
                        {{ $users->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-people display-4 text-muted"></i>
                        <p class="mt-3">Tidak ada user ditemukan.</p>
                        <a href="{{ route('users.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Tambah User Pertama
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmDelete() {
        return confirm('Apakah Anda yakin ingin menghapus user ini?');
    }
    
    // Auto-submit filter on role change
    document.getElementById('role').addEventListener('change', function() {
        this.form.submit();
    });
    
    // Auto-refresh page every 2 minutes
    setTimeout(function() {
        location.reload();
    }, 120000);
</script>
@endpush
@endsection