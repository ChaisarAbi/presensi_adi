<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Siswa {{ $student->user->name ?? 'N/A' }}</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4a6fa5;
        }
        
        .header h1 {
            font-size: 22px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        
        .header .info {
            font-size: 11px;
            color: #95a5a6;
        }
        
        /* Student info */
        .student-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .student-info h2 {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-item .label {
            font-size: 10px;
            color: #6c757d;
            margin-bottom: 3px;
        }
        
        .info-item .value {
            font-size: 13px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        /* Statistics */
        .statistics {
            margin-bottom: 25px;
        }
        
        .statistics h2 {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        
        .stat-item {
            text-align: center;
            padding: 12px;
            background-color: white;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .stat-item .label {
            font-size: 10px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .stat-item .value {
            font-size: 18px;
            font-weight: bold;
        }
        
        /* Status colors */
        .status-hadir { color: #28a745; }
        .status-terlambat { color: #ffc107; }
        .status-izin { color: #fd7e14; }
        .status-tidak-hadir { color: #dc3545; }
        .status-percentage { color: #4a6fa5; }
        
        /* Attendance table */
        .attendance-table {
            margin-bottom: 25px;
        }
        
        .attendance-table h2 {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        thead {
            background-color: #4a6fa5;
            color: white;
        }
        
        th {
            padding: 8px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            border: 1px solid #3a5a8a;
        }
        
        td {
            padding: 6px 4px;
            text-align: center;
            border: 1px solid #dee2e6;
            font-size: 10px;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        /* Status badges */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-hadir-masuk { background-color: #d4edda; color: #155724; }
        .badge-terlambat { background-color: #fff3cd; color: #856404; }
        .badge-hadir-pulang { background-color: #d1ecf1; color: #0c5460; }
        .badge-izin { background-color: #ffe5d0; color: #fd7e14; }
        .badge-tidak-hadir { background-color: #f8d7da; color: #721c24; }
        
        /* Progress bar */
        .progress-container {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        
        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 11px;
        }
        
        .progress-bar {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4a6fa5, #667eea);
            border-radius: 10px;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 10px;
            font-weight: bold;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #95a5a6;
        }
        
        .footer .generated {
            margin-bottom: 5px;
        }
        
        /* Compact styles */
        .compact th, .compact td {
            padding: 4px 3px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN KEHADIRAN SISWA</h1>
        <div class="subtitle">Periode: {{ $start_date }} s/d {{ $end_date }}</div>
        <div class="info">Sistem Absensi QR Code - Generated on: {{ $generated_at }}</div>
    </div>
    
    <!-- Student Information -->
    <div class="student-info">
        <h2>👤 Informasi Siswa</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">NIS</span>
                <span class="value">{{ $student->nis ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Nama Lengkap</span>
                <span class="value">{{ $student->user->name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Kelas</span>
                <span class="value">{{ $student->kelas ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Orang Tua</span>
                <span class="value">{{ $student->nama_ortu ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Kontak Orang Tua</span>
                <span class="value">{{ $student->kontak_ortu ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Total Hari Aktif</span>
                <span class="value">{{ $statistics['total_days'] }} hari</span>
            </div>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="statistics">
        <h2>📊 Statistik Kehadiran</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="label">Hadir Masuk</div>
                <div class="value status-hadir">{{ $statistics['hadir_masuk'] }}</div>
            </div>
            <div class="stat-item">
                <div class="label">Terlambat</div>
                <div class="value status-terlambat">{{ $statistics['terlambat'] }}</div>
            </div>
            <div class="stat-item">
                <div class="label">Hadir Pulang</div>
                <div class="value status-hadir">{{ $statistics['hadir_pulang'] }}</div>
            </div>
            <div class="stat-item">
                <div class="label">Izin</div>
                <div class="value status-izin">{{ $statistics['izin'] }}</div>
            </div>
            <div class="stat-item">
                <div class="label">Tidak Hadir</div>
                <div class="value status-tidak-hadir">{{ $statistics['tidak_hadir'] }}</div>
            </div>
            <div class="stat-item">
                <div class="label">Total Kehadiran</div>
                <div class="value">{{ $statistics['total_attendance'] }}</div>
            </div>
        </div>
    </div>
    
    <!-- Progress Bar -->
    <div class="progress-container">
        <div class="progress-label">
            <span>Persentase Kehadiran</span>
            <span>{{ $statistics['attendance_percentage'] }}%</span>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" style="width: {{ $statistics['attendance_percentage'] }}%;">
                {{ $statistics['attendance_percentage'] }}%
            </div>
        </div>
    </div>
    
    <!-- Recent Attendances -->
    <div class="attendance-table">
        <h2>📅 Riwayat Kehadiran Terbaru (20 terakhir)</h2>
        <table class="compact">
            <thead>
                <tr>
                    <th style="width: 25%;">Tanggal</th>
                    <th style="width: 20%;">Waktu</th>
                    <th style="width: 25%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recent_attendances as $attendance)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($attendance->tanggal)->format('d/m/Y') }}</td>
                    <td>{{ $attendance->waktu }}</td>
                    <td>
                        @php
                            $badgeClass = '';
                            switch($attendance->status) {
                                case 'Hadir Masuk': $badgeClass = 'badge-hadir-masuk'; break;
                                case 'Terlambat': $badgeClass = 'badge-terlambat'; break;
                                case 'Hadir Pulang': $badgeClass = 'badge-hadir-pulang'; break;
                                case 'Izin': $badgeClass = 'badge-izin'; break;
                                case 'Tidak Hadir': $badgeClass = 'badge-tidak-hadir'; break;
                            }
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $attendance->status }}</span>
                    </td>
                </tr>
                @endforeach
                @if($recent_attendances->isEmpty())
                <tr>
                    <td colspan="3" style="text-align: center; padding: 15px; color: #6c757d;">
                        Tidak ada data kehadiran untuk periode ini
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <!-- Summary -->
    <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px; font-size: 11px;">
        <h3 style="color: #2c3e50; margin-bottom: 10px;">📝 Ringkasan</h3>
        <p>Siswa <strong>{{ $student->user->name ?? 'N/A' }}</strong> memiliki tingkat kehadiran sebesar 
           <strong>{{ $statistics['attendance_percentage'] }}%</strong> selama periode 
           {{ $start_date }} sampai {{ $end_date }}.</p>
        <p>Dari total {{ $statistics['total_days'] }} hari aktif, siswa hadir sebanyak 
           {{ $statistics['total_attendance'] }} kali dengan rincian:</p>
        <ul style="margin-left: 20px;">
            <li>Hadir Masuk: {{ $statistics['hadir_masuk'] }} kali</li>
            <li>Terlambat: {{ $statistics['terlambat'] }} kali</li>
            <li>Hadir Pulang: {{ $statistics['hadir_pulang'] }} kali</li>
            <li>Izin: {{ $statistics['izin'] }} kali</li>
            <li>Tidak Hadir: {{ $statistics['tidak_hadir'] }} kali</li>
        </ul>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <div class="generated">Dokumen ini digenerate secara otomatis oleh Sistem Absensi QR Code</div>
        <div>Halaman 1/1</div>
    </div>
</body>
</html>