@extends('layouts.app')
@section('title', 'Hasil: ' . $ujian->nama)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Hasil: {{ $ujian->nama }}</h5>
    <div>
        @if($ujian->soal->where('tipe_soal','essay')->count())
            <a href="{{ route('guru.hasil.essay', $ujian) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i> Nilai Essay</a>
        @endif
        <a href="{{ route('guru.export.excel', $ujian) }}" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
        <a href="{{ route('guru.export.pdf', $ujian) }}" class="btn btn-danger btn-sm"><i class="bi bi-file-earmark-pdf"></i> Export PDF</a>
        <a href="{{ route('guru.hasil.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
</div>

<!-- Statistik -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Peserta</div>
                <div class="fs-3 fw-bold text-primary">{{ $hasil->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Rata-rata</div>
                <div class="fs-3 fw-bold text-success">{{ number_format($hasil->avg('nilai_akhir'), 1) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Tertinggi</div>
                <div class="fs-3 fw-bold text-info">{{ number_format($hasil->max('nilai_akhir'), 1) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Terendah</div>
                <div class="fs-3 fw-bold text-danger">{{ number_format($hasil->min('nilai_akhir'), 1) }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Kelas -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 align-items-center">
            <select name="kelas_id" class="form-select form-select-sm" style="width:200px" onchange="this.form.submit()">
                <option value="">Semua Kelas</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Rank</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th class="text-center">PG Benar</th>
                        <th class="text-center">Nilai</th>
                        <th>Status</th>
                        <th width="80">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hasil->sortByDesc('nilai_akhir')->values() as $i => $h)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $i + 1 }}</span></td>
                        <td>{{ $h->siswa->nis ?? '-' }}</td>
                        <td class="fw-semibold">{{ $h->siswa->nama ?? '-' }}</td>
                        <td>{{ $h->siswa->kelas->nama ?? '-' }}</td>
                        <td class="text-center text-success">{{ $h->benar_pg ?? 0 }}</td>
                        <td class="text-center">
                            <span class="fw-bold {{ $h->nilai_akhir >= ($ujian->kkm ?? 75) ? 'text-success' : 'text-danger' }}">
                                {{ number_format($h->nilai_akhir, 1) }}
                            </span>
                        </td>
                        <td>
                            @if($h->status_kelulusan == 'lulus') <span class="badge bg-success">Lulus</span>
                            @elseif($h->status_kelulusan == 'belum_dinilai') <span class="badge bg-warning">Belum Final</span>
                            @elseif($h->status_kelulusan == 'tidak_lulus') <span class="badge bg-danger">Tidak Lulus</span>
                            @else <span class="badge bg-secondary">{{ $h->status_kelulusan }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('guru.hasil.detail', [$ujian, $h->siswa]) }}" class="btn btn-outline-primary btn-sm" title="Detail Pengerjaan">
                                <i class="bi bi-file-earmark-text"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada hasil</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
