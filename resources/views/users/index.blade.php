@extends('layouts.app')

@section('title', 'Kelola User')

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
                <form method="GET" action="{{ route('users.index') }}">
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

<!-- Users Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-people"></i> Daftar User</h4>
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> Tambah User
                    </a>
                </div>
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
                                            <div class="avatar me-2">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 36px; height: 36px;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
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
                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            
                                            @if($user->id !== Auth::id())
                                            <form method="POST" action="{{ route('users.destroy', $user->id) }}" 
                                                  class="d-inline" onsubmit="return confirmDelete()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" action="{{ route('users.toggleStatus', $user->id) }}" 
                                                  class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-{{ $user->is_active ? 'warning' : 'success' }}">
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
                    
                    <div class="d-flex justify-content-center">
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
</script>
@endpush
@endsection