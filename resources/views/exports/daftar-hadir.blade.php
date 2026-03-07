<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Hadir - {{ $ujian->nama }}</title>
    <style>
        @page { margin: 20mm 15mm 20mm 15mm; }
        body { font-family: 'Times New Roman', serif; font-size: 12pt; margin: 0; padding: 0; line-height: 1.4; }

        /* KOP Surat */
        .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 20px; }
        .kop .instansi { font-size: 11pt; font-weight: bold; margin: 0; }
        .kop .sekolah { font-size: 16pt; font-weight: bold; margin: 2px 0; }
        .kop .alamat { font-size: 9pt; margin: 0; }
        .kop .kontak { font-size: 9pt; margin: 0; }

        /* Title */
        .title { text-align: center; margin: 15px 0 5px; font-size: 13pt; font-weight: bold; text-decoration: underline; }
        .subtitle { text-align: center; font-size: 12pt; font-weight: bold; margin-bottom: 20px; }

        /* Info */
        .info td { padding: 2px 5px; vertical-align: top; border: none; font-size: 12pt; }

        /* Table Daftar Hadir */
        .daftar-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 11pt; }
        .daftar-table th, .daftar-table td { border: 1px solid #000; padding: 5px 8px; }
        .daftar-table th { background: #f0f0f0; text-align: center; font-weight: bold; }
        .daftar-table td.center { text-align: center; }
        .daftar-table td.no { text-align: center; width: 35px; }
        .daftar-table td.nis { text-align: center; width: 100px; }
        .daftar-table td.ttd { width: 120px; text-align: center; }
        .daftar-table td.ket { width: 80px; text-align: center; font-size: 10pt; }
        .ttd-img { max-height: 35px; max-width: 100px; }

        /* Rekap */
        .rekap { margin-top: 15px; font-size: 11pt; }
        .rekap td { padding: 2px 5px; border: none; }

        /* TTD */
        .ttd-section { margin-top: 30px; }
        .ttd-section table { width: 100%; }
        .ttd-section td { border: none; text-align: center; vertical-align: top; padding: 5px 10px; }
        .ttd-sign { max-height: 80px; max-width: 200px; }
    </style>
</head>
<body>
    @php
        $hasBA = isset($beritaAcara);
        $tgl = $ujian->tanggal_mulai ? \Carbon\Carbon::parse($ujian->tanggal_mulai) : now();

        if ($hasBA) {
            $waktuMulai = $beritaAcara->waktu_mulai ?? $tgl->format('H:i');
            $waktuSelesai = $beritaAcara->waktu_selesai ?? $tgl->copy()->addMinutes($ujian->durasi)->format('H:i');
            $ruangName = $beritaAcara->ruangUjian->nama ?? '-';
            $pengawas = $beritaAcara->nama_pengawas ?: ($beritaAcara->proktor->name ?? '');
            $tidakHadirIds = $tidakHadirIds ?? ($beritaAcara->peserta_tidak_hadir ?? []);
        } else {
            $waktuMulai = $tgl->format('H:i');
            $waktuSelesai = $ujian->tanggal_selesai ? \Carbon\Carbon::parse($ujian->tanggal_selesai)->format('H:i') : $tgl->copy()->addMinutes($ujian->durasi)->format('H:i');
            $ruangName = $ujian->ruang->pluck('nama')->implode(', ') ?? '-';
            $pengawas = $pengawas ?? '';
            $tidakHadirIds = [];
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
    <div class="title">DAFTAR HADIR PESERTA</div>
    <div class="subtitle">PENILAIAN SUMATIF AKHIR JENJANG<br>TAHUN PELAJARAN {{ $tahunAjaran ?? date('Y') . '-' . (date('Y')+1) }}</div>

    {{-- INFO UJIAN --}}
    <table class="info">
        <tr><td width="120">Kelas</td><td>: {{ $kelasNames }}</td></tr>
        <tr><td>Ruang</td><td>: {{ $ruangName }}</td></tr>
        <tr><td>Mata Pelajaran</td><td>: {{ $ujian->mapel->nama ?? '-' }}</td></tr>
        <tr><td>Hari</td><td>: {{ $tgl->translatedFormat('l, d F Y') }}</td></tr>
        <tr><td>Waktu</td><td>: Pukul {{ $waktuMulai }} s.d. {{ $waktuSelesai }}</td></tr>
    </table>

    {{-- TABEL DAFTAR HADIR --}}
    <table class="daftar-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Peserta</th>
                <th>Nama Peserta Didik</th>
                @if($hasBA)
                <th>Ket.</th>
                @endif
                <th>Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($peserta as $i => $p)
            @php
                $isAbsen = in_array($p->id, $tidakHadirIds);
                $ttd = isset($ttdMap) ? ($ttdMap[$p->id] ?? null) : null;
            @endphp
            <tr>
                <td class="no">{{ $i + 1 }}.</td>
                <td class="nis">{{ $p->siswa->nis ?? '-' }}</td>
                <td>{{ $p->siswa->nama ?? '-' }}</td>
                @if($hasBA)
                <td class="ket">
                    @if($isAbsen)
                        <strong style="color:red;">A</strong>
                    @else
                        H
                    @endif
                </td>
                @endif
                <td class="ttd">
                    @if($ttd && !$isAbsen)
                        <img src="{{ $ttd }}" class="ttd-img">
                    @elseif($isAbsen)
                        -
                    @else
                        &nbsp;
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- REKAP KEHADIRAN --}}
    @if($hasBA)
    @php
        $totalPeserta = $peserta->count();
        $totalTidakHadir = count($tidakHadirIds);
        $totalHadir = $totalPeserta - $totalTidakHadir;
    @endphp
    <table class="rekap">
        <tr><td width="200"><strong>Jumlah Hadir</strong></td><td>: {{ $totalHadir }} peserta didik</td></tr>
        <tr><td><strong>Jumlah Tidak Hadir</strong></td><td>: {{ $totalTidakHadir }} peserta didik</td></tr>
        <tr><td><strong>Total</strong></td><td>: {{ $totalPeserta }} peserta didik</td></tr>
    </table>
    @endif

    {{-- TANDA TANGAN PENGAWAS --}}
    <div class="ttd-section">
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
                    @if($hasBA && $beritaAcara->ttd_pengawas_hadir)
                        <img src="{{ $beritaAcara->ttd_pengawas_hadir }}" class="ttd-sign"><br>
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
