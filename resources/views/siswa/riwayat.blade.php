@extends('layouts.app')
@section('title', 'Riwayat Ujian')

@push('styles')
<style>
    .page-title { font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 20px; }
    .riwayat-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        padding: 16px 18px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: box-shadow 0.15s, transform 0.15s;
        border: 1px solid #f1f5f9;
    }
    .riwayat-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.1); transform: translateY(-1px); }
    .nilai-circle {
        width: 52px; height: 52px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800;
        font-size: 1rem;
        flex-shrink: 0;
        border: 2.5px solid transparent;
    }
    .nilai-lulus        { background: #dcfce7; color: #16a34a; border-color: #bbf7d0; }
    .nilai-tidak-lulus  { background: #fee2e2; color: #dc2626; border-color: #fecaca; }
    .nilai-hidden       { background: #f1f5f9; color: #94a3b8; border-color: #e2e8f0; font-size: 1.3rem; }
    .riwayat-body { flex: 1; min-width: 0; }
    .riwayat-nama { font-size: 0.9rem; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .riwayat-mapel { font-size: 0.75rem; color: #64748b; margin-top: 2px; }
    .riwayat-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .rchip {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 0.71rem; color: #64748b;
        background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 20px; padding: 3px 9px;
    }
    .rchip.green { background: #f0fdf4; border-color: #bbf7d0; color: #16a34a; }
    .rchip.red   { background: #fff1f2; border-color: #fecaca; color: #dc2626; }
    .rchip.blue  { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; }
    .status-badge {
        font-size: 0.68rem; font-weight: 600;
        padding: 3px 10px; border-radius: 20px;
        white-space: nowrap; flex-shrink: 0;
    }
    .badge-selesai     { background: #dcfce7; color: #16a34a; }
    .badge-belum-final { background: #fef9c3; color: #92400e; }
    .badge-lain        { background: #f1f5f9; color: #64748b; }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    }
    .empty-icon {
        width: 64px; height: 64px;
        background: #f1f5f9; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.7rem; color: #94a3b8;
        margin-bottom: 14px;
    }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="page-title mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Ujian</div>
    <span class="text-muted small">{{ $hasil->total() }} ujian</span>
</div>

@forelse($hasil as $h)
@php
    $tampil       = $h->ujian && $h->ujian->tampilkan_nilai;
    $belumDinilai = $h->status === 'belum_dinilai';
    $nilai        = $tampil ? number_format($h->nilai_akhir, 0) : null;
    $lulus        = $tampil && !$belumDinilai && $h->nilai_akhir >= ($h->ujian->kkm ?? 75);
    $nilaiClass   = !$tampil ? 'nilai-hidden' : ($belumDinilai ? 'nilai-hidden' : ($lulus ? 'nilai-lulus' : 'nilai-tidak-lulus'));
    $benar        = $h->benar_pg ?? 0;
    $salah        = ($h->jumlah_soal ?? 0) - $benar;
    $tgl          = ($h->waktu_selesai ?? $h->created_at)?->translatedFormat('d M Y · H:i');
@endphp
<div class="riwayat-card">
    {{-- Nilai --}}
    <div class="nilai-circle {{ $nilaiClass }}">
        @if($tampil) {{ $nilai }} @else <i class="bi bi-eye-slash"></i> @endif
    </div>

    {{-- Info --}}
    <div class="riwayat-body">
        <div class="riwayat-nama">{{ $h->ujian->nama ?? '-' }}</div>
        <div class="riwayat-mapel"><i class="bi bi-book me-1"></i>{{ $h->ujian->mapel->nama ?? '-' }}</div>
        <div class="riwayat-chips">
            @if($tgl)
            <span class="rchip"><i class="bi bi-calendar3"></i> {{ $tgl }}</span>
            @endif
            @if($h->jumlah_soal)
            <span class="rchip green"><i class="bi bi-check2"></i> {{ $benar }} benar</span>
            <span class="rchip red"><i class="bi bi-x-lg"></i> {{ $salah }} salah</span>
            @endif
            @if($tampil)
            <span class="rchip blue"><i class="bi bi-bullseye"></i> KKM {{ $h->ujian->kkm ?? 75 }}</span>
            @endif
        </div>
    </div>

    {{-- Status --}}
    <div class="d-flex flex-column align-items-end gap-2 flex-shrink-0">
        @if($h->status === 'selesai')
            <span class="status-badge badge-selesai"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>
        @elseif($h->status === 'belum_dinilai')
            <span class="status-badge badge-belum-final"><i class="bi bi-hourglass-split me-1"></i>Belum Final</span>
        @else
            <span class="status-badge badge-lain">{{ $h->status }}</span>
        @endif

        @if($belumDinilai)
        <span class="status-badge" style="background:#fef9c3;color:#92400e;">
            <i class="bi bi-hourglass-split me-1"></i>Proses
        </span>
        @elseif($tampil)
        <span class="status-badge {{ $lulus ? 'badge-selesai' : '' }}" style="{{ !$lulus ? 'background:#fee2e2;color:#dc2626;' : '' }}">
            {{ $lulus ? 'Lulus' : 'Tidak Lulus' }}
        </span>
        @endif
    </div>
</div>
@empty
<div class="empty-state">
    <div class="empty-icon"><i class="bi bi-clock-history"></i></div>
    <div class="fw-semibold text-muted">Belum ada riwayat ujian</div>
    <div class="text-muted small mt-1">Riwayat akan muncul setelah kamu menyelesaikan ujian</div>
</div>
@endforelse

@if($hasil->hasPages())
<div class="mt-3 d-flex justify-content-center">
    {{ $hasil->links() }}
</div>
@endif
@endsection

