@extends('layouts.app')
@section('title', 'Dashboard')

@push('styles')
<style>
    .welcome-banner {
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #7c3aed 100%);
        border-radius: 16px;
        color: white;
        padding: 28px 32px;
        position: relative;
        overflow: hidden;
        margin-bottom: 24px;
    }
    .welcome-banner::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 200px; height: 200px;
        background: rgba(255,255,255,0.07);
        border-radius: 50%;
    }
    .welcome-banner::after {
        content: '';
        position: absolute;
        bottom: -60px; right: 80px;
        width: 150px; height: 150px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }
    .welcome-banner .avatar {
        width: 52px; height: 52px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    /* Stat Cards */
    .dash-stat {
        border-radius: 14px;
        border: none;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        transition: transform 0.2s, box-shadow 0.2s;
        text-decoration: none;
        color: inherit;
    }
    .dash-stat:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.12); color: inherit; }
    .dash-stat .stat-icon {
        width: 52px; height: 52px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
    .dash-stat .stat-value { font-size: 1.8rem; font-weight: 700; line-height: 1; }
    .dash-stat .stat-label { font-size: 0.8rem; color: #64748b; margin-top: 2px; }
    @media (max-width: 576px) {
        .dash-stat { flex-direction: column; align-items: center; text-align: center; padding: 14px 8px; gap: 8px; }
        .dash-stat .stat-icon { width: 36px; height: 36px; font-size: 1rem; flex-shrink: 0; }
        .dash-stat .stat-value { font-size: 1.4rem; }
        .dash-stat .stat-label { font-size: 0.7rem; white-space: normal; line-height: 1.3; }
    }
    /* Ujian Cards */
    .ujian-card {
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        background: white;
        transition: border-color 0.2s, box-shadow 0.2s;
        margin-bottom: 10px;
    }
    .ujian-card:hover { border-color: #3b82f6; box-shadow: 0 4px 16px rgba(59,130,246,0.1); }
    .ujian-card .mapel-badge {
        font-size: 0.72rem;
        padding: 3px 10px;
        border-radius: 20px;
        background: #eff6ff;
        color: #2563eb;
        font-weight: 600;
    }
    .ujian-card .info-chip {
        font-size: 0.75rem;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    /* Riwayat */
    .riwayat-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .riwayat-item:last-child { border-bottom: none; }
    .riwayat-item .nilai-badge {
        min-width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .nilai-lulus { background: #dcfce7; color: #16a34a; }
    .nilai-tidak-lulus { background: #fee2e2; color: #dc2626; }
    .nilai-hidden { background: #f1f5f9; color: #94a3b8; }
    .section-card {
        background: white;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border: none;
    }
    .section-card .section-header {
        padding: 16px 20px 12px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
</style>
@endpush

@section('content')
@php
    $nilaiVisible = $riwayat->filter(fn($h) => $h->ujian && $h->ujian->tampilkan_nilai);
    $rataRata = $nilaiVisible->count() > 0 ? number_format($nilaiVisible->avg('nilai_akhir'), 1) : '-';
    $selesai = $riwayat->count();
    $tersedia = $ujianTersedia->count();
    $jam = now()->hour;
    $sapa = $jam < 11 ? 'Selamat Pagi' : ($jam < 15 ? 'Selamat Siang' : ($jam < 18 ? 'Selamat Sore' : 'Selamat Malam'));
@endphp

{{-- Welcome Banner --}}
<div class="welcome-banner">
    <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1">
        <div class="avatar"><i class="bi bi-person-fill"></i></div>
        <div>
            <div style="font-size:0.85rem;opacity:0.8;">{{ $sapa }},</div>
            <div style="font-size:1.3rem;font-weight:700;">{{ $siswa->user->name ?? auth()->user()->name }}</div>
            <div style="font-size:0.8rem;opacity:0.7;margin-top:2px;">
                {{ $siswa->kelas->nama ?? '-' }} &nbsp;·&nbsp; NIS {{ $siswa->nis }}
            </div>
        </div>
    </div>
    @if($tersedia > 0)
    <div class="mt-3" style="position:relative;z-index:1;">
        <span style="background:rgba(255,255,255,0.2);padding:5px 14px;border-radius:20px;font-size:0.8rem;">
            <i class="bi bi-bell-fill me-1"></i> {{ $tersedia }} ujian sedang menunggumu!
        </span>
    </div>
    @endif
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-4">
        <a href="{{ route('siswa.ujian.index') }}" class="dash-stat" style="background:#eff6ff;">
            <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="bi bi-journal-check"></i></div>
            <div>
                <div class="stat-value" style="color:#2563eb;">{{ $tersedia }}</div>
                <div class="stat-label">Ujian Tersedia</div>
            </div>
        </a>
    </div>
    <div class="col-4">
        <div class="dash-stat" style="background:#f0fdf4;">
            <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-patch-check-fill"></i></div>
            <div>
                <div class="stat-value" style="color:#16a34a;">{{ $selesai }}</div>
                <div class="stat-label">Ujian Selesai</div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="dash-stat" style="background:#fefce8;">
            <div class="stat-icon" style="background:#fef9c3;color:#ca8a04;"><i class="bi bi-star-fill"></i></div>
            <div>
                <div class="stat-value" style="color:#ca8a04;">{{ $rataRata }}</div>
                <div class="stat-label">Nilai Rata-rata</div>
            </div>
        </div>
    </div>
</div>

{{-- Main Content --}}
<div class="row g-3">
    {{-- Ujian Tersedia --}}
    <div class="col-md-7">
        <div class="section-card">
            <div class="section-header">
                <div class="fw-semibold d-flex align-items-center gap-2">
                    <span style="width:8px;height:8px;background:#3b82f6;border-radius:50%;display:inline-block;"></span>
                    Ujian Tersedia
                </div>
                <a href="{{ route('siswa.ujian.index') }}" class="text-primary small text-decoration-none">Lihat semua <i class="bi bi-chevron-right"></i></a>
            </div>
            <div class="p-3">
                @forelse($ujianTersedia as $u)
                <div class="ujian-card">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <span class="mapel-badge">{{ $u->mapel->nama ?? '-' }}</span>
                                @if($u->status === 'berlangsung')
                                    <span class="badge" style="background:#fee2e2;color:#dc2626;font-size:0.68rem;border-radius:20px;">
                                        <span style="display:inline-block;width:6px;height:6px;background:#dc2626;border-radius:50%;margin-right:3px;"></span>Live
                                    </span>
                                @endif
                            </div>
                            <div class="fw-semibold mb-1" style="font-size:0.95rem;">{{ $u->nama }}</div>
                            <div class="d-flex gap-3 flex-wrap">
                                <span class="info-chip"><i class="bi bi-clock"></i> {{ $u->durasi }} menit</span>
                                <span class="info-chip"><i class="bi bi-file-earmark-text"></i> {{ $u->soal_count ?? '?' }} soal</span>
                                <span class="info-chip"><i class="bi bi-calendar3"></i> s/d {{ \Carbon\Carbon::parse($u->tanggal_selesai)->format('d M') }}</span>
                            </div>
                        </div>
                        <a href="{{ route('siswa.ujian.token') }}"
                           class="btn btn-sm flex-shrink-0"
                           style="background:linear-gradient(135deg,#2563eb,#7c3aed);color:white;border:none;border-radius:8px;padding:7px 16px;font-size:0.8rem;white-space:nowrap;">
                            <i class="bi bi-play-fill"></i> Mulai
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <div style="width:64px;height:64px;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:1.8rem;color:#94a3b8;">
                        <i class="bi bi-clipboard-x"></i>
                    </div>
                    <div class="fw-semibold text-muted">Tidak ada ujian tersedia</div>
                    <div class="small text-muted mt-1">Cek kembali nanti ya!</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Riwayat --}}
    <div class="col-md-5">
        <div class="section-card h-100">
            <div class="section-header">
                <div class="fw-semibold d-flex align-items-center gap-2">
                    <span style="width:8px;height:8px;background:#10b981;border-radius:50%;display:inline-block;"></span>
                    Riwayat Ujian
                </div>
                <a href="{{ route('siswa.riwayat') }}" class="text-primary small text-decoration-none">Semua <i class="bi bi-chevron-right"></i></a>
            </div>
            <div class="px-3 py-2">
                @forelse($riwayat->take(5) as $h)
                @php
                    $tampil = $h->ujian && $h->ujian->tampilkan_nilai;
                    $lulus  = $h->nilai_akhir >= ($h->ujian->kkm ?? 75);
                    $nilaiClass = !$tampil ? 'nilai-hidden' : ($lulus ? 'nilai-lulus' : 'nilai-tidak-lulus');
                    $nilaiText  = !$tampil ? '?' : number_format($h->nilai_akhir, 0);
                @endphp
                <div class="riwayat-item">
                    <div class="nilai-badge {{ $nilaiClass }}">{{ $nilaiText }}</div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-semibold text-truncate" style="font-size:0.88rem;">{{ $h->ujian->nama ?? '-' }}</div>
                        <div class="d-flex gap-2 mt-1 flex-wrap">
                            <span class="info-chip" style="font-size:0.72rem;color:#94a3b8;">
                                <i class="bi bi-book"></i> {{ $h->ujian->mapel->nama ?? '-' }}
                            </span>
                            @if($tampil)
                            <span class="info-chip" style="font-size:0.72rem;{{ $lulus ? 'color:#16a34a;' : 'color:#dc2626;' }}">
                                <i class="bi bi-{{ $lulus ? 'check-circle-fill' : 'x-circle-fill' }}"></i>
                                {{ $lulus ? 'Lulus' : 'Tidak Lulus' }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <div style="width:56px;height:56px;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;font-size:1.5rem;color:#94a3b8;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="text-muted small">Belum ada riwayat ujian</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Modal Informasi Sebelum Ujian --}}
@php $isPerempuan = $siswa->jenis_kelamin === 'P'; @endphp
<div class="modal fade" id="modalInfoUjian" tabindex="-1" aria-labelledby="modalInfoLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content info-modal-content">
            <div class="info-modal-header">
                <div class="info-modal-icon-wrap">
                    <i class="bi bi-info-circle-fill"></i>
                </div>
                <h5 class="info-modal-title" id="modalInfoLabel">Informasi Penting</h5>
                <p class="info-modal-subtitle">Baca sebelum memulai ujian</p>
            </div>
            <div class="modal-body info-modal-body">
                <div class="info-section">
                    <div class="info-section-icon">
                        <i class="bi bi-laptop"></i>
                    </div>
                    <div>
                        <div class="info-section-title">Perhatikan Saat Ujian</div>
                        <ul class="info-list">
                            <li>Dilarang pindah tab atau membuka aplikasi lain</li>
                            <li>Dilarang menggunakan split screen atau floating window</li>
                            <li>Tetap di halaman ujian hingga selesai</li>
                        </ul>
                    </div>
                </div>
                <div class="info-section">
                    <div class="info-section-icon">
                        <i class="bi bi-phone"></i>
                    </div>
                    <div>
                        <div class="info-section-title">Persiapkan Handphone</div>
                        <ul class="info-list">
                            <li>Aktifkan mode <strong>Do Not Disturb (DND)</strong></li>
                            <li>Atur layar agar tidak mati / screen timeout lebih lama</li>
                            <li>Aktifkan mode <strong>silent</strong> agar tidak terganggu</li>
                        </ul>
                    </div>
                </div>
                <div class="info-section info-section-doa">
                    <div class="info-section-icon">
                        <i class="bi {{ $isPerempuan ? 'bi-heart-fill' : 'bi-stars' }}"></i>
                    </div>
                    <div>
                        <div class="info-section-title">Yang Terpenting</div>
                        <p class="info-doa-text">Jangan lupa untuk <strong>berdoa</strong> sebelum mengerjakan ujian. Semoga hasilnya terbaik! 🙏</p>
                    </div>
                </div>
            </div>
            <div class="info-modal-footer">
                <button type="button" class="btn-info-ok" data-bs-dismiss="modal">
                    <i class="bi bi-check2-circle"></i> Siap, Mengerti!
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    :root {
        --info-color: {{ $isPerempuan ? '#ec4899' : '#2563eb' }};
        --info-color-light: {{ $isPerempuan ? '#fdf2f8' : '#eff6ff' }};
        --info-color-soft: {{ $isPerempuan ? '#fce7f3' : '#dbeafe' }};
        --info-color-dark: {{ $isPerempuan ? '#be185d' : '#1d4ed8' }};
    }
    .info-modal-content {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
    .info-modal-header {
        background: var(--info-color);
        padding: 28px 24px 20px;
        text-align: center;
        color: white;
    }
    .info-modal-icon-wrap {
        width: 52px; height: 52px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 10px;
        backdrop-filter: blur(8px);
    }
    .info-modal-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0 0 4px;
        color: white;
    }
    .info-modal-subtitle {
        font-size: 0.8rem;
        opacity: 0.85;
        margin: 0;
    }
    .info-modal-body {
        padding: 20px 22px 6px;
        background: #fff;
    }
    .info-section {
        display: flex;
        gap: 14px;
        padding: 14px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .info-section:last-child { border-bottom: none; }
    .info-section-icon {
        width: 36px; height: 36px;
        background: var(--info-color-soft);
        color: var(--info-color);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .info-section-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 6px;
    }
    .info-list {
        margin: 0;
        padding-left: 16px;
        font-size: 0.8rem;
        color: #475569;
        line-height: 1.8;
    }
    .info-section-doa .info-section-icon {
        background: {{ $isPerempuan ? '#fce7f3' : '#fef3c7' }};
        color: {{ $isPerempuan ? '#ec4899' : '#d97706' }};
    }
    .info-doa-text {
        font-size: 0.82rem;
        color: #475569;
        line-height: 1.6;
        margin: 0;
    }
    .info-modal-footer {
        padding: 16px 22px 22px;
        background: #fff;
        text-align: center;
    }
    .btn-info-ok {
        background: var(--info-color);
        color: white;
        border: none;
        padding: 11px 36px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.88rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        box-shadow: 0 4px 15px {{ $isPerempuan ? 'rgba(236,72,153,0.35)' : 'rgba(37,99,235,0.35)' }};
    }
    .btn-info-ok:hover {
        background: var(--info-color-dark);
        transform: translateY(-1px);
        box-shadow: 0 6px 20px {{ $isPerempuan ? 'rgba(236,72,153,0.45)' : 'rgba(37,99,235,0.45)' }};
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = new bootstrap.Modal(document.getElementById('modalInfoUjian'));
        modal.show();
    });
</script>
@endpush
@endsection
