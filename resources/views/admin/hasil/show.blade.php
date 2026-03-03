@extends('layouts.app')
@section('title', 'Hasil: ' . $ujian->nama_ujian)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Hasil: {{ $ujian->nama_ujian }}</h5>
    <div class="d-flex gap-2">
        @if($essayBelumDinilai > 0)
            <a href="{{ route('admin.hasil.essay', $ujian) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil-square"></i> Nilai Essay <span class="badge bg-danger">{{ $essayBelumDinilai }}</span>
            </a>
        @endif
        <a href="{{ route('admin.hasil.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<!-- Statistik -->
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Peserta</div>
                <div class="fs-3 fw-bold text-primary">{{ $hasil->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Rata-rata</div>
                <div class="fs-3 fw-bold text-success">{{ number_format($hasil->avg('nilai_akhir'), 1) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Tertinggi</div>
                <div class="fs-3 fw-bold text-info">{{ number_format($hasil->max('nilai_akhir'), 1) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Terendah</div>
                <div class="fs-3 fw-bold text-danger">{{ number_format($hasil->min('nilai_akhir'), 1) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Lulus</div>
                <div class="fs-3 fw-bold text-success">{{ $hasil->where('status_kelulusan', 'lulus')->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">KKM</div>
                <div class="fs-3 fw-bold text-secondary">{{ $ujian->kkm }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Kelas -->
@if($kelas->count() > 1)
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="d-flex gap-2 align-items-center">
            <span class="small text-muted">Filter:</span>
            <a href="{{ route('admin.hasil.show', $ujian) }}" class="btn btn-sm {{ !request('kelas_id') ? 'btn-primary' : 'btn-outline-primary' }}">Semua</a>
            @foreach($kelas as $k)
                <a href="{{ route('admin.hasil.show', ['ujian' => $ujian, 'kelas_id' => $k->id]) }}"
                   class="btn btn-sm {{ request('kelas_id') == $k->id ? 'btn-primary' : 'btn-outline-primary' }}">{{ $k->nama }}</a>
            @endforeach
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="50">Rank</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th class="text-center">PG</th>
                        <th class="text-center">Isian</th>
                        <th class="text-center">Essay</th>
                        <th class="text-center">Nilai Akhir</th>
                        <th>Status</th>
                        <th width="100">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $filtered = $hasil;
                        if (request('kelas_id')) {
                            $filtered = $hasil->filter(fn($h) => $h->siswa && $h->siswa->kelas_id == request('kelas_id'));
                        }
                    @endphp
                    @forelse($filtered->sortByDesc('nilai_akhir')->values() as $i => $h)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $i + 1 }}</span></td>
                        <td><code>{{ $h->siswa->nis ?? '-' }}</code></td>
                        <td class="fw-semibold">{{ $h->siswa->nama ?? '-' }}</td>
                        <td>{{ $h->siswa->kelas->nama ?? '-' }}</td>
                        <td class="text-center">{{ number_format($h->skor_pg, 1) }}</td>
                        <td class="text-center">{{ number_format($h->skor_isian, 1) }}</td>
                        <td class="text-center">
                            @if($h->skor_essay > 0)
                                {{ number_format($h->skor_essay, 1) }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="fw-bold fs-5 {{ $h->nilai_akhir >= $ujian->kkm ? 'text-success' : 'text-danger' }}">
                                {{ number_format($h->nilai_akhir, 1) }}
                            </span>
                        </td>
                        <td>
                            @if($h->status_kelulusan == 'lulus')
                                <span class="badge bg-success">Lulus</span>
                            @elseif($h->status_kelulusan == 'belum_dinilai')
                                <span class="badge bg-warning">Belum Final</span>
                            @else
                                <span class="badge bg-danger">Tidak Lulus</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.hasil.detail', [$ujian, $h->siswa]) }}" class="btn btn-outline-primary btn-sm" title="Detail Pengerjaan">
                                <i class="bi bi-file-earmark-text"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">Belum ada hasil</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
