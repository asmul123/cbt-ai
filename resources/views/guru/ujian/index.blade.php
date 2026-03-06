@extends('layouts.app')
@section('title', 'Daftar Ujian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Daftar Ujian</h5>
    <a href="{{ route('guru.ujian.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Buat Ujian</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Nama Ujian</th>
                        <th>Mapel</th>
                        <th>Durasi</th>
                        <th>Jumlah Soal</th>
                        <th>Token</th>
                        <th>Status</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ujian as $i => $u)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $u->nama }}</td>
                        <td>{{ $u->mapel->nama ?? '-' }}</td>
                        <td>{{ $u->durasi }} menit</td>
                        <td><span class="badge bg-primary">{{ $u->soal_count }}</span></td>
                        <td>
                            @if($u->token)
                                <span class="badge bg-dark font-monospace">{{ $u->token }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($u->status == 'publish') <span class="badge bg-success">Publish</span>
                            @elseif($u->status == 'berlangsung') <span class="badge bg-warning text-dark">Berlangsung</span>
                            @elseif($u->status == 'draft') <span class="badge bg-secondary">Draft</span>
                            @elseif($u->status == 'selesai') <span class="badge bg-dark">Selesai</span>
                            @else <span class="badge bg-info">{{ ucfirst($u->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('guru.ujian.edit', $u) }}" class="btn btn-warning btn-sm" title="Edit Ujian"><i class="bi bi-pencil"></i></a>
                            @if($u->status == 'draft')
                                <a href="{{ route('guru.ujian.soal', $u) }}" class="btn btn-info btn-sm" title="Pilih Soal"><i class="bi bi-list-check"></i></a>
                                <form action="{{ route('guru.ujian.publish', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Publish ujian ini?')">
                                    @csrf
                                    <button class="btn btn-success btn-sm" title="Publish"><i class="bi bi-send"></i></button>
                                </form>
                                <form action="{{ route('guru.ujian.destroy', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus ujian?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            @else
                                <a href="{{ route('guru.ujian.soal', $u) }}" class="btn btn-info btn-sm" title="Kelola Soal"><i class="bi bi-list-check"></i></a>
                                <a href="{{ route('guru.hasil.show', $u) }}" class="btn btn-primary btn-sm" title="Lihat Hasil"><i class="bi bi-bar-chart"></i></a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada ujian</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
