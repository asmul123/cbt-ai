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
                <form action="{{ route('siswa.ujian.verifikasiToken') }}" method="POST">
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

                    <button type="submit" class="btn btn-primary btn-verify w-100 text-white">
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
@endsection
