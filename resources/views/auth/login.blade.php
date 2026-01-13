@extends('layouts.app')

@section('title', 'Login - Sistem Absensi QR Code')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-box-arrow-in-right"></i> Login</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="login" class="form-label">NIS atau Email</label>
                        <input type="text" class="form-control @error('login') is-invalid @enderror" 
                               id="login" name="login" value="{{ old('login') }}" required autofocus
                               placeholder="Masukkan NIS (siswa) atau Email (admin/guru)">
                        @error('login')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p class="text-muted mb-0">
                        <small>
                            <i class="bi bi-info-circle"></i> 
                            Hubungi admin untuk membuat akun baru
                        </small>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <p class="text-muted">
                <small>
                    <i class="bi bi-info-circle"></i> 
                    Sistem Absensi QR Code - Mobile First Design
                </small>
            </p>
        </div>
    </div>
</div>
@endsection
