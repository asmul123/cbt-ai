@extends('layouts.app')
@section('title', 'Bank Soal')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Bank Soal</h5>
    <div>
        <a href="{{ route('guru.soal.import') }}" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel"></i> Import Excel</a>
        <a href="{{ route('guru.soal.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah Soal</a>
    </div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="mapel_id" class="form-select form-select-sm">
                    <option value="">Semua Mapel</option>
                    @foreach($mapel as $m)
                        <option value="{{ $m->id }}" {{ request('mapel_id') == $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="tipe_soal" class="form-select form-select-sm">
                    <option value="">Semua Tipe</option>
                    <option value="pg" {{ request('tipe_soal') == 'pg' ? 'selected' : '' }}>Pilihan Ganda</option>
                    <option value="pg_kompleks" {{ request('tipe_soal') == 'pg_kompleks' ? 'selected' : '' }}>PG Kompleks</option>
                    <option value="isian" {{ request('tipe_soal') == 'isian' ? 'selected' : '' }}>Isian</option>
                    <option value="essay" {{ request('tipe_soal') == 'essay' ? 'selected' : '' }}>Essay</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari soal..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('guru.soal.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                        <th>Soal</th>
                        <th>Mapel</th>
                        <th>Tipe</th>
                        <th>Tingkat</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($soal as $i => $s)
                    <tr>
                        <td>{{ $soal->firstItem() + $i }}</td>
                        <td>{{ Str::limit(strip_tags($s->soal), 80) }}</td>
                        <td>{{ $s->mapel->nama ?? '-' }}</td>
                        <td>
                            @if($s->tipe_soal == 'pg') <span class="badge bg-primary">PG</span>
                            @elseif($s->tipe_soal == 'pg_kompleks') <span class="badge bg-info">PG Kompleks</span>
                            @elseif($s->tipe_soal == 'isian') <span class="badge bg-warning">Isian</span>
                            @else <span class="badge bg-success">Essay</span>
                            @endif
                        </td>
                        <td>
                            @if($s->tingkat_kesulitan == 'mudah') <span class="badge bg-success">Mudah</span>
                            @elseif($s->tingkat_kesulitan == 'sedang') <span class="badge bg-warning">Sedang</span>
                            @else <span class="badge bg-danger">Sulit</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('guru.soal.show', $s) }}" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('guru.soal.edit', $s) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('guru.soal.duplicate', $s) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-secondary btn-sm" title="Duplikat"><i class="bi bi-copy"></i></button>
                            </form>
                            <form action="{{ route('guru.soal.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus soal ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada soal</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($soal->hasPages())
    <div class="card-footer">{{ $soal->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
