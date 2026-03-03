@extends('layouts.app')
@section('title', 'Import Data')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0"><i class="bi bi-cloud-upload"></i> Import Data dari Excel</h5>
</div>

{{-- Hasil Import --}}
@if(session('result'))
    @php $result = session('result'); $importType = session('import_type', 'Data'); @endphp
    <div class="alert {{ $result['failed'] > 0 ? 'alert-warning' : 'alert-success' }} alert-dismissible fade show" role="alert">
        <h6 class="alert-heading fw-bold"><i class="bi bi-check-circle"></i> Hasil Import {{ $importType }}</h6>
        <div class="d-flex gap-4 mb-2">
            <span><i class="bi bi-check-lg text-success"></i> Berhasil: <strong>{{ $result['success'] }}</strong></span>
            @if(isset($result['updated']) && $result['updated'] > 0)
                <span><i class="bi bi-arrow-repeat text-info"></i> Diperbarui: <strong>{{ $result['updated'] }}</strong></span>
            @endif
            <span><i class="bi bi-x-lg text-danger"></i> Gagal: <strong>{{ $result['failed'] }}</strong></span>
        </div>
        @if(!empty($result['errors']))
            <hr>
            <details>
                <summary class="text-danger" style="cursor:pointer">Lihat detail error ({{ count($result['errors']) }})</summary>
                <ul class="mb-0 mt-2 small">
                    @foreach($result['errors'] as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </details>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Hasil Reset --}}
@if(session('reset_result'))
    @php $rr = session('reset_result'); @endphp
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-trash"></i> <strong>Reset {{ $rr['type'] }} berhasil.</strong> {{ $rr['deleted'] }} data dihapus.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    {{-- Import Kelas --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-door-open me-2"></i>Import Kelas</span>
                <a href="{{ route('admin.import.template', 'kelas') }}" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-download"></i> Template
                </a>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Format kolom: <strong>Nama Kelas</strong> | <strong>Tingkat</strong> (X/XI/XII) | <strong>Jurusan</strong> (kode/nama) | <strong>Tahun Ajaran</strong>
                </p>
                <form action="{{ route('admin.import.kelas') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="input-group">
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Import
                        </button>
                    </div>
                    @error('file') <small class="text-danger">{{ $message }}</small> @enderror
                </form>
            </div>
            <div class="card-footer bg-light">
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> Jika kelas sudah ada (nama + jurusan + tahun ajaran sama), data akan diperbarui.
                </small>
            </div>
        </div>
    </div>

    {{-- Import Ruang Ujian --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-geo-alt me-2"></i>Import Ruang Ujian</span>
                <a href="{{ route('admin.import.template', 'ruang') }}" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-download"></i> Template
                </a>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Format kolom: <strong>Kode Ruang</strong> | <strong>Nama Ruang</strong> | <strong>Kapasitas</strong> | <strong>Lokasi</strong>
                </p>
                <form action="{{ route('admin.import.ruang') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="input-group">
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-upload"></i> Import
                        </button>
                    </div>
                    @error('file') <small class="text-danger">{{ $message }}</small> @enderror
                </form>
            </div>
            <div class="card-footer bg-light">
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> Jika kode ruang sudah ada, data akan diperbarui.
                </small>
            </div>
        </div>
    </div>

    {{-- Import Guru --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-person-badge me-2"></i>Import Guru</span>
                <a href="{{ route('admin.import.template', 'guru') }}" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-download"></i> Template
                </a>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Format kolom: <strong>NIP</strong> | <strong>Nama</strong> | <strong>Username</strong> | <strong>Email</strong> | <strong>Mapel</strong> (kode/nama) | <strong>No HP</strong> | <strong>Alamat</strong> | <strong>Password</strong>
                </p>
                <form action="{{ route('admin.import.guru') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="input-group">
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-upload"></i> Import
                        </button>
                    </div>
                    @error('file') <small class="text-danger">{{ $message }}</small> @enderror
                </form>
            </div>
            <div class="card-footer bg-light">
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> NIP yang sudah ada akan diperbarui. Username/email jika kosong otomatis dari NIP. Password default = NIP.
                </small>
            </div>
        </div>
    </div>

    {{-- Import Proktor --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #6f42c1;">
                <span><i class="bi bi-person-workspace me-2"></i>Import Proktor</span>
                <a href="{{ route('admin.import.template', 'proktor') }}" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-download"></i> Template
                </a>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Format kolom: <strong>Nama</strong> | <strong>Username</strong> | <strong>Kode Ruang</strong> | <strong>Password</strong>
                </p>
                <form action="{{ route('admin.import.proktor') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="input-group">
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <button type="submit" class="btn text-white" style="background-color: #6f42c1;">
                            <i class="bi bi-upload"></i> Import
                        </button>
                    </div>
                    @error('file') <small class="text-danger">{{ $message }}</small> @enderror
                </form>
            </div>
            <div class="card-footer bg-light">
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> Username yang sudah ada (proktor) akan diperbarui. Password default = username. Kode ruang opsional.
                </small>
            </div>
        </div>
    </div>

    {{-- Import Siswa --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2"></i>Import Siswa</span>
                <a href="{{ route('admin.import.template', 'siswa') }}" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-download"></i> Template
                </a>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Format kolom: <strong>NIS</strong> | <strong>NISN</strong> | <strong>Nama</strong> | <strong>JK</strong> (L/P) | <strong>Kelas</strong> | <strong>Jurusan</strong> | <strong>No HP</strong> | <strong>Alamat</strong>
                </p>
                <form action="{{ route('admin.import.siswa') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="input-group">
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <button type="submit" class="btn btn-info text-white">
                            <i class="bi bi-upload"></i> Import
                        </button>
                    </div>
                    @error('file') <small class="text-danger">{{ $message }}</small> @enderror
                </form>
            </div>
            <div class="card-footer bg-light">
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> Kelas & Jurusan harus sudah ada. NIS yang sudah terdaftar akan diperbarui. Akun user otomatis dibuat (password = NIS).
                </small>
            </div>
        </div>
    </div>

    {{-- Import Distribusi Ruang --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <span><i class="bi bi-diagram-3 me-2"></i>Import Distribusi Ruang</span>
                <a href="{{ route('admin.import.template', 'distribusi') }}" class="btn btn-sm btn-outline-dark">
                    <i class="bi bi-download"></i> Template
                </a>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Assign siswa ke ruang ujian secara massal lewat Excel.</p>

                <form action="{{ route('admin.import.distribusi') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mode Distribusi</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode" value="siswa" id="modeSiswa" checked>
                                <label class="form-check-label" for="modeSiswa">
                                    Per Siswa <small class="text-muted">(NIS | Kode Ruang)</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode" value="kelas" id="modeKelas">
                                <label class="form-check-label" for="modeKelas">
                                    Per Kelas <small class="text-muted">(Nama Kelas | Kode Ruang)</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="input-group">
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-upload"></i> Import
                        </button>
                    </div>
                    @error('file') <small class="text-danger">{{ $message }}</small> @enderror
                    @error('mode') <small class="text-danger">{{ $message }}</small> @enderror
                </form>
            </div>
            <div class="card-footer bg-light">
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> Siswa, Kelas & Ruang harus sudah ada. Kapasitas ruang akan diperiksa otomatis.
                </small>
            </div>
        </div>
    </div>
</div>

{{-- Reset Data --}}
<div class="card mt-4 border-danger">
    <div class="card-header bg-danger text-white">
        <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-1"></i> Reset / Hapus Semua Data</h6>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            <strong>Perhatian!</strong> Tombol reset akan <strong>menghapus semua data</strong> pada kategori yang dipilih secara permanen.
            Aksi ini tidak dapat dibatalkan. Gunakan dengan hati-hati.
        </p>
        <div class="d-flex flex-wrap gap-2">
            @php
                $resetItems = [
                    ['type' => 'siswa', 'label' => 'Siswa', 'icon' => 'bi-people', 'desc' => 'Hapus semua siswa beserta akun user-nya'],
                    ['type' => 'guru', 'label' => 'Guru', 'icon' => 'bi-person-badge', 'desc' => 'Hapus semua guru beserta akun user-nya'],
                    ['type' => 'proktor', 'label' => 'Proktor', 'icon' => 'bi-person-workspace', 'desc' => 'Hapus semua akun proktor'],
                    ['type' => 'ruang', 'label' => 'Ruang Ujian', 'icon' => 'bi-geo-alt', 'desc' => 'Hapus semua ruang dan kosongkan penempatan siswa/proktor'],
                    ['type' => 'kelas', 'label' => 'Kelas', 'icon' => 'bi-door-open', 'desc' => 'Hapus semua kelas (siswa terkait ikut terhapus!)'],
                ];
            @endphp
            @foreach($resetItems as $item)
                <form action="{{ route('admin.import.reset', $item['type']) }}" method="POST"
                      onsubmit="return confirm('PERINGATAN!\n\n{{ $item['desc'] }}.\n\nApakah Anda yakin ingin melanjutkan?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi {{ $item['icon'] }}"></i> Reset {{ $item['label'] }}
                    </button>
                </form>
            @endforeach
        </div>
    </div>
</div>

{{-- Panduan --}}
<div class="card mt-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-question-circle me-1"></i> Panduan Import</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold">Urutan Import yang Disarankan</h6>
                <ol class="mb-3">
                    <li><strong>Kelas</strong> — Buat data kelas dan jurusan terlebih dahulu</li>
                    <li><strong>Ruang Ujian</strong> — Buat data ruang ujian</li>
                    <li><strong>Guru</strong> — Import guru (mapel harus sudah ada jika diisi)</li>
                    <li><strong>Proktor</strong> — Import proktor (kode ruang opsional)</li>
                    <li><strong>Siswa</strong> — Import siswa (membutuhkan kelas & jurusan)</li>
                    <li><strong>Distribusi Ruang</strong> — Assign siswa ke ruang (membutuhkan siswa & ruang)</li>
                </ol>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold">Catatan Penting</h6>
                <ul class="mb-0">
                    <li>File Excel harus berformat <strong>.xlsx</strong>, <strong>.xls</strong>, atau <strong>.csv</strong></li>
                    <li>Baris pertama harus berisi <strong>header/judul kolom</strong></li>
                    <li>Maksimal ukuran file: <strong>5MB</strong></li>
                    <li>Data yang sudah ada akan diperbarui (berdasarkan NIS/NIP/username/kode)</li>
                    <li>Download template untuk melihat format kolom yang benar</li>
                    <li>Password akun siswa baru otomatis sama dengan NIS</li>
                    <li>Password akun guru baru: sesuai kolom password, atau default = NIP</li>
                    <li>Password akun proktor baru: sesuai kolom password, atau default = username</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
