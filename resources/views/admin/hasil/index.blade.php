@extends('layouts.app')
@section('title', 'Hasil Ujian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-bar-chart-line"></i> Hasil Ujian</h5>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Ujian</th>
                        <th>Mapel</th>
                        <th>Guru</th>
                        <th class="text-center">Peserta</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ujian as $i => $u)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $u->nama_ujian }}</td>
                        <td>{{ $u->mapel->nama ?? '-' }}</td>
                        <td>{{ $u->guru->user->name ?? '-' }}</td>
                        <td class="text-center"><span class="badge bg-info">{{ $u->peserta_count }}</span></td>
                        <td>
                            @if($u->status == 'berlangsung') <span class="badge bg-success">Berlangsung</span>
                            @else <span class="badge bg-dark">Selesai</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.hasil.show', $u) }}" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i> Lihat Hasil</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada ujian yang memiliki hasil</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
