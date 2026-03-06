@extends('layouts.app')
@section('title', 'Profil & Ubah Kata Sandi')

@section('content')
<div class="row justify-content-center g-4">
    {{-- Informasi Profil --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-person-circle"></i> Informasi Profil</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="{{ $user->username }}" disabled>
                        <small class="text-muted">Username tidak dapat diubah.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control text-capitalize" value="{{ $user->getRoleNames()->first() ?? '-' }}" disabled>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Profil</button>

                    @if(session('status') === 'profile-updated')
                        <span class="text-success ms-2"><i class="bi bi-check-circle"></i> Profil berhasil diperbarui.</span>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- Ubah Kata Sandi --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-shield-lock"></i> Ubah Kata Sandi</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('password.update') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password"
                               class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif"
                               autocomplete="current-password">
                        @if($errors->updatePassword->has('current_password'))
                            <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kata Sandi Baru</label>
                            <input type="password" name="password"
                                   class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif"
                                   autocomplete="new-password">
                            @if($errors->updatePassword->has('password'))
                                <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation"
                                   class="form-control" autocomplete="new-password">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-key"></i> Ubah Kata Sandi</button>

                    @if(session('status') === 'password-updated')
                        <span class="text-success ms-2"><i class="bi bi-check-circle"></i> Kata sandi berhasil diubah.</span>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
