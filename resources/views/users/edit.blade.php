@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-person-gear"></i> Edit User: {{ $user->name }}</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('users.update', $user->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password (Kosongkan jika tidak diubah)</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select @error('role') is-invalid @enderror" 
                                id="role" name="role" required onchange="toggleStudentFields()">
                            <option value="">Pilih Role</option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="guru" {{ old('role', $user->role) == 'guru' ? 'selected' : '' }}>Guru</option>
                            <option value="siswa" {{ old('role', $user->role) == 'siswa' ? 'selected' : '' }}>Siswa</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Student Fields -->
                    <div id="student-fields" style="display: {{ $user->role === 'siswa' ? 'block' : 'none' }};">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Data khusus untuk siswa
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nis" class="form-label">NIS</label>
                                    <input type="text" class="form-control @error('nis') is-invalid @enderror" 
                                           id="nis" name="nis" value="{{ old('nis', $user->student->nis ?? '') }}">
                                    @error('nis')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kelas" class="form-label">Kelas</label>
                                    <input type="text" class="form-control @error('kelas') is-invalid @enderror" 
                                           id="kelas" name="kelas" value="{{ old('kelas', $user->student->kelas ?? '') }}">
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
                                           id="nama_ortu" name="nama_ortu" value="{{ old('nama_ortu', $user->student->nama_ortu ?? '') }}">
                                    @error('nama_ortu')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kontak_ortu" class="form-label">Kontak Orang Tua</label>
                                    <input type="text" class="form-control @error('kontak_ortu') is-invalid @enderror" 
                                           id="kontak_ortu" name="kontak_ortu" value="{{ old('kontak_ortu', $user->student->kontak_ortu ?? '') }}">
                                    @error('kontak_ortu')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update User
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleStudentFields() {
        const role = document.getElementById('role').value;
        const studentFields = document.getElementById('student-fields');
        
        if (role === 'siswa') {
            studentFields.style.display = 'block';
            
            // Make student fields required
            document.getElementById('nis').required = true;
            document.getElementById('kelas').required = true;
            document.getElementById('nama_ortu').required = true;
            document.getElementById('kontak_ortu').required = true;
        } else {
            studentFields.style.display = 'none';
            
            // Remove required attribute
            document.getElementById('nis').required = false;
            document.getElementById('kelas').required = false;
            document.getElementById('nama_ortu').required = false;
            document.getElementById('kontak_ortu').required = false;
        }
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleStudentFields();
        
        // Show validation errors in popup if any
        @if($errors->any())
        setTimeout(() => {
            showErrorPopup();
        }, 500);
        @endif
    });
    
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
                                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill"></i> Update User Gagal</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Terjadi kesalahan saat mengupdate user:</p>
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
</script>
@endpush
@endsection