@extends('layouts.app')
@section('title', 'Data Mata Pelajaran')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Data Mata Pelajaran</h5>
    <a href="{{ route('admin.mapel.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Tambah Mapel
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Kode</th>
                        <th>Nama Mapel</th>
                        <th>Jurusan</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mapel as $i => $m)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><span class="badge bg-primary">{{ $m->kode }}</span></td>
                        <td>{{ $m->nama }}</td>
                        <td>{{ $m->jurusan->nama ?? 'Umum' }}</td>
                        <td>
                            <a href="{{ route('admin.mapel.edit', $m) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.mapel.destroy', $m) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus mapel ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data mata pelajaran</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
