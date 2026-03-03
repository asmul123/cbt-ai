@extends('layouts.app')
@section('title', 'Dashboard Guru')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-question-circle-fill"></i></div>
                <div>
                    <div class="text-muted small">Total Soal</div>
                    <div class="fs-4 fw-bold">{{ $data['totalSoal'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-success bg-opacity-10 text-success"><i class="bi bi-clipboard-check-fill"></i></div>
                <div>
                    <div class="text-muted small">Total Ujian</div>
                    <div class="fs-4 fw-bold">{{ $data['totalUjian'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-lightning-fill"></i></div>
                <div>
                    <div class="text-muted small">Ujian Aktif</div>
                    <div class="fs-4 fw-bold">{{ $data['ujianAktif'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-pie-chart"></i> Soal per Tipe</h6></div>
            <div class="card-body">
                <canvas id="chartSoalTipe" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-clock-history"></i> Ujian Terbaru</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['ujianTerbaru'] as $u)
                            <tr>
                                <td class="fw-semibold">{{ $u->nama }}</td>
                                <td>
                                    @if($u->status == 'aktif')
                                        <span class="badge bg-success">Aktif</span>
                                    @elseif($u->status == 'draft')
                                        <span class="badge bg-secondary">Draft</span>
                                    @else
                                        <span class="badge bg-dark">Selesai</span>
                                    @endif
                                </td>
                                <td>{{ $u->tanggal_mulai ? \Carbon\Carbon::parse($u->tanggal_mulai)->format('d/m/Y') : '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">Belum ada ujian</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const soalData = @json($data['soalPerTipe']);
    const labels = Object.keys(soalData).map(k => k.toUpperCase());
    const values = Object.values(soalData);
    new Chart(document.getElementById('chartSoalTipe'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
</script>
@endpush
