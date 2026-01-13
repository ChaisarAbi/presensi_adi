@extends('layouts.app')

@section('title', 'Ajukan Izin / Tidak Hadir')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0"><i class="bi bi-clipboard-plus"></i> Ajukan Izin / Tidak Hadir</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Perhatian:</strong> Upload foto bukti wajib untuk pengajuan izin.
                </div>
                
                <form method="POST" action="{{ route('permission.store') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal Izin</label>
                        <input type="date" class="form-control @error('tanggal') is-invalid @enderror" 
                               id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                        @error('tanggal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="alasan" class="form-label">Alasan dan Keterangan Lengkap</label>
                        <textarea class="form-control @error('alasan') is-invalid @enderror" 
                                  id="alasan" name="alasan" rows="4" 
                                  placeholder="Contoh: Sakit demam tinggi, membawa surat dokter. Atau: Acara keluarga penting, ada undangan pernikahan. Minimal 10 karakter." required>{{ old('alasan') }}</textarea>
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> Jelaskan alasan izin secara lengkap. Minimal 10 karakter.
                        </div>
                        @error('alasan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="foto_bukti" class="form-label">Foto Bukti (Wajib)</label>
                        <input type="file" class="form-control @error('foto_bukti') is-invalid @enderror" 
                               id="foto_bukti" name="foto_bukti" accept="image/*" required>
                        <div class="form-text">
                            <i class="bi bi-camera"></i> Upload foto bukti (surat dokter, undangan, dll). 
                            Maksimal 2MB. Format: JPG, PNG, JPEG.
                        </div>
                        @error('foto_bukti')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Preview Image -->
                    <div class="mb-3" id="image-preview" style="display: none;">
                        <label class="form-label">Preview Foto</label>
                        <div class="border rounded p-2 text-center">
                            <img id="preview" src="#" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-send-check"></i> Ajukan Izin
                        </button>
                        <a href="{{ route('dashboard.siswa') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Pengajuan Izin</h5>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Pengajuan izin akan diverifikasi oleh guru/wali kelas</li>
                    <li>Status pengajuan dapat dilihat di halaman <a href="{{ route('permission.history') }}">Riwayat Izin</a></li>
                    <li>Foto bukti wajib diupload untuk validasi</li>
                    <li>Izin yang disetujui akan tercatat sebagai "Izin" di absensi</li>
                    <li>Izin yang ditolak akan tercatat sebagai "Tidak Hadir"</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Image preview
    document.getElementById('foto_bukti').addEventListener('change', function(e) {
        const preview = document.getElementById('preview');
        const previewContainer = document.getElementById('image-preview');
        
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewContainer.style.display = 'block';
            }
            
            reader.readAsDataURL(this.files[0]);
        } else {
            previewContainer.style.display = 'none';
        }
    });
    
    // Set max date to today
    document.getElementById('tanggal').max = new Date().toISOString().split('T')[0];
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const fileInput = document.getElementById('foto_bukti');
        const file = fileInput.files[0];
        
        if (file) {
            // Check file size (2MB = 2 * 1024 * 1024 bytes)
            if (file.size > 2 * 1024 * 1024) {
                e.preventDefault();
                alert('Ukuran file terlalu besar. Maksimal 2MB.');
                return false;
            }
            
            // Check file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                e.preventDefault();
                alert('Format file tidak didukung. Gunakan JPG, JPEG, atau PNG.');
                return false;
            }
        }
    });
</script>
@endpush
@endsection
