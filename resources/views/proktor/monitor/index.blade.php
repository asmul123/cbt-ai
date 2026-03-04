@extends('layouts.app')
@section('title', 'Monitor Ujian')

@section('content')
<h5 class="mb-3">Daftar Ujian untuk Monitoring</h5>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
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
                            @if($u->status == 'aktif') <span class="badge bg-success">Aktif</span>
                            @else <span class="badge bg-dark">Selesai</span>
                            @endif
                        </td>
                        <td><span class="badge bg-info">{{ $u->peserta->count() }}</span></td>
                        <td>
                            <a href="{{ route('proktor.monitor.show', $u) }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-display"></i> Monitor
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada ujian</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
