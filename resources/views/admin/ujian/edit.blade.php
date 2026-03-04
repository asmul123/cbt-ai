@extends('layouts.app')
@section('title', 'Edit Ujian')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-11">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Ujian: {{ $ujian->nama_ujian }}</h6></div>
            <div class="card-body">
                <form action="{{ route('admin.ujian.update', $ujian) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Ujian <span class="text-danger">*</span></label>
                            <input type="text" name="nama_ujian" class="form-control @error('nama_ujian') is-invalid @enderror" value="{{ old('nama_ujian', $ujian->nama_ujian) }}" required>
                            @error('nama_ujian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="mapel_id" class="form-select @error('mapel_id') is-invalid @enderror" required>
                                <option value="">Pilih Mapel</option>
                                @foreach($mapel as $m)
                                    <option value="{{ $m->id }}" {{ old('mapel_id', $ujian->mapel_id) == $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                                @endforeach
                            </select>
                            @error('mapel_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Guru Pengampu</label>
                            <select name="guru_id" class="form-select">
                                <option value="">-- Opsional --</option>
                                @foreach($guru as $g)
                                    <option value="{{ $g->id }}" {{ old('guru_id', $ujian->guru_id) == $g->id ? 'selected' : '' }}>{{ $g->user->name ?? $g->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Durasi (menit) <span class="text-danger">*</span></label>
                            <input type="number" name="durasi" class="form-control @error('durasi') is-invalid @enderror" value="{{ old('durasi', $ujian->durasi) }}" min="10" required>
                            @error('durasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                value="{{ old('tanggal_mulai', $ujian->tanggal_mulai ? \Carbon\Carbon::parse($ujian->tanggal_mulai)->format('Y-m-d\TH:i') : '') }}" required>
                            @error('tanggal_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                value="{{ old('tanggal_selesai', $ujian->tanggal_selesai ? \Carbon\Carbon::parse($ujian->tanggal_selesai)->format('Y-m-d\TH:i') : '') }}" required>
                            @error('tanggal_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    @php $existingKelas = old('kelas_ids', $ujian->kelas->pluck('id')->toArray()); @endphp
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kelas Peserta <span class="text-danger">*</span></label>
                        <div class="row">
                            @foreach($kelas as $k)
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="kelas_ids[]" value="{{ $k->id }}" id="kelas{{ $k->id }}"
                                        {{ in_array($k->id, $existingKelas) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="kelas{{ $k->id }}">{{ $k->nama }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @error('kelas_ids') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    @php $existingRuang = old('ruang_ids', $ujian->ruang->pluck('id')->toArray()); @endphp
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ruang Ujian</label>
                        <div class="mb-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="massal" id="massalCheck" value="1"
                                    {{ old('massal') ? 'checked' : '' }} onchange="toggleMassal(this)">
                                <label class="form-check-label fw-semibold text-primary" for="massalCheck">
                                    <i class="bi bi-check2-all"></i> Tambahkan ke Semua Ruang (Massal)
                                </label>
                            </div>
                        </div>
                        <div id="ruangCheckboxes" class="{{ old('massal') ? 'opacity-50' : '' }}">
                            <div class="row">
                                @foreach($ruang as $r)
                                <div class="col-md-4 mb-1">
                                    <div class="form-check">
                                        <input class="form-check-input ruang-check" type="checkbox" name="ruang_ids[]" value="{{ $r->id }}" id="ruang{{ $r->id }}"
                                            {{ in_array($r->id, $existingRuang) ? 'checked' : '' }}
                                            {{ old('massal') ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="ruang{{ $r->id }}">
                                            <strong>{{ $r->kode }}</strong> - {{ $r->nama }}
                                            <small class="text-muted">({{ $r->siswa_count }} siswa)</small>
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @error('ruang_ids') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">KKM <span class="text-danger">*</span></label>
                            <input type="number" name="kkm" class="form-control @error('kkm') is-invalid @enderror" value="{{ old('kkm', $ujian->kkm) }}" min="0" max="100" step="0.1" required>
                            @error('kkm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jumlah Soal Tampil</label>
                            <input type="number" name="jumlah_soal_tampil" class="form-control" value="{{ old('jumlah_soal_tampil', $ujian->jumlah_soal_tampil) }}" min="1" placeholder="Kosongkan = semua">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="acak_soal" id="acakSoal" value="1" {{ old('acak_soal', $ujian->acak_soal) ? 'checked' : '' }}>
                                <label class="form-check-label" for="acakSoal">Acak Urutan Soal</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="acak_opsi" id="acakOpsi" value="1" {{ old('acak_opsi', $ujian->acak_opsi) ? 'checked' : '' }}>
                                <label class="form-check-label" for="acakOpsi">Acak Urutan Opsi</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="fullscreen_mode" id="fullscreenMode" value="1" {{ old('fullscreen_mode', $ujian->fullscreen_mode) ? 'checked' : '' }}>
                                <label class="form-check-label" for="fullscreenMode">Mode Fullscreen</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="tampilkan_nilai" id="tampilkanNilai" value="1" {{ old('tampilkan_nilai', $ujian->tampilkan_nilai) ? 'checked' : '' }}>
                                <label class="form-check-label" for="tampilkanNilai">Tampilkan Nilai</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan/Petunjuk</label>
                        <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $ujian->keterangan) }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
                        <a href="{{ route('admin.ujian.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleMassal(el) {
    const container = document.getElementById('ruangCheckboxes');
    const checks = container.querySelectorAll('.ruang-check');
    if (el.checked) {
        container.classList.add('opacity-50');
        checks.forEach(c => { c.checked = true; c.disabled = true; });
    } else {
        container.classList.remove('opacity-50');
        checks.forEach(c => { c.disabled = false; });
    }
}
</script>
@endpush
