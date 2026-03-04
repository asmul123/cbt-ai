@extends('layouts.app')
@section('title', 'Edit Ujian')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Edit Ujian</h6></div>
            <div class="card-body">
                <form action="{{ route('guru.ujian.update', $ujian) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Ujian</label>
                            <input type="text" name="nama_ujian" class="form-control @error('nama_ujian') is-invalid @enderror" value="{{ old('nama_ujian', $ujian->nama_ujian) }}" required>
                            @error('nama_ujian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mata Pelajaran</label>
                            <select name="mapel_id" class="form-select" required>
                                @foreach($mapel as $m)
                                    <option value="{{ $m->id }}" {{ old('mapel_id', $ujian->mapel_id) == $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Durasi (menit)</label>
                            <input type="number" name="durasi" class="form-control" value="{{ old('durasi', $ujian->durasi) }}" min="10" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="datetime-local" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', $ujian->tanggal_mulai ? \Carbon\Carbon::parse($ujian->tanggal_mulai)->format('Y-m-d\TH:i') : '') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="datetime-local" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai', $ujian->tanggal_selesai ? \Carbon\Carbon::parse($ujian->tanggal_selesai)->format('Y-m-d\TH:i') : '') }}" required>
                        </div>
                    </div>

                    @php $kelasIds = old('kelas_ids', $ujian->kelas->pluck('id')->toArray()); @endphp
                    <div class="mb-3">
                        <label class="form-label">Kelas Peserta</label>
                        <div class="row">
                            @foreach($kelas as $k)
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="kelas_ids[]" value="{{ $k->id }}" id="kelas{{ $k->id }}"
                                        {{ in_array($k->id, $kelasIds) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="kelas{{ $k->id }}">{{ $k->nama }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">KKM <span class="text-danger">*</span></label>
                            <input type="number" name="kkm" class="form-control @error('kkm') is-invalid @enderror" value="{{ old('kkm', $ujian->kkm ?? 75) }}" min="0" max="100" step="0.1" required>
                            @error('kkm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jumlah Soal Tampil</label>
                            <input type="number" name="jumlah_soal_tampil" class="form-control" value="{{ old('jumlah_soal_tampil', $ujian->jumlah_soal_tampil) }}" min="1" placeholder="Kosongkan = semua">
                            <small class="text-muted">Kosongkan jika semua soal ditampilkan</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="acak_soal" value="1" {{ old('acak_soal', $ujian->acak_soal) ? 'checked' : '' }}>
                                <label class="form-check-label">Acak Urutan Soal</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="acak_opsi" value="1" {{ old('acak_opsi', $ujian->acak_opsi) ? 'checked' : '' }}>
                                <label class="form-check-label">Acak Urutan Opsi</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="fullscreen_mode" value="1" {{ old('fullscreen_mode', $ujian->fullscreen_mode) ? 'checked' : '' }}>
                                <label class="form-check-label">Mode Fullscreen</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="tampilkan_nilai" value="1" {{ old('tampilkan_nilai', $ujian->tampilkan_nilai) ? 'checked' : '' }}>
                                <label class="form-check-label">Tampilkan Nilai</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan/Petunjuk</label>
                        <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $ujian->keterangan) }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update</button>
                        <a href="{{ route('guru.ujian.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
