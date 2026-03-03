@extends('layouts.app')
@section('title', 'Data Jurusan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Data Jurusan</h5>
    <a href="{{ route('admin.jurusan.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Tambah Jurusan
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
                        <th>Nama Jurusan</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jurusan as $i => $j)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><span class="badge bg-primary">{{ $j->kode }}</span></td>
                        <td>{{ $j->nama }}</td>
                        <td>
                            <a href="{{ route('admin.jurusan.edit', $j) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.jurusan.destroy', $j) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jurusan ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data jurusan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
