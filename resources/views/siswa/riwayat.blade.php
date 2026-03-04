@extends('layouts.app')
@section('title', 'Riwayat Ujian')

@section('content')
<h5 class="mb-3">Riwayat Ujian</h5>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Ujian</th>
                        <th>Mapel</th>
                        <th>Tanggal</th>
                        <th>Benar</th>
                        <th>Salah</th>
                        <th>Nilai</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hasil as $i => $h)
                    <tr>
                        <td>{{ $hasil->firstItem() + $i }}</td>
                        <td class="fw-semibold">{{ $h->ujian->nama ?? '-' }}</td>
                        <td>{{ $h->ujian->mapel->nama ?? '-' }}</td>
                        <td>{{ $h->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-success">{{ $h->benar_pg ?? 0 }}</td>
                        <td class="text-muted">{{ ($h->jumlah_soal ?? 0) - ($h->benar_pg ?? 0) }}</td>
                        <td>
                            @if($h->ujian && $h->ujian->tampilkan_nilai)
                                <span class="fw-bold fs-5 {{ $h->nilai_akhir >= ($h->ujian->kkm ?? 75) ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($h->nilai_akhir, 1) }}
                                </span>
                            @else
                                <span class="text-muted"><i class="bi bi-eye-slash"></i> Disembunyikan</span>
                            @endif
                        </td>
                        <td>
                            @if($h->status == 'selesai') <span class="badge bg-success">Selesai</span>
                            @elseif($h->status == 'belum_dinilai') <span class="badge bg-warning">Belum Final</span>
                            @else <span class="badge bg-secondary">{{ $h->status }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada riwayat ujian</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($hasil->hasPages())
    <div class="card-footer">{{ $hasil->links() }}</div>
    @endif
</div>
@endsection
