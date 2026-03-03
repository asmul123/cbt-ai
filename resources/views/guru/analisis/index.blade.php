@extends('layouts.app')
@section('title', 'Analisis Butir Soal')

@section('content')
<h5 class="mb-3">Analisis Butir Soal</h5>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Ujian</th>
                        <th>Mapel</th>
                        <th>Jumlah Soal</th>
                        <th>Peserta</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ujian as $i => $u)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $u->nama }}</td>
                        <td>{{ $u->mapel->nama ?? '-' }}</td>
                        <td><span class="badge bg-primary">{{ $u->soal->count() }}</span></td>
                        <td><span class="badge bg-info">{{ $u->peserta->count() }}</span></td>
                        <td>
                            <a href="{{ route('guru.analisis.show', $u) }}" class="btn btn-primary btn-sm"><i class="bi bi-graph-up"></i> Analisis</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada ujian selesai</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
