<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hasil Ujian - {{ $ujian->nama }}</title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12px; margin: 20px; }
        h2 { text-align: center; margin-bottom: 5px; }
        .subtitle { text-align: center; margin-bottom: 20px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px 8px; }
        th { background: #1e3a5f; color: white; text-align: center; }
        td { text-align: center; }
        .info-table td { border: none; text-align: left; padding: 2px 5px; }
        .footer { margin-top: 30px; text-align: right; }
    </style>
</head>
<body>
    <h2>DAFTAR NILAI UJIAN</h2>
    <p class="subtitle">{{ config('app.name') }}</p>

    <table class="info-table" style="margin-bottom: 15px;">
        <tr><td width="120"><strong>Nama Ujian</strong></td><td>: {{ $ujian->nama }}</td></tr>
        <tr><td><strong>Mata Pelajaran</strong></td><td>: {{ $ujian->mapel->nama ?? '-' }}</td></tr>
        <tr><td><strong>Guru</strong></td><td>: {{ $ujian->guru->user->name ?? '-' }}</td></tr>
        <tr><td><strong>Tanggal</strong></td><td>: {{ $ujian->tanggal_mulai ? \Carbon\Carbon::parse($ujian->tanggal_mulai)->format('d F Y') : '-' }}</td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Benar</th>
                <th>Salah</th>
                <th>Nilai</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hasil->sortByDesc('nilai_akhir')->values() as $i => $h)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $h->pesertaUjian->siswa->nis ?? '-' }}</td>
                <td style="text-align:left;">{{ $h->pesertaUjian->siswa->user->name ?? '-' }}</td>
                <td>{{ $h->pesertaUjian->siswa->kelas->nama ?? '-' }}</td>
                <td>{{ $h->jumlah_benar }}</td>
                <td>{{ $h->jumlah_salah }}</td>
                <td><strong>{{ number_format($h->nilai_akhir, 1) }}</strong></td>
                <td>{{ ucfirst($h->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d F Y H:i') }}</p>
        <br><br>
        <p>Guru Mata Pelajaran</p>
        <br><br><br>
        <p><u>{{ $ujian->guru->user->name ?? '________________' }}</u></p>
        <p>NIP. {{ $ujian->guru->nip ?? '-' }}</p>
    </div>
</body>
</html>
