@extends('layouts.app')
@section('title', 'Konfirmasi Submit')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-warning text-dark text-center">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Konfirmasi Pengumpulan Ujian</h5>
            </div>
            <div class="card-body">
                <h6 class="text-center mb-4">{{ $ujian->nama }}</h6>

                <div class="row g-3 mb-4 text-center">
                    <div class="col-md-4">
                        <div class="p-3 bg-success bg-opacity-10 rounded">
                            <div class="fs-2 fw-bold text-success">{{ $terjawab }}</div>
                            <small class="text-muted">Terjawab</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-danger bg-opacity-10 rounded">
                            <div class="fs-2 fw-bold text-danger">{{ $belumDijawab }}</div>
                            <small class="text-muted">Belum Dijawab</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-warning bg-opacity-10 rounded">
                            <div class="fs-2 fw-bold text-warning">{{ $raguRagu }}</div>
                            <small class="text-muted">Ragu-ragu</small>
                        </div>
                    </div>
                </div>

                <div class="progress mb-3" style="height: 24px;">
                    <div class="progress-bar bg-success" style="width: {{ ($terjawab / max($totalSoal,1)) * 100 }}%">
                        {{ $terjawab }}/{{ $totalSoal }}
                    </div>
                </div>

                @if($belumDijawab > 0)
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i> Anda masih memiliki <strong>{{ $belumDijawab }}</strong> soal yang belum dijawab!
                </div>
                @endif

                @if($raguRagu > 0)
                <div class="alert alert-warning">
                    <i class="bi bi-flag"></i> Ada <strong>{{ $raguRagu }}</strong> soal yang ditandai ragu-ragu.
                </div>
                @endif

                <div class="d-flex gap-2 justify-content-center mt-4">
                    <a href="{{ route('siswa.ujian.kerjakan', [$ujian, 1]) }}" class="btn btn-secondary btn-lg">
                        <i class="bi bi-arrow-left"></i> Kembali ke Soal
                    </a>
                    <form action="{{ route('siswa.ujian.submit', $ujian) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Yakin ingin mengumpulkan ujian? Tindakan ini tidak bisa dibatalkan.')">
                            <i class="bi bi-send-fill"></i> Kumpulkan Ujian
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
