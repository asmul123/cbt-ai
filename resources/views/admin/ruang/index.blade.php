@extends('layouts.app')
@section('title', 'Data Ruang Ujian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Data Ruang Ujian</h5>
    <a href="{{ route('admin.ruang.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Tambah Ruang
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Kode</th>
                        <th>Nama Ruang</th>
                        <th>Kapasitas</th>
                        <th>Lokasi</th>
                        <th>Siswa</th>
                        <th>Proktor</th>
                        <th>Status</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ruang as $i => $r)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><code>{{ $r->kode }}</code></td>
                        <td class="fw-semibold">{{ $r->nama }}</td>
                        <td>{{ $r->kapasitas }} siswa</td>
                        <td>{{ $r->lokasi ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $r->siswa_count > 0 ? 'bg-info' : 'bg-secondary' }}">{{ $r->siswa_count }} siswa</span>
                        </td>
                        <td>{{ $r->proktor_count }} proktor</td>
                        <td>
                            @if($r->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.ruang.show', $r) }}" class="btn btn-info btn-sm text-white" title="Kelola siswa & jadwal"><i class="bi bi-gear"></i> Kelola</a>
                            <a href="{{ route('admin.ruang.edit', $r) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.ruang.destroy', $r) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus ruang ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Belum ada data ruang ujian</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
