@extends('layouts.app')
@section('title', 'Edit Proktor')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Edit Proktor</h6></div>
            <div class="card-body">
                <form action="{{ route('admin.proktor.update', $proktor) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $proktor->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $proktor->username) }}" required>
                        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <small class="text-muted">(kosongkan jika tidak diubah)</small></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ruang Ujian</label>
                        <select name="ruang_ujian_id" class="form-select @error('ruang_ujian_id') is-invalid @enderror">
                            <option value="">-- Belum ditentukan --</option>
                            @foreach($ruang as $r)
                                <option value="{{ $r->id }}" {{ old('ruang_ujian_id', $proktor->ruang_ujian_id) == $r->id ? 'selected' : '' }}>{{ $r->kode }} - {{ $r->nama }}</option>
                            @endforeach
                        </select>
                        @error('ruang_ujian_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update</button>
                        <a href="{{ route('admin.proktor.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
