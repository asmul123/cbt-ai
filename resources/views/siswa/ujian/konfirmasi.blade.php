@extends('layouts.app')
@section('title', 'Konfirmasi Ujian')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-primary text-white text-center">
                <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Konfirmasi Ujian</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive mb-4">
                    <table class="table table-borderless">
                        <tr><td class="text-muted" width="150">Nama Ujian</td><td class="fw-bold">{{ $ujian->nama }}</td></tr>
                        <tr><td class="text-muted">Mata Pelajaran</td><td>{{ $ujian->mapel->nama ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Durasi</td><td>{{ $ujian->durasi }} menit</td></tr>
                        <tr><td class="text-muted">Jumlah Soal</td><td>{{ $ujian->soal_count }} soal</td></tr>
                        <tr><td class="text-muted">Nama Peserta</td><td>{{ $siswa->user->name ?? '-' }}</td></tr>
                        <tr><td class="text-muted">NIS</td><td>{{ $siswa->nis }}</td></tr>
                        <tr><td class="text-muted">Kelas</td><td>{{ $siswa->kelas->nama ?? '-' }}</td></tr>
                    </table>
                </div>

                @if($ujian->deskripsi)
                <div class="alert alert-info">
                    <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Petunjuk Ujian</h6>
                    {!! nl2br(e($ujian->deskripsi)) !!}
                </div>
                @endif

                <div class="alert alert-warning">
                    <h6 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Perhatian!</h6>
                    <ul class="mb-0">
                        <li>Pastikan koneksi internet stabil</li>
                        <li>Jangan pindah tab/window selama ujian</li>
                        <li>Jangan menekan tombol back browser</li>
                        <li>Waktu akan berjalan otomatis setelah ujian dimulai</li>
                        <li>Ujian akan otomatis dikumpulkan saat waktu habis</li>
                    </ul>
                </div>

                <div class="d-flex gap-2 justify-content-center">
                    <form action="{{ route('siswa.ujian.mulai', $ujian) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Yakin ingin memulai ujian? Waktu akan langsung berjalan.')">
                            <i class="bi bi-play-fill"></i> Mulai Ujian
                        </button>
                    </form>
                    <a href="{{ route('siswa.dashboard') }}" class="btn btn-secondary btn-lg">Batal</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
