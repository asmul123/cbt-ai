@extends('layouts.app')
@section('title', 'Data Guru')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Data Guru</h5>
    <a href="{{ route('admin.guru.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Tambah Guru
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>NIP</th>
                        <th>Nama Guru</th>
                        <th>Mata Pelajaran</th>
                        <th>Username</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guru as $i => $g)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $g->nip }}</td>
                        <td class="fw-semibold">{{ $g->user->name ?? '-' }}</td>
                        <td>{{ $g->mapel->nama ?? '-' }}</td>
                        <td><code>{{ $g->user->username ?? '-' }}</code></td>
                        <td>
                            @if($g->user && $g->user->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.guru.edit', $g) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.guru.destroy', $g) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus guru ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data guru</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
