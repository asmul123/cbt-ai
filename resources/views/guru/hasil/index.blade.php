@extends('layouts.app')
@section('title', 'Hasil Ujian')

@section('content')
<h5 class="mb-3">Hasil Ujian</h5>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Nama Ujian</th>
                        <th>Mapel</th>
                        <th>Peserta</th>
                        <th>Rata-rata</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ujian as $i => $u)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $u->nama }}</td>
                        <td>{{ $u->mapel->nama ?? '-' }}</td>
                        <td><span class="badge bg-info">{{ $u->peserta_count ?? $u->peserta->count() }}</span></td>
                        <td>
                            @php $avg = $u->hasilUjian->avg('nilai_akhir'); @endphp
                            <span class="fw-bold {{ $avg >= 75 ? 'text-success' : 'text-danger' }}">{{ number_format($avg, 1) }}</span>
                        </td>
                        <td>
                            @if($u->status == 'aktif') <span class="badge bg-success">Aktif</span>
                            @elseif($u->status == 'selesai') <span class="badge bg-dark">Selesai</span>
                            @else <span class="badge bg-secondary">Draft</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('guru.hasil.show', $u) }}" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i> Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada hasil ujian</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
