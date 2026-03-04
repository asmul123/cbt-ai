@extends('layouts.app')
@section('title', 'Detail Pengerjaan: ' . $siswa->nama)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">Detail Pengerjaan</h5>
        <small class="text-muted">{{ $siswa->nama }} ({{ $siswa->nis }}) - {{ $ujian->nama_ujian }}</small>
    </div>
    <a href="{{ route('admin.hasil.show', $ujian) }}" class="btn btn-secondary btn-sm">Kembali</a>
</div>

<!-- Ringkasan -->
@if($hasil)
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-2">
                <div class="text-muted small">Nilai Akhir</div>
                <div class="fs-3 fw-bold {{ $hasil->nilai_akhir >= $ujian->kkm ? 'text-success' : 'text-danger' }}">{{ number_format($hasil->nilai_akhir, 1) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-2">
                <div class="text-muted small">Skor PG</div>
                <div class="fs-4 fw-bold text-primary">{{ number_format($hasil->skor_pg, 1) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-2">
                <div class="text-muted small">Skor Isian</div>
                <div class="fs-4 fw-bold text-info">{{ number_format($hasil->skor_isian, 1) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-2">
                <div class="text-muted small">Skor Essay</div>
                <div class="fs-4 fw-bold text-warning">{{ number_format($hasil->skor_essay, 1) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-2">
                <div class="text-muted small">Durasi</div>
                <div class="fs-5 fw-bold text-secondary">
                    @if($hasil->durasi_pengerjaan)
                        {{ floor($hasil->durasi_pengerjaan / 60) }}m {{ $hasil->durasi_pengerjaan % 60 }}s
                    @else - @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-2">
                <div class="text-muted small">Status</div>
                @if($hasil->status_kelulusan == 'lulus')
                    <span class="badge bg-success fs-6">Lulus</span>
                @elseif($hasil->status_kelulusan == 'belum_dinilai')
                    <span class="badge bg-warning fs-6">Belum Final</span>
                @else
                    <span class="badge bg-danger fs-6">Tidak Lulus</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- Detail Soal per Soal -->
@foreach($soalOrder as $idx => $soalId)
    @php
        $soal = $soalList[$soalId] ?? null;
        $jwb = $jawaban[$soalId] ?? null;
        if (!$soal) continue;
    @endphp
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <div>
                <span class="badge bg-secondary me-1">Soal {{ $idx + 1 }}</span>
                @if($soal->tipe_soal == 'pg') <span class="badge bg-primary">PG</span>
                @elseif($soal->tipe_soal == 'pg_kompleks') <span class="badge bg-info">PG Kompleks</span>
                @elseif($soal->tipe_soal == 'isian') <span class="badge bg-warning text-dark">Isian</span>
                @else <span class="badge bg-success">Essay</span>
                @endif
                <small class="text-muted ms-2">Bobot: {{ $soal->bobot }}</small>
            </div>
            <div>
                @if($jwb)
                    @if($soal->tipe_soal == 'essay')
                        @if($jwb->skor !== null)
                            <span class="badge bg-info">Skor: {{ $jwb->skor }}/{{ $soal->bobot }}</span>
                        @else
                            <span class="badge bg-warning">Belum Dinilai</span>
                        @endif
                    @elseif($jwb->is_benar)
                        <span class="badge bg-success"><i class="bi bi-check-lg"></i> Benar</span>
                    @else
                        <span class="badge bg-danger"><i class="bi bi-x-lg"></i> Salah</span>
                    @endif
                @else
                    <span class="badge bg-secondary">Tidak Dijawab</span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <!-- Soal Text -->
            <div class="mb-3">{!! $soal->soal !!}</div>

            @if(in_array($soal->tipe_soal, ['pg', 'pg_kompleks']))
                <!-- Opsi PG -->
                @php
                    $jawabanSiswa = $jwb ? $jwb->jawaban : null;
                    $jawabanArray = $soal->tipe_soal == 'pg_kompleks' ? (json_decode($jawabanSiswa, true) ?? []) : [];
                @endphp
                <div class="list-group list-group-flush">
                    @foreach($soal->opsi as $opsi)
                        @php
                            $isSelected = false;
                            if ($soal->tipe_soal == 'pg') {
                                $isSelected = $jawabanSiswa == $opsi->label;
                            } else {
                                $isSelected = in_array($opsi->label, $jawabanArray);
                            }
                            $isCorrect = $opsi->is_benar;
                        @endphp
                        <div class="list-group-item py-2 {{ $isCorrect ? 'list-group-item-success' : ($isSelected && !$isCorrect ? 'list-group-item-danger' : '') }}">
                            <div class="d-flex align-items-start">
                                <span class="fw-bold me-2" style="min-width: 25px;">{{ $opsi->label }}.</span>
                                <span>{!! $opsi->teks !!}</span>
                                @if($isSelected)
                                    <span class="ms-auto badge {{ $isCorrect ? 'bg-success' : 'bg-danger' }}">
                                        {{ $isCorrect ? 'Benar' : 'Jawaban Siswa' }}
                                    </span>
                                @elseif($isCorrect)
                                    <span class="ms-auto badge bg-success">Kunci Jawaban</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif($soal->tipe_soal == 'isian')
                @php
                    $kunciIsi = $soal->opsi->where('is_benar', true)->first();
                @endphp
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Jawaban Siswa:</label>
                        <div class="p-2 rounded border {{ $jwb && $jwb->is_benar ? 'border-success bg-success bg-opacity-10' : 'border-danger bg-danger bg-opacity-10' }}">
                            {{ $jwb->jawaban ?? '-' }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Kunci Jawaban:</label>
                        <div class="p-2 rounded border border-success bg-success bg-opacity-10">
                            {{ $kunciIsi->teks ?? '-' }}
                        </div>
                    </div>
                </div>
            @elseif($soal->tipe_soal == 'essay')
                <div class="mb-3">
                    <label class="form-label text-muted small">Jawaban Siswa:</label>
                    <div class="p-3 rounded border bg-light">{!! nl2br(e($jwb->jawaban ?? 'Tidak dijawab')) !!}</div>
                </div>
                @if($jwb)
                <form action="{{ route('admin.hasil.simpanEssay', $jwb) }}" method="POST" class="d-flex align-items-center gap-3">
                    @csrf
                    <div class="input-group" style="max-width: 280px;">
                        <span class="input-group-text">Skor (max: {{ $soal->bobot }})</span>
                        <input type="number" name="skor" class="form-control" min="0" max="{{ $soal->bobot }}" step="0.1" value="{{ $jwb->skor }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg"></i> Simpan Skor</button>
                    @if($jwb->skor !== null)
                        <span class="text-success small"><i class="bi bi-check-circle"></i> Sudah dinilai</span>
                    @endif
                </form>
                @endif
            @endif
        </div>
    </div>
@endforeach
@endsection
