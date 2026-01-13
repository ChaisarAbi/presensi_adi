@extends('layouts.app')

@section('title', 'Tambah User Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-person-plus"></i> Tambah User Baru</h4>
            </div>
            <div class="card-body">
                <!-- Tabs untuk pilih jenis user -->
                <ul class="nav nav-tabs mb-4" id="userTypeTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="admin-guru-tab" data-bs-toggle="tab" 
                                data-bs-target="#admin-guru" type="button" role="tab">
                            <i class="bi bi-person-badge"></i> Admin/Guru
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="siswa-tab" data-bs-toggle="tab" 
                                data-bs-target="#siswa" type="button" role="tab">
                            <i class="bi bi-person-video"></i> Siswa
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="userTypeTabContent">
                    <!-- Tab Admin/Guru -->
                    <div class="tab-pane fade show active" id="admin-guru" role="tabpanel">
                        <form method="POST" action="{{ route('users.store') }}" id="adminGuruForm">
                            @csrf
                            <input type="hidden" name="user_type" value="admin_guru">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name_admin" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name_admin" name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email_admin" class="form-label">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email_admin" name="email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password_admin" class="form-label">Password</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                               id="password_admin" name="password" required>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password_confirmation_admin" class="form-label">Konfirmasi Password</label>
                                        <input type="password" class="form-control" 
                                               id="password_confirmation_admin" name="password_confirmation" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="role_admin" class="form-label">Role</label>
                                <select class="form-select @error('role') is-invalid @enderror" 
                                        id="role_admin" name="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="guru" {{ old('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-person-plus"></i> Simpan Admin/Guru
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Tab Siswa -->
                    <div class="tab-pane fade" id="siswa" role="tabpanel">
                        <form method="POST" action="{{ route('users.store') }}" id="siswaForm">
                            @csrf
                            <input type="hidden" name="user_type" value="siswa">
                            <input type="hidden" name="role" value="siswa">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name_siswa" class="form-label">Nama Lengkap Siswa</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name_siswa" name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email_siswa" class="form-label">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email_siswa" name="email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password_siswa" class="form-label">Password</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                               id="password_siswa" name="password" required>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password_confirmation_siswa" class="form-label">Konfirmasi Password</label>
                                        <input type="password" class="form-control" 
                                               id="password_confirmation_siswa" name="password_confirmation" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Data khusus untuk siswa
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nis" class="form-label">NIS (Nomor Induk Siswa)</label>
                                        <input type="text" class="form-control @error('nis') is-invalid @enderror" 
                                               id="nis" name="nis" value="{{ old('nis') }}" required>
                                        @error('nis')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="kelas" class="form-label">Kelas</label>
                                        <input type="text" class="form-control @error('kelas') is-invalid @enderror" 
                                               id="kelas" name="kelas" value="{{ old('kelas') }}" required>
                                        @error('kelas')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama_ortu" class="form-label">Nama Orang Tua</label>
                                        <input type="text" class="form-control @error('nama_ortu') is-invalid @enderror" 
                                               id="nama_ortu" name="nama_ortu" value="{{ old('nama_ortu') }}" required>
                                        @error('nama_ortu')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="kontak_ortu" class="form-label">Kontak Orang Tua</label>
                                        <input type="text" class="form-control @error('kontak_ortu') is-invalid @enderror" 
                                               id="kontak_ortu" name="kontak_ortu" value="{{ old('kontak_ortu') }}" required>
                                        @error('kontak_ortu')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-person-plus"></i> Simpan Siswa
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="mt-3">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar User
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Show validation errors in popup if any
    @if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            showErrorPopup();
        }, 500);
    });
    @endif
    
    function showErrorPopup() {
        const errorMessages = [];
        @foreach($errors->all() as $error)
            errorMessages.push('{{ $error }}');
        @endforeach
        
        if (errorMessages.length > 0) {
            const errorHtml = errorMessages.map(msg => `<li>${msg}</li>`).join('');
            const modalHtml = `
                <div class="modal fade" id="errorModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill"></i> Tambah User Gagal</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Terjadi kesalahan saat menambah user:</p>
                                <ul class="mb-0">
                                    ${errorHtml}
                                </ul>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('errorModal'));
            modal.show();
            
            // Remove modal after hidden
            document.getElementById('errorModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }
    }
    
    // Auto-switch tab based on validation errors
    document.addEventListener('DOMContentLoaded', function() {
        @if(old('user_type') == 'siswa' || $errors->has('nis') || $errors->has('kelas') || $errors->has('nama_ortu') || $errors->has('kontak_ortu'))
        const siswaTab = new bootstrap.Tab(document.getElementById('siswa-tab'));
        siswaTab.show();
        @endif
    });
</script>
@endpush
@endsection