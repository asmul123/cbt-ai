<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Berita Acara - {{ $ujian->nama }}</title>
    <style>
        @page { margin: 20mm 15mm 20mm 15mm; }
        body { font-family: 'Times New Roman', serif; font-size: 12pt; margin: 0; padding: 0; line-height: 1.5; }

        /* KOP Surat */
        .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 20px; }
        .kop .instansi { font-size: 11pt; font-weight: bold; margin: 0; }
        .kop .sekolah { font-size: 16pt; font-weight: bold; margin: 2px 0; }
        .kop .alamat { font-size: 9pt; margin: 0; }
        .kop .kontak { font-size: 9pt; margin: 0; }

        /* Title */
        .title { text-align: center; margin: 15px 0 5px; font-size: 13pt; font-weight: bold; text-decoration: underline; }
        .subtitle { text-align: center; font-size: 12pt; font-weight: bold; margin-bottom: 20px; }

        /* Content */
        .content { text-align: justify; }
        .info td { padding: 2px 5px; vertical-align: top; border: none; }
        .info td:first-child { width: 280px; }

        /* TTD */
        .ttd { margin-top: 30px; }
        .ttd table { width: 100%; }
        .ttd td { border: none; text-align: center; vertical-align: top; padding: 5px 10px; }
        .ttd-img { max-height: 80px; max-width: 200px; }
    </style>
</head>
<body>
    @php
        $hasBA = isset($beritaAcara);
        $tgl = $ujian->tanggal_mulai ? \Carbon\Carbon::parse($ujian->tanggal_mulai) : now();
        $hari = $tgl->translatedFormat('l');
        $tglTerbilang = $tgl->translatedFormat('j');
        $bulan = $tgl->translatedFormat('F');
        $tahun = $tgl->format('Y');

        if ($hasBA) {
            $waktuMulai = $beritaAcara->waktu_mulai ?? $tgl->format('H:i');
            $waktuSelesai = $beritaAcara->waktu_selesai ?? ($ujian->tanggal_selesai ? \Carbon\Carbon::parse($ujian->tanggal_selesai)->format('H:i') : $tgl->copy()->addMinutes($ujian->durasi)->format('H:i'));
            $catatan = $beritaAcara->catatan ?? 'Aman';
            $pengawas = $beritaAcara->nama_pengawas ?: ($beritaAcara->proktor->name ?? '');
            $ruangName = $beritaAcara->ruangUjian->nama ?? '-';
            $totalPeserta = $stats['total'] ?? 0;
            $hadirPeserta = $stats['hadir'] ?? 0;
            $tidakHadirCount = $stats['tidak_hadir'] ?? 0;
        } else {
            $waktuMulai = $tgl->format('H:i');
            $waktuSelesai = $ujian->tanggal_selesai ? \Carbon\Carbon::parse($ujian->tanggal_selesai)->format('H:i') : $tgl->copy()->addMinutes($ujian->durasi)->format('H:i');
            $catatan = $catatan ?? 'Aman';
            $pengawas = $pengawas ?? '';
            $ruangName = $ujian->ruang->pluck('nama')->implode(', ') ?? '-';
            $totalPeserta = $stats['total'] ?? 0;
            $hadirPeserta = ($stats['mengerjakan'] ?? 0) + ($stats['selesai'] ?? 0);
            $tidakHadirCount = $stats['belum'] ?? 0;
        }
        $kelasNames = $ujian->kelas->pluck('nama')->implode(', ') ?: '-';
    @endphp

    {{-- KOP SURAT --}}
    <div class="kop">
        <p class="instansi">{{ config('app.sekolah.instansi') }}<br>{{ config('app.sekolah.dinas') }}</p>
        <p class="sekolah">{{ config('app.sekolah.nama') }}</p>
        <p class="alamat">{{ config('app.sekolah.alamat') }} Telp {{ config('app.sekolah.telp') }} Fax: {{ config('app.sekolah.fax') }}</p>
        <p class="kontak">Website : {{ config('app.sekolah.website') }} Email : {{ config('app.sekolah.email') }}</p>
        <p class="alamat">{{ config('app.sekolah.kecamatan') }} - {{ config('app.sekolah.kota') }} {{ config('app.sekolah.kodepos') }}</p>
    </div>

    {{-- JUDUL --}}
    <div class="title">BERITA ACARA</div>
    <div class="subtitle">PENILAIAN SUMATIF AKHIR JENJANG<br>TAHUN PELAJARAN {{ $tahunAjaran ?? date('Y') . '-' . (date('Y')+1) }}</div>

    {{-- ISI --}}
    <div class="content">
        <p>Pada hari ini <strong>{{ $hari }}</strong> tanggal <strong>{{ $tglTerbilang }}</strong> bulan <strong>{{ $bulan }}</strong> tahun <strong>{{ $tahun }}</strong>.
        Telah dilaksanakan Penilaian Sumatif Akhir Jenjang, Tahun Pelajaran {{ $tahunAjaran ?? date('Y') . '-' . (date('Y')+1) }}
        mulai dari pukul <strong>{{ $waktuMulai }}</strong> sampai dengan pukul <strong>{{ $waktuSelesai }}</strong></p>

        <p>Pada :</p>
        <table class="info">
            <tr><td>Kelas</td><td>: {{ $kelasNames }}</td></tr>
            <tr><td>Ruang</td><td>: {{ $ruangName }}</td></tr>
            <tr><td>Mata Pelajaran</td><td>: {{ $ujian->mapel->nama ?? '-' }}</td></tr>
            <tr><td>Jumlah Peserta didik yang seharusnya</td><td>: {{ $totalPeserta }} Peserta didik</td></tr>
            <tr><td>Jumlah Peserta didik yang hadir</td><td>: {{ $hadirPeserta }} Peserta didik</td></tr>
            <tr><td>Jumlah Peserta didik yang tidak hadir</td><td>: {{ $tidakHadirCount }} Peserta didik</td></tr>
        </table>

        <table class="info">
            <tr>
                <td>Nomor Peserta didik yang tidak hadir</td>
                <td>: @if($hasBA && isset($pesertaTidakHadir) && $pesertaTidakHadir->count())
                        {{ $pesertaTidakHadir->map(fn($p) => $p->siswa->nis ?? '-')->implode(', ') }}
                    @elseif(!$hasBA && isset($ujian->peserta))
                        @php $absen = $ujian->peserta->where('status', 'belum_mulai'); @endphp
                        {{ $absen->count() ? $absen->map(fn($p) => $p->siswa->nis ?? '-')->implode(', ') : '-' }}
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>

        <table class="info">
            <tr><td>Catatan selama pelaksanaan ujian</td><td>: {{ $catatan }}</td></tr>
        </table>

        <p>Demikian berita acara Pelaksanaan Penilaian Sumatif Akhir Jenjang ini dibuat dengan sesungguhnya.</p>
    </div>

    {{-- TANDA TANGAN --}}
    <div class="ttd">
        <table>
            <tr>
                <td width="50%">&nbsp;</td>
                <td width="50%">{{ config('app.sekolah.kota') }}, {{ $tgl->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>Pengawas,</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    @if($hasBA && $beritaAcara->ttd_pengawas)
                        <img src="{{ $beritaAcara->ttd_pengawas }}" class="ttd-img"><br>
                    @else
                        <br><br><br><br>
                    @endif
                    <u>{{ $pengawas ?: '.................................' }}</u>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
        </table>
    </div>
</body>
</html>
