@extends('layouts.app')
@section('title', 'Dashboard Admin')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="text-muted small">Total Siswa</div>
                    <div class="fs-4 fw-bold">{{ $data['totalSiswa'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-success bg-opacity-10 text-success"><i class="bi bi-person-badge-fill"></i></div>
                <div>
                    <div class="text-muted small">Total Guru</div>
                    <div class="fs-4 fw-bold">{{ $data['totalGuru'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-clipboard-check-fill"></i></div>
                <div>
                    <div class="text-muted small">Total Ujian</div>
                    <div class="fs-4 fw-bold">{{ $data['totalUjian'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-lightning-fill"></i></div>
                <div>
                    <div class="text-muted small">Ujian Aktif</div>
                    <div class="fs-4 fw-bold">{{ $data['totalUjianAktif'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-info bg-opacity-10 text-info"><i class="bi bi-building"></i></div>
                <div>
                    <div class="text-muted small">Jurusan</div>
                    <div class="fs-4 fw-bold">{{ $data['totalJurusan'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-door-open-fill"></i></div>
                <div>
                    <div class="text-muted small">Kelas</div>
                    <div class="fs-4 fw-bold">{{ $data['totalKelas'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-dark bg-opacity-10 text-dark"><i class="bi bi-book-fill"></i></div>
                <div>
                    <div class="text-muted small">Mata Pelajaran</div>
                    <div class="fs-4 fw-bold">{{ $data['totalMapel'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-success bg-opacity-10 text-success"><i class="bi bi-graph-up"></i></div>
                <div>
                    <div class="text-muted small">Rata-rata Nilai</div>
                    <div class="fs-4 fw-bold">{{ number_format($data['nilaiRataRata'], 1) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-clock-history"></i> Ujian Terbaru</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama Ujian</th>
                        <th>Mata Pelajaran</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['ujianTerbaru'] as $u)
                    <tr>
                        <td class="fw-semibold">{{ $u->nama }}</td>
                        <td>{{ $u->mapel->nama ?? '-' }}</td>
                        <td>
                            @if($u->status == 'aktif')
                                <span class="badge bg-success">Aktif</span>
                            @elseif($u->status == 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @else
                                <span class="badge bg-dark">Selesai</span>
                            @endif
                        </td>
                        <td>{{ $u->tanggal_mulai ? \Carbon\Carbon::parse($u->tanggal_mulai)->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada ujian</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
