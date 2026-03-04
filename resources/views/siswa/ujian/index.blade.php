@extends('layouts.app')
@section('title', 'Daftar Ujian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Daftar Ujian</h5>
    <a href="{{ route('siswa.ujian.token') }}" class="btn btn-primary btn-sm"><i class="bi bi-key"></i> Masukkan Token</a>
</div>

<div class="row g-3">
    @forelse($ujian as $u)
    @php $status = $pesertaStatus[$u->id] ?? null; @endphp
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1">{{ $u->nama }}</h6>
                        <small class="text-muted d-block">{{ $u->mapel->nama ?? '-' }}</small>
                    </div>
                    @if($u->status == 'aktif')
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-dark">Selesai</span>
                    @endif
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col">
                        <small class="text-muted">Durasi</small>
                        <div class="fw-semibold">{{ $u->durasi }} menit</div>
                    </div>
                    <div class="col">
                        <small class="text-muted">Soal</small>
                        <div class="fw-semibold">{{ $u->soal->count() }}</div>
                    </div>
                    <div class="col">
                        <small class="text-muted">Status Anda</small>
                        <div class="fw-semibold">
                            @if($status == 'selesai') <span class="text-success">Selesai</span>
                            @elseif($status == 'mengerjakan') <span class="text-warning">Sedang Mengerjakan</span>
                            @else <span class="text-muted">Belum Mulai</span>
                            @endif
                        </div>
                    </div>
                </div>
                @if($u->status == 'aktif' && $status != 'selesai')
                <div class="mt-3 text-center">
                    <a href="{{ route('siswa.ujian.token') }}" class="btn btn-primary btn-sm"><i class="bi bi-play-fill"></i> Masuk Ujian</a>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center text-muted py-5">
            <i class="bi bi-clipboard-x fs-1"></i>
            <p class="mt-2">Tidak ada ujian tersedia</p>
        </div>
    </div>
    @endforelse
</div>
@endsection
