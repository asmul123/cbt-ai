@extends('layouts.app')
@section('title', 'Berita Acara & Daftar Hadir')

@section('content')
<h5 class="mb-3">Berita Acara & Daftar Hadir</h5>
@if($ruang)
<div class="alert alert-info py-2 mb-3">
    <i class="bi bi-geo-alt-fill me-1"></i> Ruang: <strong>{{ $ruang->nama }}</strong> ({{ $ruang->kode }})
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Ujian</th>
                        <th>Mapel</th>
                        <th>Status Ujian</th>
                        <th>Berita Acara</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ujianList as $i => $u)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $u->nama }}</td>
                        <td>{{ $u->mapel->nama ?? '-' }}</td>
                        <td>
                            @if($u->status == 'selesai')
                                <span class="badge bg-dark">Selesai</span>
                            @elseif($u->status == 'berlangsung')
                                <span class="badge bg-warning">Berlangsung</span>
                            @else
                                <span class="badge bg-success">Publish</span>
                            @endif
                        </td>
                        <td>
                            @if($beritaAcaraMap->has($u->id))
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Sudah diisi</span>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-dash-circle"></i> Belum diisi</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('proktor.berita-acara.create', $u) }}" class="btn btn-sm {{ $beritaAcaraMap->has($u->id) ? 'btn-outline-info' : 'btn-info' }}">
                                    <i class="bi bi-file-text"></i> {{ $beritaAcaraMap->has($u->id) ? 'Edit' : 'Isi' }} Berita Acara
                                </a>
                                @if($beritaAcaraMap->has($u->id))
                                <a href="{{ route('proktor.daftar-hadir.show', $u) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-person-lines-fill"></i> Daftar Hadir
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada ujian</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
