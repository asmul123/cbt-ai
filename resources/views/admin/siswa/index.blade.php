@extends('layouts.app')
@section('title', 'Data Siswa')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Data Siswa</h5>
    <a href="{{ route('admin.siswa.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Tambah Siswa
    </a>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari NIS/Nama..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="kelas_id" class="form-select form-select-sm">
                    <option value="">Semua Kelas</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="jurusan_id" class="form-select form-select-sm">
                    <option value="">Semua Jurusan</option>
                    @foreach($jurusan as $j)
                        <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('admin.siswa.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>Status</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswa as $i => $s)
                    <tr>
                        <td>{{ $siswa->firstItem() + $i }}</td>
                        <td>{{ $s->nis }}</td>
                        <td class="fw-semibold">{{ $s->user->name ?? '-' }}</td>
                        <td>{{ $s->kelas->nama ?? '-' }}</td>
                        <td>{{ $s->kelas->jurusan->nama ?? '-' }}</td>
                        <td>
                            @if($s->user && $s->user->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.siswa.edit', $s) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.siswa.resetPassword', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Reset password siswa ini?')">
                                @csrf
                                <button class="btn btn-info btn-sm" title="Reset Password"><i class="bi bi-key"></i></button>
                            </form>
                            <form action="{{ route('admin.siswa.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus siswa ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data siswa</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($siswa->hasPages())
    <div class="card-footer">
        {{ $siswa->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
