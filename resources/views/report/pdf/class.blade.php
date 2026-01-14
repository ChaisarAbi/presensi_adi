<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kelas {{ $kelas }}</title>
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
        
        /* Summary section */
        .summary {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .summary h2 {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        
        .summary-item {
            text-align: center;
            padding: 8px;
            background-color: white;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }
        
        .summary-item .label {
            font-size: 10px;
            color: #6c757d;
            margin-bottom: 3px;
        }
        
        .summary-item .value {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        /* Table styles */
        .table-container {
            margin-bottom: 25px;
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
        
        tbody tr:hover {
            background-color: #e9ecef;
        }
        
        /* Status colors */
        .status-hadir { color: #28a745; }
        .status-terlambat { color: #ffc107; }
        .status-izin { color: #fd7e14; }
        .status-tidak-hadir { color: #dc3545; }
        
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
        
        /* Page break */
        .page-break {
            page-break-before: always;
        }
        
        /* Compact styles for better PDF rendering */
        .compact th, .compact td {
            padding: 4px 3px;
            font-size: 9px;
        }
        
        /* Column widths */
        .col-nis { width: 15%; }
        .col-name { width: 25%; }
        .col-hadir { width: 12%; }
        .col-terlambat { width: 12%; }
        .col-pulang { width: 12%; }
        .col-izin { width: 12%; }
        .col-tidak-hadir { width: 12%; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN KEHADIRAN KELAS</h1>
        <div class="subtitle">Kelas: {{ $kelas }}</div>
        <div class="subtitle">Periode: {{ $start_date }} s/d {{ $end_date }}</div>
        <div class="info">Sistem Absensi QR Code - Generated on: {{ $generated_at }}</div>
    </div>
    
    <!-- Summary -->
    <div class="summary">
        <h2>📊 Ringkasan Statistik</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Siswa</div>
                <div class="value">{{ $summary['total_students'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Hari Aktif</div>
                <div class="value">{{ $summary['total_days'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Hadir Masuk</div>
                <div class="value status-hadir">{{ $summary['total_hadir_masuk'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Terlambat</div>
                <div class="value status-terlambat">{{ $summary['total_terlambat'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Hadir Pulang</div>
                <div class="value status-hadir">{{ $summary['total_hadir_pulang'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Izin</div>
                <div class="value status-izin">{{ $summary['total_izin'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Tidak Hadir</div>
                <div class="value status-tidak-hadir">{{ $summary['total_tidak_hadir'] }}</div>
            </div>
        </div>
    </div>
    
    <!-- Student Data Table -->
    <div class="table-container">
        <h2>📋 Data Kehadiran per Siswa</h2>
        <table class="compact">
            <thead>
                <tr>
                    <th class="col-nis">NIS</th>
                    <th class="col-name">Nama Siswa</th>
                    <th class="col-hadir">Hadir Masuk</th>
                    <th class="col-terlambat">Terlambat</th>
                    <th class="col-pulang">Hadir Pulang</th>
                    <th class="col-izin">Izin</th>
                    <th class="col-tidak-hadir">Tidak Hadir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats as $row)
                <tr>
                    <td>{{ $row->nis }}</td>
                    <td style="text-align: left; padding-left: 8px;">{{ $row->name }}</td>
                    <td class="status-hadir">{{ $row->hadir_masuk }}</td>
                    <td class="status-terlambat">{{ $row->terlambat }}</td>
                    <td class="status-hadir">{{ $row->hadir_pulang }}</td>
                    <td class="status-izin">{{ $row->izin }}</td>
                    <td class="status-tidak-hadir">{{ $row->tidak_hadir }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Legend -->
    <div style="margin-top: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 4px; font-size: 10px;">
        <strong>Keterangan:</strong>
        <span style="color: #28a745;">● Hadir</span> |
        <span style="color: #ffc107;">● Terlambat</span> |
        <span style="color: #fd7e14;">● Izin</span> |
        <span style="color: #dc3545;">● Tidak Hadir</span>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <div class="generated">Dokumen ini digenerate secara otomatis oleh Sistem Absensi QR Code</div>
        <div>Halaman 1/1</div>
    </div>
</body>
</html>