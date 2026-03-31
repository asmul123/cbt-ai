<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Tidak Hadir Per Mapel</title>
    <style>
        @page { margin: 15mm 12mm 15mm 12mm; }
        body { font-family: 'Times New Roman', serif; font-size: 11pt; margin: 0; padding: 0; line-height: 1.4; }

        .kop { border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 15px; }
        .kop table { width: 100%; }
        .kop td { border: none; vertical-align: middle; }
        .kop .logo-cell { width: 80px; text-align: center; }
        .kop .logo-cell img { max-height: 75px; max-width: 75px; }
        .kop .text-cell { text-align: center; }
        .kop .instansi { font-size: 11pt; font-weight: bold; margin: 0; }
        .kop .sekolah { font-size: 16pt; font-weight: bold; margin: 2px 0; }
        .kop .alamat { font-size: 9pt; margin: 0; }
        .kop .kontak { font-size: 9pt; margin: 0; }

        .title { text-align: center; margin: 10px 0 3px; font-size: 13pt; font-weight: bold; text-decoration: underline; }
        .subtitle { text-align: center; font-size: 11pt; font-weight: bold; margin-bottom: 15px; }

        .mapel-header { background: #e9ecef; padding: 5px 10px; font-weight: bold; font-size: 11pt; margin-top: 15px; border: 1px solid #000; border-bottom: none; }
        .ujian-label { padding: 3px 10px; font-weight: bold; font-size: 10pt; background: #f8f9fa; border: 1px solid #000; border-bottom: none; }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        table.data th, table.data td { border: 1px solid #000; padding: 3px 6px; font-size: 10pt; }
        table.data th { background: #dee2e6; text-align: center; }
        table.data td { vertical-align: top; }

        table.ringkasan { width: 60%; border-collapse: collapse; margin-top: 20px; }
        table.ringkasan th, table.ringkasan td { border: 1px solid #000; padding: 4px 8px; font-size: 10pt; }
        table.ringkasan th { background: #dee2e6; text-align: center; }

        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('id');
        $logoPath = public_path('logo_jabar.png');
    @endphp

    {{-- KOP SURAT --}}
    <div class="kop">
        <table>
            <tr>
                <td class="logo-cell">
                    @if(file_exists($logoPath))
                        <img src="{{ $logoPath }}" alt="Logo">
                    @endif
                </td>
                <td class="text-cell">
                    <p class="instansi">{{ config('app.sekolah.instansi') }}<br>{{ config('app.sekolah.dinas') }}</p>
                    <p class="sekolah">{{ config('app.sekolah.nama') }}</p>
                    <p class="alamat">{{ config('app.sekolah.alamat') }} Telp {{ config('app.sekolah.telp') }} Fax: {{ config('app.sekolah.fax') }}</p>
                    <p class="kontak">Website : {{ config('app.sekolah.website') }} Email : {{ config('app.sekolah.email') }}</p>
                    <p class="alamat">{{ config('app.sekolah.kecamatan') }} - {{ config('app.sekolah.kota') }} {{ config('app.sekolah.kodepos') }}</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- JUDUL --}}
    <div class="title">REKAPITULASI PESERTA DIDIK TIDAK HADIR</div>
    <div class="subtitle">PENILAIAN SUMATIF AKHIR JENJANG<br>TAHUN PELAJARAN {{ $tahunAjaran }}</div>

    @if($rekapPerMapel->isEmpty())
        <p style="text-align:center; margin-top:30px;">Tidak ada peserta didik yang tidak hadir.</p>
    @else
        @foreach($rekapPerMapel as $mapelNama => $ujianGroup)
            <div class="mapel-header">{{ $mapelNama }}</div>
            @foreach($ujianGroup as $ujianNama => $pesertaList)
                <div class="ujian-label">{{ $ujianNama }} — {{ $pesertaList->count() }} tidak hadir</div>
                <table class="data">
                    <thead>
                        <tr>
                            <th width="30">No</th>
                            <th width="80">NIS</th>
                            <th>Nama Peserta Didik</th>
                            <th width="100">Kelas</th>
                            <th width="100">Ruangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pesertaList as $i => $p)
                        <tr>
                            <td style="text-align:center">{{ $i + 1 }}</td>
                            <td>{{ $p->siswa->nis ?? '-' }}</td>
                            <td>{{ $p->siswa->nama ?? '-' }}</td>
                            <td>{{ $p->siswa->kelas->nama ?? '-' }}</td>
                            <td>{{ $p->ruangUjian->nama ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @endforeach

        {{-- RINGKASAN --}}
        <div style="margin-top:25px;">
            <p style="font-weight:bold; font-size:11pt;">Ringkasan:</p>
            <table class="ringkasan">
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>Mata Pelajaran</th>
                        <th width="100">Jml Ujian</th>
                        <th width="100">Tidak Hadir</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; $grandTotal = 0; @endphp
                    @foreach($rekapPerMapel as $mapelNama => $ujianGroup)
                    @php
                        $totalPerMapel = $ujianGroup->flatten(1)->count();
                        $grandTotal += $totalPerMapel;
                    @endphp
                    <tr>
                        <td style="text-align:center">{{ $no++ }}</td>
                        <td>{{ $mapelNama }}</td>
                        <td style="text-align:center">{{ $ujianGroup->count() }}</td>
                        <td style="text-align:center">{{ $totalPerMapel }}</td>
                    </tr>
                    @endforeach
                    <tr style="font-weight:bold; background:#f0f0f0;">
                        <td colspan="3" style="text-align:right; padding-right:10px;">Grand Total</td>
                        <td style="text-align:center">{{ $grandTotal }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    {{-- TANDA TANGAN --}}
    <div style="margin-top:40px;">
        <table style="width:100%; border:none;">
            <tr>
                <td style="border:none; width:50%;">&nbsp;</td>
                <td style="border:none; width:50%; text-align:center;">
                    {{ config('app.sekolah.kota') }}, {{ now()->translatedFormat('d F Y') }}
                    <br>Mengetahui,
                    <br>Kepala Sekolah
                    <br><br><br><br>
                    <u>{{ config('app.sekolah.kepala_sekolah', '..............................') }}</u>
                    <br>NIP. {{ config('app.sekolah.nip_kepala', '..............................') }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
