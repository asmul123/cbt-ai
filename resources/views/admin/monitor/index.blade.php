@extends('layouts.app')
@section('title', 'Monitor Ujian')

@section('content')
<h5 class="mb-3"><i class="bi bi-display"></i> Monitor Ujian - Seluruh Ruangan</h5>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Ringkasan Per Ruang -->
<div class="row g-3 mb-4">
    @foreach($ruangList as $r)
    <div class="col-md-3">
        <div class="card border-start border-3 border-primary">
            <div class="card-body py-2">
                <div class="fw-semibold">{{ $r->nama }}</div>
                <small class="text-muted">{{ $r->kode }}</small>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header"><h6 class="mb-0">Daftar Ujian</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Nama Ujian</th>
                        <th>Mapel</th>
                        <th>Token</th>
                        <th>Status</th>
                        <th>Peserta</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ujianList as $i => $u)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $u->nama }}</td>
                        <td>{{ $u->mapel->nama ?? '-' }}</td>
                        <td><code class="fs-6">{{ $u->token ?? '-' }}</code></td>
                        <td>
                            @if($u->status == 'publish') <span class="badge bg-primary">Publish</span>
                            @elseif($u->status == 'berlangsung') <span class="badge bg-warning">Berlangsung</span>
                            @else <span class="badge bg-dark">Selesai</span>
                            @endif
                        </td>
                        <td><span class="badge bg-info">{{ $u->peserta_count }}</span></td>
                        <td>
                            <a href="{{ route('admin.monitor.show', $u) }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-display"></i> Monitor
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada ujian aktif</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
