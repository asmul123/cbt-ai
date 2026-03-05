@extends('layouts.app')
@section('title', 'Edit Soal')

@push('styles')
<style>
    .ck-editor__editable { min-height: 200px; }
    .opsi-card .ck-editor__editable { min-height: 80px; }
    .eq-btn { cursor: pointer; }
</style>
@endpush

@section('content')
@php
    $opsiItems = $soal->opsi->sortBy('urutan')->values();
    $initialOpsiCount = $opsiItems->count() > 0 ? $opsiItems->count() : 5;
    if (count(old('opsi', [])) > 0) {
        $initialOpsiCount = count(old('opsi'));
    }
    $initialOpsiCount = max(2, $initialOpsiCount);
    $jawabanIsian = '';
    if ($soal->tipe_soal === 'isian' && $soal->opsi->first()) {
        $jawabanIsian = $soal->opsi->first()->teks;
    }
@endphp

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Soal</h5>
            <a href="{{ route('guru.soal.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <form id="soalForm" method="POST" action="{{ route('guru.soal.update', $soal) }}" enctype="multipart/form-data" onsubmit="return syncAllEditors()">
            @csrf
            @method('PUT')

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
                                    <option value="{{ $m->id }}" {{ old('mapel_id', $soal->mapel_id) == $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                                @endforeach
                            </select>
                            @error('mapel_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipe Soal <span class="text-danger">*</span></label>
                            <select name="tipe_soal" id="tipeSoal" class="form-select @error('tipe_soal') is-invalid @enderror" required onchange="toggleOpsi()">
                                <option value="pg" {{ old('tipe_soal', $soal->tipe_soal) == 'pg' ? 'selected' : '' }}>Pilihan Ganda</option>
                                <option value="pg_kompleks" {{ old('tipe_soal', $soal->tipe_soal) == 'pg_kompleks' ? 'selected' : '' }}>PG Kompleks</option>
                                <option value="isian" {{ old('tipe_soal', $soal->tipe_soal) == 'isian' ? 'selected' : '' }}>Isian Singkat</option>
                                <option value="essay" {{ old('tipe_soal', $soal->tipe_soal) == 'essay' ? 'selected' : '' }}>Essay</option>
                            </select>
                            @error('tipe_soal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tingkat Kesulitan <span class="text-danger">*</span></label>
                            <select name="tingkat_kesulitan" class="form-select @error('tingkat_kesulitan') is-invalid @enderror" required>
                                <option value="mudah" {{ old('tingkat_kesulitan', $soal->tingkat_kesulitan) == 'mudah' ? 'selected' : '' }}>Mudah</option>
                                <option value="sedang" {{ old('tingkat_kesulitan', $soal->tingkat_kesulitan) == 'sedang' ? 'selected' : '' }}>Sedang</option>
                                <option value="sulit" {{ old('tingkat_kesulitan', $soal->tingkat_kesulitan) == 'sulit' ? 'selected' : '' }}>Sulit</option>
                            </select>
                            @error('tingkat_kesulitan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kompetensi Dasar</label>
                            <input type="text" name="kompetensi_dasar" class="form-control" value="{{ old('kompetensi_dasar', $soal->kompetensi_dasar) }}" placeholder="Contoh: 3.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bobot <span class="text-danger">*</span></label>
                            <input type="number" name="bobot" class="form-control @error('bobot') is-invalid @enderror" value="{{ old('bobot', $soal->bobot) }}" step="0.01" min="0.01" required>
                            @error('bobot') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft" {{ old('status', $soal->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="aktif" {{ old('status', $soal->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
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
                    <div id="soalEditor">{!! old('soal', $soal->soal) !!}</div>
                    <input type="hidden" name="soal" id="soal_hidden" value="{{ old('soal', $soal->soal) }}">
                    @error('soal') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Gambar Soal --}}
            <div class="card mb-3">
                <div class="card-body">
                    <label class="form-label"><i class="bi bi-image"></i> Gambar Soal (opsional)</label>
                    @if($soal->gambar)
                        <div class="mb-2" id="gambarSoalPreview">
                            <img src="{{ asset('storage/' . $soal->gambar) }}" class="img-fluid rounded" style="max-height: 200px">
                            <div class="mt-2 d-flex gap-2 align-items-center">
                                <button type="button" class="btn btn-sm btn-outline-danger" id="btnHapusGambarSoal" onclick="hapusGambarSoal()">
                                    <i class="bi bi-trash"></i> Hapus Gambar
                                </button>
                                <small class="text-muted">atau upload baru untuk mengganti.</small>
                            </div>
                        </div>
                    @endif
                    <input type="file" name="gambar" class="form-control @error('gambar') is-invalid @enderror" accept="image/*">
                    @error('gambar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Opsi Jawaban --}}
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
                            <span id="tipePetunjuk">
                                @if($soal->tipe_soal === 'pg_kompleks')
                                    Centang <strong>semua</strong> jawaban yang benar (bisa lebih dari satu).
                                @else
                                    Centang <strong>satu</strong> jawaban yang benar.
                                @endif
                            </span>
                        </p>
                        <div id="opsiList">
                            @for($i = 0; $i < $initialOpsiCount; $i++)
                            @php
                                $opsi = $opsiItems[$i] ?? null;
                                $opsiTeks = old("opsi.$i.teks", $opsi->teks ?? '');
                                $isBenar = is_array(old('jawaban_benar_pg'))
                                    ? in_array($i, old('jawaban_benar_pg'))
                                    : ($opsi && $opsi->is_benar);
                            @endphp
                            <div class="card opsi-card mb-2" id="opsiCard_{{ $i }}">
                                <div class="card-body py-2">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="pt-2">
                                            <span class="badge bg-secondary fs-6 opsi-label">{{ chr(65 + $i) }}</span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="opsi-editor" id="opsiEditor_{{ $i }}">{!! $opsiTeks !!}</div>
                                            <input type="hidden" name="opsi[{{ $i }}][teks]" id="opsi_{{ $i }}_hidden" value="{{ $opsiTeks }}">
                                        </div>
                                        <div class="pt-2 d-flex gap-1 align-items-center">
                                            <div class="form-check">
                                                <input type="checkbox" name="jawaban_benar_pg[]" value="{{ $i }}" class="form-check-input jawaban-check"
                                                    {{ $isBenar ? 'checked' : '' }}>
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
                        <input type="text" name="jawaban_benar" class="form-control" value="{{ old('jawaban_benar', $jawabanIsian) }}" placeholder="Ketik jawaban yang benar...">
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
                    <div id="pembahasanEditor">{!! old('pembahasan', $soal->pembahasan) !!}</div>
                    <input type="hidden" name="pembahasan" id="pembahasan_hidden" value="{{ old('pembahasan', $soal->pembahasan) }}">
                </div>
            </div>

            {{-- Submit --}}
            <div class="d-flex gap-2 mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Soal
                </button>
                <a href="{{ route('guru.soal.show', $soal) }}" class="btn btn-outline-secondary">Batal</a>
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

    // Hapus gambar soal (opsional)
    function hapusGambarSoal() {
        if (!confirm('Yakin ingin menghapus gambar soal ini?')) return;

        const btn = document.getElementById('btnHapusGambarSoal');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menghapus...';

        fetch('{{ route("guru.soal.hapusGambarSoal", $soal->id) }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('gambarSoalPreview').remove();
            } else {
                alert('Gagal menghapus gambar.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-trash"></i> Hapus Gambar';
            }
        })
        .catch(() => {
            alert('Terjadi kesalahan saat menghapus gambar.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-trash"></i> Hapus Gambar';
        });
    }
</script>
@endpush
