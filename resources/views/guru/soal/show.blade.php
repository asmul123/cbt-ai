@extends('layouts.app')
@section('title', 'Detail Soal')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Detail Soal</h6>
                <div>
                    <a href="{{ route('guru.soal.edit', $soal) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                    <a href="{{ route('guru.soal.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <small class="text-muted">Mapel</small>
                        <div class="fw-semibold">{{ $soal->mapel->nama ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Tipe</small>
                        <div>
                            @if($soal->tipe_soal == 'pg') <span class="badge bg-primary">Pilihan Ganda</span>
                            @elseif($soal->tipe_soal == 'pg_kompleks') <span class="badge bg-info">PG Kompleks</span>
                            @elseif($soal->tipe_soal == 'isian') <span class="badge bg-warning">Isian</span>
                            @else <span class="badge bg-success">Essay</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Tingkat</small>
                        <div>
                            @if($soal->tingkat_kesulitan == 'mudah') <span class="badge bg-success">Mudah</span>
                            @elseif($soal->tingkat_kesulitan == 'sedang') <span class="badge bg-warning">Sedang</span>
                            @else <span class="badge bg-danger">Sulit</span>
                            @endif
                        </div>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <small class="text-muted">Soal</small>
                    <div class="p-3 bg-light rounded soal-content">{!! $soal->soal !!}</div>
                </div>

                @if($soal->gambar)
                <div class="mb-3">
                    <small class="text-muted">Gambar</small>
                    <div><img src="{{ asset('storage/' . $soal->gambar) }}" class="img-fluid rounded" style="max-height: 300px"></div>
                </div>
                @endif

                @if($soal->opsi && $soal->opsi->count())
                <div class="mb-3">
                    <small class="text-muted">Opsi Jawaban</small>
                    @foreach($soal->opsi as $opsi)
                    <div class="d-flex align-items-start gap-2 py-1 {{ $opsi->is_benar ? 'text-success fw-bold' : '' }}">
                        <span class="badge {{ $opsi->is_benar ? 'bg-success' : 'bg-secondary' }}">{{ $opsi->label }}</span>
                        <div>{!! $opsi->teks !!}</div>
                        @if($opsi->is_benar) <i class="bi bi-check-circle-fill text-success"></i> @endif
                    </div>
                    @endforeach
                </div>
                @endif

                @if($soal->tipe_soal === 'isian' && $soal->opsi->first())
                <div class="mb-3">
                    <small class="text-muted">Jawaban Benar</small>
                    <div class="fw-bold text-success">{{ $soal->opsi->first()->teks }}</div>
                </div>
                @endif

                @if($soal->pembahasan)
                <div class="mb-3">
                    <small class="text-muted">Pembahasan</small>
                    <div class="p-3 bg-light rounded soal-content">{!! $soal->pembahasan !!}</div>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-4"><small class="text-muted">KD:</small> {{ $soal->kompetensi_dasar ?? '-' }}</div>
                    <div class="col-md-4"><small class="text-muted">Bobot:</small> {{ $soal->bobot }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
