<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Laporan PDF - Sistem Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .btn-generate {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
        }
        .btn-generate:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        .report-type-btn {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .report-type-btn.active {
            border-color: #667eea;
            background-color: rgba(102, 126, 234, 0.1);
        }
        .report-type-btn:hover {
            border-color: #667eea;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">📊 Generate Laporan PDF</h4>
                        <p class="mb-0 small">Pilih jenis laporan dan parameter, file PDF akan otomatis terdownload</p>
                    </div>
                    
                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form id="reportForm" method="GET" action="">
                            <!-- Report Type Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Jenis Laporan</label>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="report-type-btn text-center" data-type="class">
                                            <i class="bi bi-people-fill fs-1 text-primary"></i>
                                            <h5 class="mt-2">Laporan Kelas</h5>
                                            <p class="text-muted small">Statistik kehadiran per kelas</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="report-type-btn text-center" data-type="student">
                                            <i class="bi bi-person-fill fs-1 text-success"></i>
                                            <h5 class="mt-2">Laporan Siswa</h5>
                                            <p class="text-muted small">Statistik kehadiran per siswa</p>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="report_type" id="reportType" value="class">
                            </div>

                            <!-- Date Range -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="{{ date('Y-m-d', strtotime('-7 days')) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">Tanggal Selesai</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>

                            <!-- Class Selection (for class report) -->
                            <div class="mb-3" id="classSection">
                                <label for="kelas" class="form-label">Kelas</label>
                                <select class="form-select" id="kelas" name="kelas" required>
                                    <option value="">Pilih Kelas</option>
                                    @foreach($kelasList as $kelas)
                                        <option value="{{ $kelas }}">{{ $kelas }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Student Selection (for student report) -->
                            <div class="mb-3 hidden" id="studentSection">
                                <label for="student_id" class="form-label">Siswa</label>
                                <select class="form-select" id="student_id" name="student_id">
                                    <option value="">Pilih Siswa</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}">
                                            {{ $student->nis }} - {{ $student->user->name ?? 'N/A' }} ({{ $student->kelas }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Info Box -->
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Informasi:</strong> File PDF akan otomatis terdownload setelah proses generate selesai. 
                                Proses mungkin memakan waktu beberapa detik untuk data yang besar.
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-generate">
                                    <i class="bi bi-file-earmark-pdf"></i> Generate & Download PDF
                                </button>
                                <a href="{{ route('report.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali ke Menu Laporan
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reportTypeBtns = document.querySelectorAll('.report-type-btn');
            const reportTypeInput = document.getElementById('reportType');
            const classSection = document.getElementById('classSection');
            const studentSection = document.getElementById('studentSection');
            const kelasSelect = document.getElementById('kelas');
            const studentSelect = document.getElementById('student_id');
            const reportForm = document.getElementById('reportForm');

            // Set initial active button
            document.querySelector('.report-type-btn[data-type="class"]').classList.add('active');

            // Report type selection
            reportTypeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const type = this.getAttribute('data-type');
                    
                    // Update active button
                    reportTypeBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update hidden input
                    reportTypeInput.value = type;
                    
                    // Show/hide sections
                    if (type === 'class') {
                        classSection.classList.remove('hidden');
                        studentSection.classList.add('hidden');
                        kelasSelect.required = true;
                        studentSelect.required = false;
                    } else {
                        classSection.classList.add('hidden');
                        studentSection.classList.remove('hidden');
                        kelasSelect.required = false;
                        studentSelect.required = true;
                    }
                });
            });

            // Form submission
            reportForm.addEventListener('submit', function(e) {
                const reportType = reportTypeInput.value;
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                
                if (!startDate || !endDate) {
                    e.preventDefault();
                    alert('Harap isi tanggal mulai dan tanggal selesai');
                    return;
                }
                
                if (reportType === 'class') {
                    const kelas = document.getElementById('kelas').value;
                    if (!kelas) {
                        e.preventDefault();
                        alert('Harap pilih kelas');
                        return;
                    }
                    this.action = "{{ route('report.generate.class.pdf') }}";
                } else {
                    const studentId = document.getElementById('student_id').value;
                    if (!studentId) {
                        e.preventDefault();
                        alert('Harap pilih siswa');
                        return;
                    }
                    this.action = "{{ route('report.generate.student.pdf') }}";
                }
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating PDF...';
                submitBtn.disabled = true;
            });

            // Set max date for end_date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('end_date').max = today;
            document.getElementById('start_date').max = today;
        });
    </script>
</body>
</html>