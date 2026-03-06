@extends('layouts.app')
@section('title', 'Konfirmasi Submit')

@push('styles')
<style>
    .submit-card {
        border: none;
        border-radius: 18px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.09);
        overflow: hidden;
    }
    .submit-header {
        background: linear-gradient(135deg, #92400e, #d97706);
        color: white;
        padding: 22px 28px;
        text-align: center;
    }
    .submit-header h5 { font-size: 1.05rem; font-weight: 700; margin: 0; }
    .stat-box {
        border-radius: 12px;
        padding: 18px 12px;
        text-align: center;
    }
    .stat-box .stat-num { font-size: 2rem; font-weight: 800; line-height: 1; }
    .stat-box .stat-lbl { font-size: 0.75rem; color: #64748b; margin-top: 4px; }
    .stat-green { background: #f0fdf4; }
    .stat-red   { background: #fff1f2; }
    .stat-amber { background: #fffbeb; }
    .alert-soal {
        border-radius: 10px;
        font-size: 0.85rem;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 8px;
    }
    .alert-soal.danger { background: #fff1f2; color: #b91c1c; border: 1px solid #fecaca; }
    .alert-soal.warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
    .btn-submit-back {
        background: #f1f5f9; color: #475569; border: none;
        border-radius: 12px; padding: 13px 24px;
        font-weight: 600; font-size: 0.92rem;
        text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        transition: background 0.15s;
    }
    .btn-submit-back:hover { background: #e2e8f0; color: #334155; }
    .btn-submit-ok {
        background: linear-gradient(135deg, #1e3a5f, #2c5282);
        color: white; border: none;
        border-radius: 12px; padding: 13px 28px;
        font-weight: 700; font-size: 0.92rem;
        cursor: pointer; display: inline-flex; align-items: center; gap: 8px;
        transition: opacity 0.2s;
    }
    .btn-submit-ok:hover { opacity: 0.88; }

    /* Custom Confirm Modal */
    .confirm-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(15,23,42,0.5);
        backdrop-filter: blur(5px);
        z-index: 9999; align-items: center; justify-content: center; padding: 20px;
    }
    .confirm-overlay.show { display: flex; animation: cfFade 0.18s ease; }
    @keyframes cfFade { from { opacity: 0; } to { opacity: 1; } }
    .confirm-box {
        background: #fff; border-radius: 20px;
        padding: 30px 26px 24px; max-width: 360px; width: 100%;
        text-align: center;
        box-shadow: 0 24px 64px rgba(0,0,0,0.18);
        animation: cfSlide 0.2s ease;
    }
    @keyframes cfSlide {
        from { transform: translateY(18px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }
    .cf-icon-wrap {
        width: 60px; height: 60px;
        background: #fff7ed; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.8rem; color: #d97706; margin-bottom: 16px;
    }
    .cf-title { font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
    .cf-desc  { font-size: 0.82rem; color: #64748b; line-height: 1.65; margin-bottom: 24px; }
    .cf-actions { display: flex; gap: 10px; }
    .cf-btn-cancel {
        flex: 1; background: #f1f5f9; color: #64748b; border: none;
        border-radius: 10px; padding: 12px; font-weight: 600; font-size: 0.88rem;
        cursor: pointer; transition: background 0.15s;
    }
    .cf-btn-cancel:hover { background: #e2e8f0; }
    .cf-btn-ok {
        flex: 1; background: linear-gradient(135deg, #1e3a5f, #2c5282);
        color: #fff; border: none; border-radius: 10px; padding: 12px;
        font-weight: 700; font-size: 0.88rem; cursor: pointer; transition: opacity 0.15s;
    }
    .cf-btn-ok:hover { opacity: 0.88; }

    /* Sembunyikan sidebar & topbar di halaman ini */
    #sidebar, #sidebarBackdrop, .topbar { display: none !important; }
    .main-content { margin-left: 0 !important; padding: 32px 16px !important; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="submit-card">
            <div class="submit-header">
                <div style="font-size:1.8rem;margin-bottom:8px;">📋</div>
                <h5><i class="bi bi-send-fill me-2"></i>Konfirmasi Pengumpulan</h5>
                <div style="font-size:0.78rem;opacity:0.8;margin-top:4px;">{{ $ujian->nama }}</div>
            </div>

            <div class="card-body p-4">
                {{-- Stat boxes --}}
                <div class="row g-2 mb-4">
                    <div class="col-4">
                        <div class="stat-box stat-green">
                            <div class="stat-num text-success">{{ $terjawab }}</div>
                            <div class="stat-lbl">Terjawab</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-box stat-red">
                            <div class="stat-num text-danger">{{ $belumDijawab }}</div>
                            <div class="stat-lbl">Belum Dijawab</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-box stat-amber">
                            <div class="stat-num text-warning">{{ $raguRagu }}</div>
                            <div class="stat-lbl">Ragu-ragu</div>
                        </div>
                    </div>
                </div>

                {{-- Progress bar --}}
                <div class="mb-1 d-flex justify-content-between" style="font-size:0.75rem;color:#64748b;">
                    <span>Progress Pengerjaan</span>
                    <span>{{ $terjawab }}/{{ $totalSoal }} soal</span>
                </div>
                <div class="progress mb-4" style="height:10px;border-radius:8px;background:#f1f5f9;">
                    <div class="progress-bar bg-success" style="width:{{ ($terjawab / max($totalSoal,1)) * 100 }}%;border-radius:8px;"></div>
                </div>

                {{-- Alerts --}}
                @if($belumDijawab > 0)
                <div class="alert-soal danger">
                    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                    Masih ada <strong>{{ $belumDijawab }} soal</strong> yang belum dijawab!
                </div>
                @endif
                @if($raguRagu > 0)
                <div class="alert-soal warning">
                    <i class="bi bi-flag-fill flex-shrink-0"></i>
                    Ada <strong>{{ $raguRagu }} soal</strong> yang ditandai ragu-ragu.
                </div>
                @endif

                {{-- Actions --}}
                <div class="d-flex gap-2 justify-content-center mt-4">
                    <a href="{{ route('siswa.ujian.kerjakan', [$ujian, 1]) }}" class="btn-submit-back">
                        <i class="bi bi-arrow-left"></i> Kembali ke Soal
                    </a>
                    <form action="{{ route('siswa.ujian.submit', $ujian) }}" method="POST" id="submitForm">
                        @csrf
                        <button type="button" class="btn-submit-ok" onclick="showSubmitConfirm()">
                            <i class="bi bi-send-fill"></i> Kumpulkan Ujian
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Custom Confirm Modal --}}
<div class="confirm-overlay" id="submitConfirmOverlay">
    <div class="confirm-box">
        <div class="cf-icon-wrap"><i class="bi bi-send-fill"></i></div>
        <div class="cf-title">Kumpulkan Ujian?</div>
        <div class="cf-desc">
            Tindakan ini <strong>tidak bisa dibatalkan</strong>.<br>
            @if($belumDijawab > 0)
            Masih ada <strong class="text-danger">{{ $belumDijawab }} soal</strong> yang belum dijawab.<br>
            @endif
            Yakin ingin mengumpulkan sekarang?
        </div>
        <div class="cf-actions">
            <button class="cf-btn-cancel" onclick="hideSubmitConfirm()">
                <i class="bi bi-arrow-left me-1"></i>Belum
            </button>
            <button class="cf-btn-ok" onclick="document.getElementById('submitForm').submit()">
                <i class="bi bi-send-fill me-1"></i>Ya, Kumpulkan!
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showSubmitConfirm() {
        document.getElementById('submitConfirmOverlay').classList.add('show');
    }
    function hideSubmitConfirm() {
        document.getElementById('submitConfirmOverlay').classList.remove('show');
    }
    document.getElementById('submitConfirmOverlay').addEventListener('click', function(e) {
        if (e.target === this) hideSubmitConfirm();
    });
</script>
@endpush
@endsection

