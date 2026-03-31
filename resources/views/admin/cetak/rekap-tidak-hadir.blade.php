@extends('layouts.app')
@section('title', 'Rekap Siswa Tidak Hadir Per Mapel')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-person-x"></i> Rekapitulasi Siswa Tidak Hadir Per Mata Pelajaran</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.cetak.cetakRekapTidakHadir') }}" class="btn btn-danger btn-sm">
            <i class="bi bi-file-pdf"></i> Cetak PDF
        </a>
        <a href="{{ route('admin.cetak.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
</div>

@if($rekapPerMapel->isEmpty())
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-check-circle" style="font-size:2rem;"></i>
        <p class="mt-2">Tidak ada siswa yang tidak hadir di semua ujian.</p>
    </div>
</div>
@else
    @foreach($rekapPerMapel as $mapelNama => $ujianGroup)
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-book"></i> {{ $mapelNama }}</h6>
        </div>
        <div class="card-body p-0">
            @foreach($ujianGroup as $ujianNama => $pesertaList)
            <div class="px-3 pt-2 pb-1">
                <span class="badge bg-primary">{{ $ujianNama }}</span>
                <span class="badge bg-danger">{{ $pesertaList->count() }} tidak hadir</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Ruangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pesertaList as $i => $p)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $p->siswa->nis ?? '-' }}</td>
                            <td class="fw-semibold">{{ $p->siswa->nama ?? '-' }}</td>
                            <td>{{ $p->siswa->kelas->nama ?? '-' }}</td>
                            <td>{{ $p->ruangUjian->nama ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    {{-- Ringkasan --}}
    <div class="card">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Ringkasan</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Mata Pelajaran</th>
                            <th>Jumlah Ujian</th>
                            <th>Total Tidak Hadir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; $grandTotal = 0; @endphp
                        @foreach($rekapPerMapel as $mapelNama => $ujianGroup)
                        @php
                            $totalPerMapel = $ujianGroup->flatten(1)->count();
                            $grandTotal += $totalPerMapel;
                        @endphp
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td class="fw-semibold">{{ $mapelNama }}</td>
                            <td>{{ $ujianGroup->count() }}</td>
                            <td><span class="badge bg-danger">{{ $totalPerMapel }}</span></td>
                        </tr>
                        @endforeach
                        <tr class="table-warning fw-bold">
                            <td colspan="3" class="text-end">Grand Total</td>
                            <td><span class="badge bg-dark">{{ $grandTotal }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
