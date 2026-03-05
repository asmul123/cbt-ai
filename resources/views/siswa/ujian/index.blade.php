@extends('layouts.app')
@section('title', 'Daftar Ujian')

@push('styles')
<style>
    .ujian-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: transform 0.2s, box-shadow 0.2s;
        overflow: hidden;
    }
    .ujian-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.12); }
    .ujian-card .card-top {
        background: linear-gradient(135deg, #1e3a5f, #2c5282);
        color: white;
        padding: 16px 18px 14px;
    }
    .ujian-card .stat-item { text-align: center; padding: 10px 0; }
    .ujian-card .stat-item .stat-value { font-size: 1.1rem; font-weight: 700; }
    .ujian-card .stat-item .stat-label { font-size: 0.72rem; color: #94a3b8; text-transform: uppercase; }
    .badge-status { font-size: 0.75rem; padding: 5px 10px; border-radius: 20px; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0 fw-bold">Daftar Ujian</h5>
        <small class="text-muted">Ujian yang tersedia untuk kelas Anda</small>
    </div>
    <a href="{{ route('siswa.ujian.token') }}" class="btn btn-primary btn-sm px-3">
        <i class="bi bi-key me-1"></i> Masukkan Token
    </a>
</div>

<div class="row g-3">
    @forelse($ujian as $u)
    @php $status = $pesertaStatus[$u->id] ?? null; @endphp
    <div class="col-md-6 col-lg-4">
        <div class="card ujian-card h-100">
            <div class="card-top">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1 me-2">
                        <h6 class="mb-1 fw-bold">{{ $u->nama }}</h6>
                        <small class="opacity-75"><i class="bi bi-book me-1"></i>{{ $u->mapel->nama ?? '-' }}</small>
                    </div>
                    @if($status == 'selesai')
                        <span class="badge-status bg-success text-white"><i class="bi bi-check-circle me-1"></i>Selesai</span>
                    @elseif($status == 'mengerjakan')
                        <span class="badge-status bg-warning text-dark"><i class="bi bi-pencil me-1"></i>Berlangsung</span>
                    @else
                        <span class="badge-status bg-light text-dark"><i class="bi bi-clock me-1"></i>Belum Mulai</span>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                <div class="row g-0 border-bottom">
                    <div class="col-4 stat-item border-end">
                        <div class="stat-value">{{ $u->durasi }}'</div>
                        <div class="stat-label">Durasi</div>
                    </div>
                    <div class="col-4 stat-item border-end">
                        <div class="stat-value">{{ $u->jumlah_soal ?? $u->soal->count() }}</div>
                        <div class="stat-label">Soal</div>
                    </div>
                    <div class="col-4 stat-item">
                        <div class="stat-value">{{ $u->kkm ?? '-' }}</div>
                        <div class="stat-label">KKM</div>
                    </div>
                </div>
                @if($status != 'selesai')
                <div class="p-3">
                    <a href="{{ route('siswa.ujian.token') }}" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-{{ $status == 'mengerjakan' ? 'arrow-right-circle' : 'play-fill' }} me-1"></i>
                        {{ $status == 'mengerjakan' ? 'Lanjutkan Ujian' : 'Masuk Ujian' }}
                    </a>
                </div>
                @else
                <div class="p-3">
                    <div class="text-center text-success small py-1">
                        <i class="bi bi-check-circle-fill me-1"></i> Ujian telah selesai dikerjakan
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center py-5">
            <div class="mb-3" style="font-size: 4rem; opacity: 0.3;">📋</div>
            <h6 class="text-muted">Tidak ada ujian tersedia</h6>
            <p class="text-muted small">Ujian akan muncul di sini saat guru/admin mengaktifkannya.</p>
        </div>
    </div>
    @endforelse
</div>
@endsection
