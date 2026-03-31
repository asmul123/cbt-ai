@extends('layouts.app')
@section('title', 'Cetak Berita Acara & Daftar Hadir')

@section('content')
<h5 class="mb-3"><i class="bi bi-printer"></i> Cetak Berita Acara & Daftar Hadir</h5>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    <a href="{{ route('admin.cetak.rekapTidakHadir') }}" class="btn btn-warning btn-sm">
        <i class="bi bi-person-x"></i> Rekap Tidak Hadir Per Mapel
    </a>
</div>

{{-- Pilih Ujian --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 align-items-center">
            <label class="form-label mb-0 fw-semibold me-2">Pilih Ujian:</label>
            <select name="ujian_id" class="form-select form-select-sm" style="width:350px" onchange="this.form.submit()">
                <option value="">-- Pilih Ujian --</option>
                @foreach($ujianList as $u)
                    <option value="{{ $u->id }}" {{ request('ujian_id') == $u->id ? 'selected' : '' }}>
                        {{ $u->nama_ujian }} ({{ $u->mapel->nama ?? '-' }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>
</div>

@if($selectedUjian)
{{-- Info Ujian --}}
<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted" width="150">Ujian</td><td>: <strong>{{ $selectedUjian->nama_ujian }}</strong></td></tr>
                    <tr><td class="text-muted">Mata Pelajaran</td><td>: {{ $selectedUjian->mapel->nama ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Kelas</td><td>: {{ $selectedUjian->kelas->pluck('nama')->implode(', ') ?: '-' }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted" width="150">Tanggal</td><td>: {{ $selectedUjian->tanggal_mulai?->translatedFormat('l, d F Y') ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Status</td><td>: <span class="badge bg-{{ $selectedUjian->status == 'selesai' ? 'dark' : 'success' }}">{{ ucfirst($selectedUjian->status) }}</span></td></tr>
                    <tr><td class="text-muted">Ruang Terdaftar</td><td>: {{ $selectedUjian->ruang->pluck('nama')->implode(', ') ?: '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Daftar Berita Acara per Ruangan --}}
<div class="card">
    <div class="card-header"><h6 class="mb-0"><i class="bi bi-file-text"></i> Berita Acara per Ruangan</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Ruangan</th>
                        <th>Pengawas</th>
                        <th>Waktu</th>
                        <th>Hadir / Total</th>
                        <th>TTD Siswa</th>
                        <th>TTD Pengawas</th>
                        <th>Cetak</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($beritaAcaraList as $i => $ba)
                    @php
                        $totalPeserta = \App\Models\PesertaUjian::where('ujian_id', $selectedUjian->id)
                            ->where('ruang_ujian_id', $ba->ruang_ujian_id)->count();
                        $tidakHadir = count($ba->peserta_tidak_hadir ?? []);
                        $hadir = $totalPeserta - $tidakHadir;
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $ba->ruangUjian->nama ?? '-' }}</td>
                        <td>{{ $ba->proktor->name ?? '-' }}</td>
                        <td>{{ $ba->waktu_mulai }} - {{ $ba->waktu_selesai }}</td>
                        <td>
                            <span class="text-success fw-bold">{{ $hadir }}</span> / {{ $totalPeserta }}
                            @if($tidakHadir > 0)
                                <span class="badge bg-danger ms-1">{{ $tidakHadir }} absen</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $ba->tanda_tangan_hadir_count >= $hadir ? 'success' : 'warning' }}">
                                {{ $ba->tanda_tangan_hadir_count }} / {{ $hadir }}
                            </span>
                        </td>
                        <td>
                            @if($ba->ttd_pengawas)
                                <span class="badge bg-success"><i class="bi bi-check"></i> BA</span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                            @if($ba->ttd_pengawas_hadir)
                                <span class="badge bg-success"><i class="bi bi-check"></i> DH</span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.cetak.beritaAcara', $ba) }}" class="btn btn-info btn-sm" title="Cetak Berita Acara">
                                    <i class="bi bi-file-text"></i> BA
                                </a>
                                <a href="{{ route('admin.cetak.daftarHadir', $ba) }}" class="btn btn-primary btn-sm" title="Cetak Daftar Hadir">
                                    <i class="bi bi-person-lines-fill"></i> DH
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-info-circle"></i> Belum ada berita acara yang diisi untuk ujian ini.
                            <br><small>Proktor perlu mengisi berita acara dari menu monitor.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-arrow-up-circle" style="font-size:2rem;"></i>
        <p class="mt-2">Pilih ujian untuk melihat daftar berita acara dan mencetak dokumen.</p>
    </div>
</div>
@endif
@endsection
