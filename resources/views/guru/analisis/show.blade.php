@extends('layouts.app')
@section('title', 'Analisis: ' . $ujian->nama)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Analisis: {{ $ujian->nama }}</h5>
    <a href="{{ route('guru.analisis.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
</div>

<!-- Statistik Umum -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Peserta</div>
                <div class="fs-4 fw-bold">{{ $statistik['jumlah_peserta'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Rata-rata</div>
                <div class="fs-4 fw-bold text-primary">{{ number_format($statistik['rata_rata'] ?? 0, 1) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Std. Deviasi</div>
                <div class="fs-4 fw-bold text-warning">{{ number_format($statistik['std_deviasi'] ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Nilai Tertinggi</div>
                <div class="fs-4 fw-bold text-success">{{ number_format($statistik['nilai_tertinggi'] ?? 0, 1) }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Distribusi Nilai Chart -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-bar-chart"></i> Distribusi Nilai</h6></div>
            <div class="card-body">
                <canvas id="chartDistribusi" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-trophy"></i> Top 10 Ranking</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Nama</th><th>Nilai</th></tr>
                        </thead>
                        <tbody>
                            @foreach(collect($ranking)->take(10) as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $r['nama'] ?? '-' }}</td>
                                <td class="fw-bold">{{ number_format($r['nilai'] ?? 0, 1) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analisis Butir Soal -->
<div class="card">
    <div class="card-header"><h6 class="mb-0"><i class="bi bi-graph-up"></i> Analisis Per Butir Soal</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Soal</th>
                        <th>Tipe</th>
                        <th>Tingkat Kesukaran (P)</th>
                        <th>Kategori</th>
                        <th>Daya Pembeda (D)</th>
                        <th>Kategori</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analisis as $i => $a)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ Str::limit(strip_tags($a['soal'] ?? ''), 60) }}</td>
                        <td><span class="badge bg-secondary">{{ strtoupper($a['tipe'] ?? '') }}</span></td>
                        <td>
                            <div class="progress" style="height: 20px; min-width: 80px;">
                                @php $p = $a['tingkat_kesukaran'] ?? 0; @endphp
                                <div class="progress-bar {{ $p > 0.7 ? 'bg-success' : ($p > 0.3 ? 'bg-warning' : 'bg-danger') }}"
                                     style="width: {{ $p * 100 }}%">{{ number_format($p, 2) }}</div>
                            </div>
                        </td>
                        <td>
                            @if(($a['tingkat_kesukaran'] ?? 0) > 0.7) <span class="badge bg-success">Mudah</span>
                            @elseif(($a['tingkat_kesukaran'] ?? 0) > 0.3) <span class="badge bg-warning">Sedang</span>
                            @else <span class="badge bg-danger">Sulit</span>
                            @endif
                        </td>
                        <td>
                            @php $d = $a['daya_pembeda'] ?? 0; @endphp
                            <span class="fw-bold {{ $d >= 0.4 ? 'text-success' : ($d >= 0.2 ? 'text-warning' : 'text-danger') }}">
                                {{ number_format($d, 2) }}
                            </span>
                        </td>
                        <td>
                            @if($d >= 0.4) <span class="badge bg-success">Sangat Baik</span>
                            @elseif($d >= 0.3) <span class="badge bg-primary">Baik</span>
                            @elseif($d >= 0.2) <span class="badge bg-warning">Cukup</span>
                            @else <span class="badge bg-danger">Buruk</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Distribusi Nilai Chart
    const distribusi = @json($statistik['distribusi'] ?? []);
    new Chart(document.getElementById('chartDistribusi'), {
        type: 'bar',
        data: {
            labels: Object.keys(distribusi),
            datasets: [{
                label: 'Jumlah Siswa',
                data: Object.values(distribusi),
                backgroundColor: '#3b82f6'
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
            plugins: { legend: { display: false } }
        }
    });
</script>
@endpush
