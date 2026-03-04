@extends('layouts.app')
@section('title', 'Tambah Soal')

@push('styles')
<style>
    .ck-editor__editable { min-height: 200px; }
    .opsi-card .ck-editor__editable { min-height: 80px; }
    .eq-btn { cursor: pointer; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Tambah Soal Baru</h5>
            <a href="{{ route('guru.soal.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <form id="soalForm" method="POST" action="{{ route('guru.soal.store') }}" enctype="multipart/form-data" onsubmit="return syncAllEditors()">
            @csrf

            {{-- Meta Fields --}}
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-gear"></i> Informasi Soal</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="mapel_id" class="form-select @error('mapel_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Mapel --</option>
                                @foreach($mapel as $m)
                                    <option value="{{ $m->id }}" {{ old('mapel_id') == $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                                @endforeach
                            </select>
                            @error('mapel_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipe Soal <span class="text-danger">*</span></label>
                            <select name="tipe_soal" id="tipeSoal" class="form-select @error('tipe_soal') is-invalid @enderror" required onchange="toggleOpsi()">
                                <option value="pg" {{ old('tipe_soal', 'pg') == 'pg' ? 'selected' : '' }}>Pilihan Ganda</option>
                                <option value="pg_kompleks" {{ old('tipe_soal') == 'pg_kompleks' ? 'selected' : '' }}>PG Kompleks</option>
                                <option value="isian" {{ old('tipe_soal') == 'isian' ? 'selected' : '' }}>Isian Singkat</option>
                                <option value="essay" {{ old('tipe_soal') == 'essay' ? 'selected' : '' }}>Essay</option>
                            </select>
                            @error('tipe_soal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tingkat Kesulitan <span class="text-danger">*</span></label>
                            <select name="tingkat_kesulitan" class="form-select @error('tingkat_kesulitan') is-invalid @enderror" required>
                                <option value="mudah" {{ old('tingkat_kesulitan', 'mudah') == 'mudah' ? 'selected' : '' }}>Mudah</option>
                                <option value="sedang" {{ old('tingkat_kesulitan') == 'sedang' ? 'selected' : '' }}>Sedang</option>
                                <option value="sulit" {{ old('tingkat_kesulitan') == 'sulit' ? 'selected' : '' }}>Sulit</option>
                            </select>
                            @error('tingkat_kesulitan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kompetensi Dasar</label>
                            <input type="text" name="kompetensi_dasar" class="form-control" value="{{ old('kompetensi_dasar') }}" placeholder="Contoh: 3.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bobot <span class="text-danger">*</span></label>
                            <input type="number" name="bobot" class="form-control @error('bobot') is-invalid @enderror" value="{{ old('bobot', 1) }}" step="0.01" min="0.01" required>
                            @error('bobot') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Soal Editor --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-pencil-square"></i> Soal <span class="text-danger">*</span></span>
                    <button type="button" class="btn btn-sm btn-outline-primary eq-btn" onclick="openEquationModal('soal')">
                        <i class="bi bi-calculator"></i> Sisipkan Persamaan
                    </button>
                </div>
                <div class="card-body">
                    <div id="soalEditor">{!! old('soal') !!}</div>
                    <input type="hidden" name="soal" id="soal_hidden" value="{{ old('soal') }}">
                    @error('soal') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Gambar Soal --}}
            <div class="card mb-3">
                <div class="card-body">
                    <label class="form-label"><i class="bi bi-image"></i> Gambar Soal (opsional)</label>
                    <input type="file" name="gambar" class="form-control @error('gambar') is-invalid @enderror" accept="image/*">
                    @error('gambar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted">Maks 2MB. Format: JPG, PNG, GIF, WebP. Atau sisipkan langsung di editor soal via toolbar.</small>
                </div>
            </div>

            @php
                $oldOpsi = old('opsi', []);
                $initialOpsiCount = count($oldOpsi) > 0 ? count($oldOpsi) : 5;
                $initialOpsiCount = max(2, $initialOpsiCount);
            @endphp
            {{-- Opsi Jawaban (PG / PG Kompleks) --}}
            <div id="opsiContainer">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-list-check"></i> Opsi Jawaban</span>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addOpsi()">
                            <i class="bi bi-plus-circle"></i> Tambah Opsi
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="bi bi-info-circle"></i>
                            <span id="tipePetunjuk">Centang <strong>satu</strong> jawaban yang benar.</span>
                        </p>
                        <div id="opsiList">
                            @for($i = 0; $i < $initialOpsiCount; $i++)
                            <div class="card opsi-card mb-2" id="opsiCard_{{ $i }}">
                                <div class="card-body py-2">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="pt-2">
                                            <span class="badge bg-secondary fs-6 opsi-label">{{ chr(65 + $i) }}</span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="opsi-editor" id="opsiEditor_{{ $i }}">{!! old("opsi.$i.teks") !!}</div>
                                            <input type="hidden" name="opsi[{{ $i }}][teks]" id="opsi_{{ $i }}_hidden" value="{{ old("opsi.$i.teks") }}">
                                        </div>
                                        <div class="pt-2 d-flex gap-1 align-items-center">
                                            <div class="form-check">
                                                <input type="checkbox" name="jawaban_benar_pg[]" value="{{ $i }}" class="form-check-input jawaban-check"
                                                    {{ is_array(old('jawaban_benar_pg')) && in_array($i, old('jawaban_benar_pg')) ? 'checked' : '' }}>
                                                <label class="form-check-label text-success fw-semibold">Benar</label>
                                            </div>
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-opsi" onclick="removeOpsi({{ $i }})" title="Hapus opsi">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            {{-- Jawaban Isian --}}
            <div id="jawabanIsianContainer" style="display:none;">
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-input-cursor-text"></i> Jawaban Benar (Isian)</div>
                    <div class="card-body">
                        <input type="text" name="jawaban_benar" class="form-control" value="{{ old('jawaban_benar') }}" placeholder="Ketik jawaban yang benar...">
                    </div>
                </div>
            </div>

            {{-- Pembahasan --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-lightbulb"></i> Pembahasan (Opsional)</span>
                    <button type="button" class="btn btn-sm btn-outline-primary eq-btn" onclick="openEquationModal('pembahasan')">
                        <i class="bi bi-calculator"></i> Sisipkan Persamaan
                    </button>
                </div>
                <div class="card-body">
                    <div id="pembahasanEditor">{!! old('pembahasan') !!}</div>
                    <input type="hidden" name="pembahasan" id="pembahasan_hidden" value="{{ old('pembahasan') }}">
                </div>
            </div>

            {{-- Submit --}}
            <div class="d-flex gap-2 mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Soal
                </button>
                <a href="{{ route('guru.soal.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

@include('guru.soal._equation-modal')
@endsection

@push('scripts')
@include('guru.soal._editor-scripts')
<script>
    // Set opsi counter from server-rendered count
    opsiCounter = {{ $initialOpsiCount }};

    // Update petunjuk berdasarkan tipe soal
    document.getElementById('tipeSoal').addEventListener('change', function() {
        const petunjuk = document.getElementById('tipePetunjuk');
        if (this.value === 'pg') {
            petunjuk.innerHTML = 'Centang <strong>satu</strong> jawaban yang benar.';
        } else if (this.value === 'pg_kompleks') {
            petunjuk.innerHTML = 'Centang <strong>semua</strong> jawaban yang benar (bisa lebih dari satu).';
        }
    });

    // Initial setup
    bindCheckboxBehavior();
    updateRemoveButtons();
    toggleOpsi();
</script>
@endpush
