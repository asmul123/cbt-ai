@extends('layouts.app')
@section('title', 'Ujian Selesai')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card text-center">
            <div class="card-body py-5">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                </div>
                <h3 class="text-success mb-3">Ujian Selesai!</h3>
                <p class="text-muted">{{ $ujian->nama }}</p>

                @if($hasil)
                <div class="bg-light rounded p-4 mt-4 mx-auto" style="max-width: 300px;">
                    @if($ujian->tampilkan_nilai)
                        <div class="text-muted small">Nilai Anda</div>
                        <div class="fs-1 fw-bold {{ $hasil->nilai_akhir >= ($ujian->kkm ?? 75) ? 'text-success' : 'text-danger' }}">
                            {{ number_format($hasil->nilai_akhir, 1) }}
                        </div>
                        <div class="row mt-3">
                            <div class="col">
                                <small class="text-muted">Benar</small>
                                <div class="fw-semibold text-success">{{ $hasil->benar_pg ?? 0 }}</div>
                            </div>
                            <div class="col">
                                <small class="text-muted">Total Soal</small>
                                <div class="fw-semibold text-info">{{ $hasil->jumlah_soal }}</div>
                            </div>
                        </div>
                        @if($hasil->status_kelulusan == 'belum_dinilai')
                            <div class="alert alert-warning mt-3 mb-0 small">
                                <i class="bi bi-info-circle"></i> Nilai belum final karena ada soal essay yang perlu dinilai guru.
                            </div>
                        @endif
                    @else
                        <div class="text-muted"><i class="bi bi-eye-slash fs-1"></i></div>
                        <p class="text-muted mt-2 mb-0">Nilai tidak ditampilkan untuk ujian ini.</p>
                    @endif
                </div>
                @endif

                <div class="mt-4">
                    <a href="{{ route('siswa.dashboard') }}" class="btn btn-primary"><i class="bi bi-house"></i> Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
