@extends('layouts.app')
@section('title', 'Manajemen Ujian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Manajemen Ujian</h5>
    <a href="{{ route('admin.ujian.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Buat Ujian Baru</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="40">#</th>
                        <th>Nama Ujian</th>
                        <th>Mapel</th>
                        <th>Guru</th>
                        <th class="text-center">Soal</th>
                        <th class="text-center">Peserta</th>
                        <th>Ruang</th>
                        <th>Token</th>
                        <th>Status</th>
                        <th width="220">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ujian as $i => $u)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $u->nama_ujian }}</td>
                        <td>{{ $u->mapel->nama ?? '-' }}</td>
                        <td>{{ $u->guru->user->name ?? '-' }}</td>
                        <td class="text-center"><span class="badge bg-info">{{ $u->soal_count }}</span></td>
                        <td class="text-center"><span class="badge bg-secondary">{{ $u->peserta_count }}</span></td>
                        <td>
                            @foreach($u->ruang->take(3) as $r)
                                <span class="badge bg-outline-dark border">{{ $r->kode }}</span>
                            @endforeach
                            @if($u->ruang->count() > 3)
                                <span class="text-muted small">+{{ $u->ruang->count() - 3 }}</span>
                            @endif
                        </td>
                        <td>
                            @if($u->token_ujian)
                                <span class="badge bg-dark font-monospace">{{ $u->token_ujian }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($u->status == 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @elseif($u->status == 'publish')
                                <span class="badge bg-primary">Publish</span>
                            @elseif($u->status == 'berlangsung')
                                <span class="badge bg-success">Berlangsung</span>
                            @else
                                <span class="badge bg-dark">Selesai</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                @if($u->status == 'draft')
                                    <a href="{{ route('admin.ujian.edit', $u) }}" class="btn btn-warning btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <a href="{{ route('admin.ujian.soal', $u) }}" class="btn btn-outline-primary btn-sm" title="Pilih Soal"><i class="bi bi-list-check"></i></a>
                                    <form action="{{ route('admin.ujian.publish', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Publish ujian ini?')">
                                        @csrf
                                        <button class="btn btn-success btn-sm" title="Publish"><i class="bi bi-send"></i></button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.ujian.generateToken', $u) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-info btn-sm" title="Generate Token Baru"><i class="bi bi-key"></i></button>
                                </form>
                                @if(in_array($u->status, ['publish', 'berlangsung']))
                                    <form action="{{ route('admin.ujian.updateStatus', $u) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        @if($u->status == 'publish')
                                            <input type="hidden" name="status" value="berlangsung">
                                            <button class="btn btn-outline-success btn-sm" title="Mulai Ujian"><i class="bi bi-play-fill"></i></button>
                                        @elseif($u->status == 'berlangsung')
                                            <input type="hidden" name="status" value="selesai">
                                            <button class="btn btn-dark btn-sm" title="Selesaikan"><i class="bi bi-stop-fill"></i></button>
                                        @endif
                                    </form>
                                @endif
                                @if($u->status == 'draft')
                                    <form action="{{ route('admin.ujian.destroy', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus ujian ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">Belum ada ujian. <a href="{{ route('admin.ujian.create') }}">Buat ujian baru</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
