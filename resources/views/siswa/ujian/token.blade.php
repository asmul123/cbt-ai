@extends('layouts.app')
@section('title', 'Masuk Ujian')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header text-center">
                <h5 class="mb-0"><i class="bi bi-key-fill"></i> Masukkan Token Ujian</h5>
            </div>
            <div class="card-body">
                <p class="text-muted text-center">Masukkan token yang diberikan oleh proktor/guru untuk memulai ujian.</p>

                <form action="{{ route('siswa.ujian.verifikasiToken') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Token Ujian</label>
                        <input type="text" name="token" class="form-control form-control-lg text-center font-monospace @error('token') is-invalid @enderror"
                               value="{{ old('token') }}" placeholder="XXXXXX" maxlength="10" required autofocus
                               style="letter-spacing: 8px; font-size: 1.5rem;">
                        @error('token')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="bi bi-arrow-right-circle"></i> Verifikasi Token
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
