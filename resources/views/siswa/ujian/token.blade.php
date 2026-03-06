@extends('layouts.app')
@section('title', 'Masuk Ujian')

@push('styles')
<style>
    .token-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        overflow: hidden;
    }
    .token-card .token-header {
        background: linear-gradient(135deg, #1e3a5f, #2c5282);
        color: white;
        padding: 28px 24px;
        text-align: center;
    }
    .token-input {
        letter-spacing: 10px;
        font-size: 1.8rem;
        font-weight: 700;
        text-align: center;
        text-transform: uppercase;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px;
        font-family: monospace;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .token-input:focus {
        border-color: #2c5282;
        box-shadow: 0 0 0 0.2rem rgba(44, 82, 130, 0.2);
        background: #fff;
    }
    .btn-verify {
        background: linear-gradient(135deg, #1e3a5f, #2c5282);
        border: none;
        border-radius: 10px;
        padding: 13px;
        font-weight: 600;
        font-size: 1rem;
        letter-spacing: 0.5px;
        transition: opacity 0.2s;
    }
    .btn-verify:hover { opacity: 0.9; }

    /* Confirm Modal */
    .confirm-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,0.45);
        backdrop-filter: blur(4px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .confirm-overlay.show { display: flex; animation: fadeIn 0.18s ease; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .confirm-box {
        background: #fff;
        border-radius: 18px;
        padding: 28px 24px 22px;
        max-width: 340px;
        width: 100%;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0,0,0,0.18);
        animation: slideUp 0.2s ease;
    }
    @keyframes slideUp { from { transform: translateY(16px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .confirm-icon {
        width: 52px; height: 52px;
        background: #eff6ff;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #2563eb;
        margin-bottom: 14px;
    }
    .confirm-title { font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
    .confirm-desc { font-size: 0.82rem; color: #64748b; margin-bottom: 22px; line-height: 1.6; }
    .confirm-token-preview {
        display: inline-block;
        background: #f1f5f9;
        border-radius: 8px;
        padding: 6px 18px;
        font-family: monospace;
        font-size: 1.2rem;
        font-weight: 700;
        letter-spacing: 6px;
        color: #1e3a5f;
        margin-bottom: 20px;
    }
    .confirm-actions { display: flex; gap: 10px; }
    .btn-cancel-confirm {
        flex: 1;
        background: #f1f5f9;
        color: #64748b;
        border: none;
        border-radius: 10px;
        padding: 11px;
        font-weight: 600;
        font-size: 0.88rem;
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-cancel-confirm:hover { background: #e2e8f0; }
    .btn-ok-confirm {
        flex: 1;
        background: linear-gradient(135deg, #1e3a5f, #2c5282);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 11px;
        font-weight: 600;
        font-size: 0.88rem;
        cursor: pointer;
        transition: opacity 0.15s;
    }
    .btn-ok-confirm:hover { opacity: 0.88; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="token-card card">
            <div class="token-header">
                <div style="font-size: 2.8rem; margin-bottom: 8px;">🔑</div>
                <h5 class="mb-1 fw-bold">Masukkan Token Ujian</h5>
                <small class="opacity-75">Masukkan token yang diberikan oleh proktor</small>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('siswa.ujian.verifikasiToken') }}" method="POST" id="tokenForm">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted small text-uppercase">Token Ujian</label>
                        <input type="text"
                               name="token"
                               id="tokenInput"
                               class="form-control token-input @error('token') is-invalid @enderror"
                               value="{{ old('token') }}"
                               placeholder="— — — —"
                               maxlength="10"
                               required
                               autofocus
                               autocomplete="off"
                               oninput="this.value = this.value.toUpperCase()">
                        @error('token')
                            <div class="invalid-feedback text-center">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="button" class="btn btn-primary btn-verify w-100 text-white" onclick="showConfirm()">
                        <i class="bi bi-arrow-right-circle me-2"></i>Verifikasi Token
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="{{ route('siswa.ujian.index') }}" class="small text-decoration-none" style="color: #ec4899;">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Ujian
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Confirm Modal --}}
<div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-box">
        <div class="confirm-icon"><i class="bi bi-key-fill"></i></div>
        <div class="confirm-title">Konfirmasi Token</div>
        <div class="confirm-desc">Apakah Anda yakin ingin masuk dengan token berikut?</div>
        <div class="confirm-token-preview" id="previewToken">—</div>
        <div class="confirm-actions">
            <button class="btn-cancel-confirm" onclick="hideConfirm()">
                <i class="bi bi-x-lg me-1"></i>Batal
            </button>
            <button class="btn-ok-confirm" onclick="doSubmit()">
                <i class="bi bi-check2 me-1"></i>Ya, Masuk!
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showConfirm() {
        const val = document.getElementById('tokenInput').value.trim();
        if (!val) {
            document.getElementById('tokenInput').focus();
            return;
        }
        document.getElementById('previewToken').textContent = val.toUpperCase();
        document.getElementById('confirmOverlay').classList.add('show');
    }
    function hideConfirm() {
        document.getElementById('confirmOverlay').classList.remove('show');
    }
    function doSubmit() {
        document.getElementById('tokenForm').submit();
    }
    // Tutup overlay kalau klik di luar box
    document.getElementById('confirmOverlay').addEventListener('click', function(e) {
        if (e.target === this) hideConfirm();
    });
</script>
@endpush
@endsection
