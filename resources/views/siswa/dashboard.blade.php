@extends('layouts.app')
@section('title', 'Dashboard Siswa')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card border-start border-4 border-primary">
            <div class="card-body">
                <div class="text-muted small">Ujian Tersedia</div>
                <div class="fs-3 fw-bold text-primary">{{ $ujianTersedia->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card border-start border-4 border-success">
            <div class="card-body">
                <div class="text-muted small">Ujian Selesai</div>
                <div class="fs-3 fw-bold text-success">{{ $riwayat->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card border-start border-4 border-warning">
            <div class="card-body">
                <div class="text-muted small">Rata-rata Nilai</div>
                @php
                    $nilaiVisible = $riwayat->filter(fn($h) => $h->ujian && $h->ujian->tampilkan_nilai);
                @endphp
                <div class="fs-3 fw-bold text-warning">{{ $nilaiVisible->count() > 0 ? number_format($nilaiVisible->avg('nilai_akhir'), 1) : '-' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-clipboard-check"></i> Ujian Tersedia</h6></div>
            <div class="card-body">
                @forelse($ujianTersedia as $u)
                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded mb-2">
                    <div>
                        <div class="fw-semibold">{{ $u->nama }}</div>
                        <small class="text-muted">{{ $u->mapel->nama ?? '-' }} | {{ $u->durasi }} menit</small>
                    </div>
                    <a href="{{ route('siswa.ujian.token') }}" class="btn btn-primary btn-sm"><i class="bi bi-play-fill"></i> Mulai</a>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-clipboard-x fs-1"></i>
                    <p class="mt-2">Tidak ada ujian tersedia saat ini</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Terbaru</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Ujian</th><th>Nilai</th></tr></thead>
                        <tbody>
                            @forelse($riwayat->take(5) as $h)
                            <tr>
                                <td>{{ $h->ujian->nama ?? '-' }}</td>
                                <td class="fw-bold">
                                    @if($h->ujian && $h->ujian->tampilkan_nilai)
                                        <span class="{{ $h->nilai_akhir >= ($h->ujian->kkm ?? 75) ? 'text-success' : 'text-danger' }}">{{ number_format($h->nilai_akhir, 1) }}</span>
                                    @else
                                        <span class="text-muted"><i class="bi bi-eye-slash"></i></span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">Belum ada riwayat</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
