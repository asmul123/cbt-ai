@extends('layouts.app')
@section('title', 'Konfirmasi Ujian')

@push('styles')
<style>
    .konfirmasi-card {
        border: none;
        border-radius: 18px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.09);
        overflow: hidden;
    }
    .konfirmasi-header {
        background: linear-gradient(135deg, #1e3a5f, #2c5282);
        color: white;
        padding: 24px 28px;
    }
    .konfirmasi-header h5 { font-size: 1.1rem; font-weight: 700; margin: 0; }
    .info-row {
        display: flex;
        padding: 11px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { width: 140px; flex-shrink: 0; color: #64748b; font-weight: 500; }
    .info-value { color: #1e293b; font-weight: 600; }
    .perhatian-box {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 12px;
        padding: 16px 18px;
        margin-top: 20px;
    }
    .perhatian-box .perhatian-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: #92400e;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .perhatian-box ul {
        margin: 0;
        padding-left: 18px;
        font-size: 0.82rem;
        color: #78350f;
        line-height: 1.9;
    }
    .btn-mulai {
        background: linear-gradient(135deg, #1e3a5f, #2c5282);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 13px 32px;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        transition: opacity 0.2s, transform 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-mulai:hover { opacity: 0.9; transform: translateY(-1px); color: white; }
    .btn-batal {
        background: #f1f5f9;
        color: #64748b;
        border: none;
        border-radius: 12px;
        padding: 13px 28px;
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.15s;
    }
    .btn-batal:hover { background: #e2e8f0; color: #475569; }

    /* Custom Confirm Modal */
    .confirm-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,0.5);
        backdrop-filter: blur(5px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .confirm-overlay.show { display: flex; animation: cfFadeIn 0.18s ease; }
    @keyframes cfFadeIn { from { opacity: 0; } to { opacity: 1; } }
    .confirm-box {
        background: #fff;
        border-radius: 20px;
        padding: 30px 26px 24px;
        max-width: 360px;
        width: 100%;
        text-align: center;
        box-shadow: 0 24px 64px rgba(0,0,0,0.18);
        animation: cfSlideUp 0.2s ease;
    }
    @keyframes cfSlideUp {
        from { transform: translateY(20px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }
    .confirm-icon-wrap {
        width: 58px; height: 58px;
        background: #eff6ff;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        color: #2563eb;
        margin-bottom: 16px;
    }
    .confirm-title { font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
    .confirm-desc  { font-size: 0.83rem; color: #64748b; line-height: 1.6; margin-bottom: 24px; }
    .confirm-actions { display: flex; gap: 10px; }
    .cf-btn-cancel {
        flex: 1;
        background: #f1f5f9;
        color: #64748b;
        border: none;
        border-radius: 10px;
        padding: 12px;
        font-weight: 600;
        font-size: 0.88rem;
        cursor: pointer;
        transition: background 0.15s;
    }
    .cf-btn-cancel:hover { background: #e2e8f0; }
    .cf-btn-ok {
        flex: 1;
        background: linear-gradient(135deg, #1e3a5f, #2c5282);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 12px;
        font-weight: 700;
        font-size: 0.88rem;
        cursor: pointer;
        transition: opacity 0.15s;
    }
    .cf-btn-ok:hover { opacity: 0.88; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="konfirmasi-card">
            <div class="konfirmasi-header">
                <h5><i class="bi bi-clipboard-check me-2"></i>Konfirmasi Ujian</h5>
                <div style="font-size:0.78rem;opacity:0.75;margin-top:4px;">Periksa detail ujian sebelum memulai</div>
            </div>
            <div class="card-body p-4">
                {{-- Info Ujian --}}
                <div class="mb-4">
                    <div class="info-row">
                        <span class="info-label">Nama Ujian</span>
                        <span class="info-value">{{ $ujian->nama }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Mata Pelajaran</span>
                        <span class="info-value">{{ $ujian->mapel->nama ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Durasi</span>
                        <span class="info-value"><i class="bi bi-clock text-muted me-1"></i>{{ $ujian->durasi }} menit</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Jumlah Soal</span>
                        <span class="info-value"><i class="bi bi-list-check text-muted me-1"></i>{{ $ujian->soal_count }} soal</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Nama Peserta</span>
                        <span class="info-value">{{ $siswa->user->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">NIS</span>
                        <span class="info-value">{{ $siswa->nis }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Kelas</span>
                        <span class="info-value">{{ $siswa->kelas->nama ?? '-' }}</span>
                    </div>
                </div>

                @if($ujian->deskripsi)
                <div class="alert alert-info rounded-3 mb-3" style="font-size:0.85rem;">
                    <div class="fw-bold mb-1"><i class="bi bi-info-circle me-1"></i>Petunjuk Ujian</div>
                    {!! nl2br(e($ujian->deskripsi)) !!}
                </div>
                @endif

                <div class="perhatian-box">
                    <div class="perhatian-title"><i class="bi bi-exclamation-triangle-fill"></i> Perhatian!</div>
                    <ul>
                        <li>Pastikan koneksi internet stabil</li>
                        <li>Jangan pindah tab/window selama ujian</li>
                        <li>Jangan menekan tombol back browser</li>
                        <li>Waktu akan berjalan otomatis setelah ujian dimulai</li>
                        <li>Ujian akan otomatis dikumpulkan saat waktu habis</li>
                    </ul>
                </div>

                <div class="d-flex gap-2 justify-content-center mt-4">
                    <form action="{{ route('siswa.ujian.mulai', $ujian) }}" method="POST" id="mulaiForm">
                        @csrf
                        <button type="button" class="btn-mulai" onclick="showMulaiConfirm()">
                            <i class="bi bi-play-fill"></i> Mulai Ujian
                        </button>
                    </form>
                    <a href="{{ route('siswa.dashboard') }}" class="btn-batal">
                        <i class="bi bi-x-lg"></i> Batal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Custom Confirm Modal --}}
<div class="confirm-overlay" id="mulaiConfirmOverlay">
    <div class="confirm-box">
        <div class="confirm-icon-wrap"><i class="bi bi-play-circle-fill"></i></div>
        <div class="confirm-title">Mulai Ujian Sekarang?</div>
        <div class="confirm-desc">
            Waktu <strong>{{ $ujian->durasi }} menit</strong> akan langsung berjalan setelah dikonfirmasi.<br>
            Pastikan kamu sudah siap!
        </div>
        <div class="confirm-actions">
            <button class="cf-btn-cancel" onclick="hideMulaiConfirm()">
                <i class="bi bi-x-lg me-1"></i>Belum Siap
            </button>
            <button class="cf-btn-ok" onclick="document.getElementById('mulaiForm').submit()">
                <i class="bi bi-play-fill me-1"></i>Siap, Mulai!
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showMulaiConfirm() {
        document.getElementById('mulaiConfirmOverlay').classList.add('show');
    }
    function hideMulaiConfirm() {
        document.getElementById('mulaiConfirmOverlay').classList.remove('show');
    }
    document.getElementById('mulaiConfirmOverlay').addEventListener('click', function(e) {
        if (e.target === this) hideMulaiConfirm();
    });
</script>
@endpush
@endsection

