@extends('layouts.app')
@section('title', 'Monitor: ' . $ujian->nama)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">Monitor: {{ $ujian->nama }}</h5>
        <small class="text-muted">
            Token: <code class="fs-5">{{ $ujian->token }}</code>
            @if($currentRuang)
                | Ruang: <strong>{{ $currentRuang->nama }}</strong>
            @else
                | Semua Ruangan
            @endif
            | Auto refresh setiap 10 detik
        </small>
    </div>
    <a href="{{ route('admin.monitor.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Filter Ruang -->
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="fw-semibold me-2"><i class="bi bi-funnel"></i> Filter Ruang:</span>
            <a href="{{ route('admin.monitor.show', $ujian) }}" class="btn btn-sm {{ !$ruangFilter ? 'btn-primary' : 'btn-outline-primary' }}">
                Semua
            </a>
            @foreach($ruangList as $r)
            <a href="{{ route('admin.monitor.show', [$ujian, 'ruang' => $r->id]) }}"
               class="btn btn-sm {{ $ruangFilter == $r->id ? 'btn-primary' : 'btn-outline-primary' }}">
                {{ $r->nama }}
            </a>
            @endforeach
            <a href="{{ route('admin.monitor.show', [$ujian, 'ruang' => 'none']) }}"
               class="btn btn-sm {{ $ruangFilter === 'none' ? 'btn-warning' : 'btn-outline-warning' }}">
                Tanpa Ruang
            </a>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4" id="statsArea">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Total Peserta</div>
                <div class="fs-3 fw-bold text-primary" id="statTotal">{{ $stats['total'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Sedang Mengerjakan</div>
                <div class="fs-3 fw-bold text-warning" id="statMengerjakan">{{ $stats['mengerjakan'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Selesai</div>
                <div class="fs-3 fw-bold text-success" id="statSelesai">{{ $stats['selesai'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Belum Mulai</div>
                <div class="fs-3 fw-bold text-secondary" id="statBelum">{{ $stats['belum_mulai'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Peserta Table -->
<div class="card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="bi bi-people"></i> Detail Peserta</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Ruang</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Pelanggaran</th>
                        <th>Sisa Waktu</th>
                        <th>IP Address</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="pesertaTable">
                    @foreach($peserta as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $p->siswa->nis ?? '-' }}</td>
                        <td class="fw-semibold">{{ $p->siswa->user->name ?? '-' }}</td>
                        <td>{{ $p->siswa->kelas->nama ?? '-' }}</td>
                        <td>
                            @if($p->ruangUjian)
                                <span class="badge bg-info">{{ $p->ruangUjian->nama }}</span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            @if($p->status == 'mengerjakan')
                                <span class="badge bg-warning">Mengerjakan</span>
                            @elseif($p->status == 'selesai')
                                <span class="badge bg-success">Selesai</span>
                            @else
                                <span class="badge bg-secondary">Belum Mulai</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $total = $ujian->soal_count;
                                $answered = $p->jawabanSiswa->count();
                                $pct = $total > 0 ? ($answered / $total) * 100 : 0;
                            @endphp
                            <div class="progress" style="height: 18px; min-width: 80px;">
                                <div class="progress-bar {{ $pct == 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $pct }}%">
                                    {{ $answered }}/{{ $total }}
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($p->jumlah_pelanggaran > 0)
                                <span class="badge bg-danger">{{ $p->jumlah_pelanggaran }}x</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td>
                            @if($p->status == 'mengerjakan')
                                <span class="text-warning fw-semibold">{{ floor($p->sisaWaktu() / 60) }}m</span>
                            @else
                                -
                            @endif
                        </td>
                        <td><small>{{ $p->ip_address ?? '-' }}</small></td>
                        <td>
                            <div class="d-flex gap-1">
                                @if($p->status == 'selesai')
                                    <form action="{{ route('admin.monitor.buka', [$ujian, $p]) }}" method="POST"
                                          onsubmit="return confirm('Buka kembali ujian {{ $p->siswa->user->name ?? 'siswa' }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success btn-sm" title="Buka kembali">
                                            <i class="bi bi-unlock"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.monitor.reset', [$ujian, $p]) }}" method="POST"
                                          onsubmit="return confirm('Reset pekerjaan {{ $p->siswa->user->name ?? 'siswa' }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning btn-sm" title="Reset">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($p->status == 'mengerjakan')
                                    <form action="{{ route('admin.monitor.selesaikan', [$ujian, $p]) }}" method="POST"
                                          onsubmit="return confirm('Selesaikan ujian {{ $p->siswa->user->name ?? 'siswa' }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary btn-sm" title="Selesaikan">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($p->status != 'belum_mulai')
                                    <form action="{{ route('admin.monitor.hapus', [$ujian, $p]) }}" method="POST"
                                          onsubmit="return confirm('HAPUS pengerjaan {{ $p->siswa->user->name ?? 'siswa' }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus pengerjaan">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Log Aktivitas -->
<div class="card">
    <div class="card-header"><h6 class="mb-0"><i class="bi bi-shield-exclamation"></i> Log Aktivitas Mencurigakan</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr><th>Waktu</th><th>Siswa</th><th>Aktivitas</th><th>Detail</th></tr>
                </thead>
                <tbody>
                    @forelse($log as $l)
                    <tr class="{{ in_array($l->aktivitas, ['tab_switch','window_blur','exit_fullscreen']) ? 'table-danger' : '' }}">
                        <td><small>{{ $l->created_at->format('H:i:s') }}</small></td>
                        <td>{{ $l->user->name ?? '-' }}</td>
                        <td><span class="badge bg-danger">{{ $l->aktivitas }}</span></td>
                        <td><small>{{ $l->keterangan }}</small></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada log mencurigakan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
setInterval(function() {
    const url = '{{ route("admin.monitor.data", $ujian) }}' + '{{ $ruangFilter ? "?ruang=" . $ruangFilter : "" }}';
    fetch(url)
        .then(r => r.json())
        .then(data => {
            document.getElementById('statTotal').textContent = data.stats.total || 0;
            document.getElementById('statMengerjakan').textContent = data.stats.mengerjakan || 0;
            document.getElementById('statSelesai').textContent = data.stats.selesai || 0;
            document.getElementById('statBelum').textContent = data.stats.belum_mulai || 0;
        })
        .catch(() => {});
}, 10000);
</script>
@endpush
