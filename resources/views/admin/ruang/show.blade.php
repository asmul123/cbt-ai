@extends('layouts.app')
@section('title', 'Kelola Ruang: ' . $ruang->nama)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">Kelola Ruang: {{ $ruang->nama }}</h5>
        <small class="text-muted">Kode: <code>{{ $ruang->kode }}</code> | Kapasitas: {{ $ruang->kapasitas }} siswa | Lokasi: {{ $ruang->lokasi ?? '-' }}</small>
    </div>
    <a href="{{ route('admin.ruang.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
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

<!-- Info Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Total Siswa</div>
                <div class="fs-3 fw-bold text-primary">{{ $ruang->siswa->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Kapasitas</div>
                <div class="fs-3 fw-bold text-info">{{ $ruang->kapasitas }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Jadwal Ujian</div>
                <div class="fs-3 fw-bold text-warning">{{ $ruang->ujian->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Proktor</div>
                <div class="fs-3 fw-bold text-success">{{ $ruang->proktor->count() }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3" id="ruangTabs">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tabSiswa"><i class="bi bi-people"></i> Siswa ({{ $ruang->siswa->count() }})</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tabUjian"><i class="bi bi-clipboard-check"></i> Jadwal Ujian ({{ $ruang->ujian->count() }})</a>
    </li>
</ul>

<div class="tab-content">
    <!-- Tab Siswa -->
    <div class="tab-pane fade show active" id="tabSiswa">
        <!-- Form Tambah Siswa -->
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-plus-circle"></i> Tambah Siswa ke Ruang</h6></div>
            <div class="card-body">
                <ul class="nav nav-pills mb-3" id="addSiswaMode">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="pill" href="#modeKelas">Per Kelas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#modeIndividual">Individual</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Mode: Per Kelas -->
                    <div class="tab-pane fade show active" id="modeKelas">
                        <form action="{{ route('admin.ruang.addSiswa', $ruang) }}" method="POST">
                            @csrf
                            <input type="hidden" name="mode" value="kelas">
                            <div class="row align-items-end">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Pilih Kelas</label>
                                    <select name="kelas_id" class="form-select" required>
                                        <option value="">-- Pilih Kelas --</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id }}">{{ $k->nama }} ({{ $k->jurusan->nama ?? '-' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-lg"></i> Tambah Semua Siswa Kelas
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">Hanya siswa yang belum memiliki ruang yang akan ditambahkan</small>
                        </form>
                    </div>

                    <!-- Mode: Individual -->
                    <div class="tab-pane fade" id="modeIndividual">
                        <form action="{{ route('admin.ruang.addSiswa', $ruang) }}" method="POST">
                            @csrf
                            <input type="hidden" name="mode" value="individual">
                            <div class="row align-items-end mb-2">
                                <div class="col-md-4">
                                    <label class="form-label">Filter Kelas</label>
                                    <select id="filterKelasIndiv" class="form-select">
                                        <option value="">-- Pilih Kelas --</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" id="btnLoadSiswa" class="btn btn-outline-primary mt-4">
                                        <i class="bi bi-search"></i> Cari Siswa
                                    </button>
                                </div>
                            </div>
                            <div id="siswaListContainer" class="border rounded p-2 mb-2" style="max-height: 300px; overflow-y: auto; display: none;">
                                <!-- Diisi via JS -->
                            </div>
                            <button type="submit" class="btn btn-primary" id="btnAddIndividual" style="display: none;">
                                <i class="bi bi-plus-lg"></i> Tambah Siswa Terpilih
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Siswa -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-people-fill"></i> Daftar Siswa di Ruang Ini</h6>
                @if($ruang->siswa->count() > 0)
                <form action="{{ route('admin.ruang.clearSiswa', $ruang) }}" method="POST"
                      onsubmit="return confirm('Keluarkan SEMUA siswa dari ruang ini?')">
                    @csrf
                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle"></i> Keluarkan Semua</button>
                </form>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ruang->siswa as $i => $s)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td><code>{{ $s->nis }}</code></td>
                                <td class="fw-semibold">{{ $s->nama }}</td>
                                <td>{{ $s->kelas->nama ?? '-' }}</td>
                                <td>
                                    <form action="{{ route('admin.ruang.removeSiswa', [$ruang, $s]) }}" method="POST"
                                          onsubmit="return confirm('Keluarkan siswa {{ $s->nama }} dari ruang?')">
                                        @csrf
                                        <button class="btn btn-outline-danger btn-sm" title="Keluarkan dari ruang">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Belum ada siswa di ruang ini</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Jadwal Ujian -->
    <div class="tab-pane fade" id="tabUjian">
        <div class="mb-3">
            <a href="{{ route('admin.ujian.create', ['ruang_id' => $ruang->id]) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Buat Ujian Baru untuk Ruang Ini
            </a>
        </div>

        <!-- Form Tambah Ujian Existing -->
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-plus-circle"></i> Tambah Jadwal Ujian yang Sudah Ada</h6></div>
            <div class="card-body">
                <form action="{{ route('admin.ruang.addUjian', $ruang) }}" method="POST">
                    @csrf
                    <div class="row align-items-end">
                        <div class="col-md-8 mb-2">
                            <label class="form-label">Pilih Ujian</label>
                            <select name="ujian_id" class="form-select" required>
                                <option value="">-- Pilih Ujian --</option>
                                @foreach($ujianAvailable as $u)
                                    <option value="{{ $u->id }}">{{ $u->nama_ujian }} ({{ $u->mapel->nama ?? '-' }}) - {{ ucfirst($u->status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i> Tambah Jadwal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Daftar Ujian -->
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-clipboard-check"></i> Jadwal Ujian di Ruang Ini</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Nama Ujian</th>
                                <th>Mata Pelajaran</th>
                                <th>Tanggal</th>
                                <th>Durasi</th>
                                <th>Status</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ruang->ujian as $i => $u)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $u->nama_ujian }}</td>
                                <td>{{ $u->mapel->nama ?? '-' }}</td>
                                <td><small>{{ $u->tanggal_mulai?->format('d/m/Y H:i') }}</small></td>
                                <td>{{ $u->durasi }} menit</td>
                                <td>
                                    @if($u->status == 'draft') <span class="badge bg-secondary">Draft</span>
                                    @elseif($u->status == 'publish') <span class="badge bg-primary">Publish</span>
                                    @elseif($u->status == 'berlangsung') <span class="badge bg-warning">Berlangsung</span>
                                    @else <span class="badge bg-dark">Selesai</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.ruang.removeUjian', [$ruang, $u]) }}" method="POST"
                                          onsubmit="return confirm('Hapus jadwal ujian {{ $u->nama_ujian }} dari ruang ini?')">
                                        @csrf
                                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-x-lg"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada jadwal ujian</td></tr>
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
document.getElementById('btnLoadSiswa').addEventListener('click', function() {
    const kelasId = document.getElementById('filterKelasIndiv').value;
    if (!kelasId) { alert('Pilih kelas terlebih dahulu'); return; }

    fetch('{{ route("admin.ruang.siswaByKelas") }}?kelas_id=' + kelasId)
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('siswaListContainer');
            container.style.display = 'block';
            document.getElementById('btnAddIndividual').style.display = 'inline-block';

            if (data.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-3">Semua siswa di kelas ini sudah memiliki ruang</div>';
                return;
            }

            let html = '<div class="mb-2"><small class="text-muted">' + data.length + ' siswa ditemukan (belum memiliki ruang)</small></div>';
            data.forEach(s => {
                html += `<div class="form-check">
                    <input class="form-check-input" type="checkbox" name="siswa_ids[]" value="${s.id}" id="siswa${s.id}" checked>
                    <label class="form-check-label" for="siswa${s.id}">${s.nis} - ${s.nama} (${s.kelas})</label>
                </div>`;
            });
            container.innerHTML = html;
        })
        .catch(() => alert('Gagal memuat data siswa'));
});
</script>
@endpush
