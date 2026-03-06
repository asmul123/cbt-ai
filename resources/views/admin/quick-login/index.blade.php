@extends('layouts.app')
@section('title', 'Quick Login Proktor')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1"><i class="bi bi-box-arrow-in-right"></i> Quick Login Proktor</h5>
        <small class="text-muted">Klik ruang untuk langsung masuk sebagai proktor di ruang tersebut</small>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-3">
    @forelse($ruangan as $ruang)
    <div class="col-md-6 col-lg-4 col-xl-3">
        <div class="card h-100 border-0 shadow-sm ruang-card">
            <div class="card-body text-center p-4">
                {{-- Icon Ruang --}}
                <div class="ruang-icon mx-auto mb-3 {{ $ruang->proktor->count() > 0 ? 'bg-primary' : 'bg-secondary' }}">
                    <i class="bi bi-display fs-3 text-white"></i>
                </div>

                {{-- Info Ruang --}}
                <h6 class="fw-bold mb-1">{{ $ruang->nama }}</h6>
                <div class="text-muted small mb-2">
                    <span class="badge bg-light text-dark">{{ $ruang->kode }}</span>
                    @if($ruang->lokasi)
                    <span class="ms-1">{{ $ruang->lokasi }}</span>
                    @endif
                </div>

                {{-- Statistik --}}
                <div class="d-flex justify-content-center gap-3 mb-3 small">
                    <span title="Kapasitas"><i class="bi bi-people text-muted"></i> {{ $ruang->kapasitas ?? '-' }}</span>
                    <span title="Peserta Aktif" class="{{ $ruang->peserta_aktif_count > 0 ? 'text-success fw-semibold' : 'text-muted' }}">
                        <i class="bi bi-person-check"></i> {{ $ruang->peserta_aktif_count }} aktif
                    </span>
                </div>

                {{-- Daftar Proktor dan Tombol Login --}}
                @if($ruang->proktor->count() > 0)
                    @foreach($ruang->proktor as $proktor)
                    <form action="{{ route('admin.quick-login.loginAs', $proktor) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2"
                                onclick="return confirm('Login sebagai proktor {{ $proktor->name }} di ruang {{ $ruang->nama }}?')">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span>{{ $proktor->name }}</span>
                        </button>
                    </form>
                    @endforeach
                @else
                    <div class="text-muted small py-2">
                        <i class="bi bi-exclamation-circle"></i> Belum ada proktor
                    </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center py-5">
            <div class="mb-3" style="font-size: 4rem; opacity: 0.3;"><i class="bi bi-geo-alt"></i></div>
            <h6 class="text-muted">Belum ada ruang ujian aktif</h6>
            <a href="{{ route('admin.ruang.create') }}" class="btn btn-primary btn-sm mt-2">
                <i class="bi bi-plus"></i> Tambah Ruang Ujian
            </a>
        </div>
    </div>
    @endforelse
</div>

@push('styles')
<style>
    .ruang-card {
        transition: all 0.2s ease;
        border-radius: 12px;
    }
    .ruang-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
    }
    .ruang-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush
@endsection
