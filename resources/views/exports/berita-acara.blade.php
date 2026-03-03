<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Berita Acara Ujian</title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12px; margin: 30px; }
        h2 { text-align: center; margin-bottom: 5px; text-decoration: underline; }
        .subtitle { text-align: center; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { border: 1px solid #333; padding: 6px 8px; }
        .data-table th { background: #f0f0f0; }
        .info td { padding: 3px 5px; border: none; }
        .ttd { margin-top: 40px; }
        .ttd table td { border: none; text-align: center; padding: 5px 20px; }
    </style>
</head>
<body>
    <h2>BERITA ACARA PELAKSANAAN UJIAN</h2>
    <p class="subtitle">{{ config('app.name') }}</p>

    <p>Pada hari ini, {{ now()->translatedFormat('l') }}, tanggal {{ now()->format('d') }}, bulan {{ now()->translatedFormat('F') }},
       tahun {{ now()->format('Y') }}, telah dilaksanakan ujian dengan rincian sebagai berikut:</p>

    <table class="info" style="margin: 15px 0;">
        <tr><td width="180">1. Nama Ujian</td><td>: {{ $ujian->nama }}</td></tr>
        <tr><td>2. Mata Pelajaran</td><td>: {{ $ujian->mapel->nama ?? '-' }}</td></tr>
        <tr><td>3. Guru Pengampu</td><td>: {{ $ujian->guru->user->name ?? '-' }}</td></tr>
        <tr><td>4. Tanggal Pelaksanaan</td><td>: {{ $ujian->tanggal_mulai ? \Carbon\Carbon::parse($ujian->tanggal_mulai)->format('d F Y') : '-' }}</td></tr>
        <tr><td>5. Durasi</td><td>: {{ $ujian->durasi }} menit</td></tr>
        <tr><td>6. Jumlah Soal</td><td>: {{ $ujian->soal->count() }} butir</td></tr>
    </table>

    <p><strong>Rekapitulasi Peserta:</strong></p>
    <table class="data-table" style="margin-bottom: 15px;">
        <tr><th>Keterangan</th><th>Jumlah</th></tr>
        <tr><td>Total Peserta Terdaftar</td><td style="text-align:center">{{ $stats['total'] ?? 0 }}</td></tr>
        <tr><td>Peserta Hadir (Mengerjakan)</td><td style="text-align:center">{{ ($stats['mengerjakan'] ?? 0) + ($stats['selesai'] ?? 0) }}</td></tr>
        <tr><td>Peserta Selesai</td><td style="text-align:center">{{ $stats['selesai'] ?? 0 }}</td></tr>
        <tr><td>Peserta Tidak Hadir</td><td style="text-align:center">{{ $stats['belum'] ?? 0 }}</td></tr>
    </table>

    @if(isset($stats['rata_rata']))
    <p><strong>Statistik Nilai:</strong></p>
    <table class="data-table" style="margin-bottom: 15px;">
        <tr><th>Keterangan</th><th>Nilai</th></tr>
        <tr><td>Nilai Rata-rata</td><td style="text-align:center">{{ number_format($stats['rata_rata'] ?? 0, 1) }}</td></tr>
        <tr><td>Nilai Tertinggi</td><td style="text-align:center">{{ number_format($stats['tertinggi'] ?? 0, 1) }}</td></tr>
        <tr><td>Nilai Terendah</td><td style="text-align:center">{{ number_format($stats['terendah'] ?? 0, 1) }}</td></tr>
    </table>
    @endif

    <p>Demikian berita acara ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya.</p>

    <div class="ttd">
        <table width="100%">
            <tr>
                <td>Proktor/Pengawas</td>
                <td>Guru Mata Pelajaran</td>
            </tr>
            <tr>
                <td><br><br><br><br>( ________________ )</td>
                <td><br><br><br><br>( {{ $ujian->guru->user->name ?? '________________' }} )</td>
            </tr>
        </table>
    </div>
</body>
</html>
