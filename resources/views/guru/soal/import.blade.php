@extends('layouts.app')
@section('title', 'Import Soal')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-file-earmark-excel"></i> Import Soal dari Excel</h6></div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Format File Excel</h6>
                    <p class="mb-1">File Excel harus memiliki kolom berikut:</p>
                    <code>soal | tipe | opsi_a | opsi_b | opsi_c | opsi_d | opsi_e | jawaban_benar | tingkat_kesulitan | kd | bobot</code>
                    <hr>
                    <small>Tipe: pg, pg_kompleks, isian, essay | Tingkat: mudah, sedang, sulit</small>
                </div>

                <form action="{{ route('guru.soal.importProcess') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Mata Pelajaran</label>
                        <select name="mapel_id" class="form-select @error('mapel_id') is-invalid @enderror" required>
                            <option value="">Pilih Mapel</option>
                            @foreach($mapel as $m)
                                <option value="{{ $m->id }}" {{ old('mapel_id') == $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                            @endforeach
                        </select>
                        @error('mapel_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File Excel (.xlsx)</label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls" required>
                        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success"><i class="bi bi-upload"></i> Import</button>
                        <a href="{{ route('guru.soal.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
