@extends('layouts.app')
@section('title', 'Dashboard Proktor')

@section('content')
@if($ruang)
<div class="alert alert-info d-flex align-items-center mb-3">
    <i class="bi bi-geo-alt-fill me-2"></i>
    Ruang Anda: <strong class="ms-1">{{ $ruang->nama }}</strong> <span class="text-muted ms-2">({{ $ruang->kode }})</span>
</div>
@else
<div class="alert alert-warning d-flex align-items-center mb-3">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    Anda belum ditugaskan ke ruang ujian. Hubungi admin.
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-success bg-opacity-10 text-success"><i class="bi bi-lightning-fill"></i></div>
                <div>
                    <div class="text-muted small">Ujian Aktif</div>
                    <div class="fs-3 fw-bold">{{ $ujianAktif->count() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h6 class="mb-0"><i class="bi bi-display"></i> Ujian Aktif Saat Ini</h6></div>
    <div class="card-body">
        @forelse($ujianAktif as $u)
        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded mb-2">
            <div>
                <div class="fw-semibold">{{ $u->nama }}</div>
                <small class="text-muted">{{ $u->mapel->nama ?? '-' }} | Token: <code class="fs-6">{{ $u->token }}</code></small>
            </div>
            <a href="{{ route('proktor.monitor.show', $u) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-display"></i> Monitor
            </a>
        </div>
        @empty
        <div class="text-center text-muted py-4">
            <i class="bi bi-display fs-1"></i>
            <p class="mt-2">Tidak ada ujian aktif saat ini</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
