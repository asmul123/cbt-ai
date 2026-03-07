@extends('layouts.app')
@section('title', 'Isi Berita Acara: ' . $ujian->nama)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">Berita Acara: {{ $ujian->nama }}</h5>
        <small class="text-muted">Ruang: <strong>{{ $ruang->nama }}</strong> | Mapel: {{ $ujian->mapel->nama ?? '-' }}</small>
    </div>
    <a href="{{ route('proktor.berita-acara.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
</div>

<form action="{{ route('proktor.berita-acara.store', $ujian) }}" method="POST" id="formBeritaAcara">
    @csrf

    <div class="row g-3">
        {{-- Kolom Kiri: Info & Form --}}
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="bi bi-person-badge"></i> Nama Pengawas</h6></div>
                <div class="card-body">
                    <input type="text" name="nama_pengawas" class="form-control"
                           value="{{ old('nama_pengawas', $beritaAcara->nama_pengawas ?? '') }}"
                           placeholder="Masukkan nama pengawas" required>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="bi bi-clock"></i> Waktu Pelaksanaan</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="waktu_mulai" class="form-control"
                                   value="{{ old('waktu_mulai', $beritaAcara->waktu_mulai ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="waktu_selesai" class="form-control"
                                   value="{{ old('waktu_selesai', $beritaAcara->waktu_selesai ?? '') }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="bi bi-chat-left-text"></i> Catatan Pelaksanaan</h6></div>
                <div class="card-body">
                    <textarea name="catatan" class="form-control" rows="3"
                              placeholder="Catatan selama pelaksanaan ujian (kosongkan jika aman)">{{ old('catatan', $beritaAcara->catatan ?? '') }}</textarea>
                    <div class="form-text">Jika dikosongkan, akan tertulis "Aman"</div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-person-x"></i> Peserta Tidak Hadir</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Centang peserta yang <strong>TIDAK HADIR</strong> pada ujian ini:</p>
                    @php
                        $tidakHadirIds = old('peserta_tidak_hadir', $beritaAcara->peserta_tidak_hadir ?? []);
                    @endphp
                    <div class="row g-2" style="max-height: 300px; overflow-y: auto;">
                        @foreach($peserta as $p)
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="peserta_tidak_hadir[]"
                                       value="{{ $p->id }}"
                                       id="absen_{{ $p->id }}"
                                       {{ in_array($p->id, $tidakHadirIds) ? 'checked' : '' }}>
                                <label class="form-check-label" for="absen_{{ $p->id }}">
                                    <small>{{ $p->siswa->nis ?? '-' }}</small> — {{ $p->siswa->nama ?? '-' }}
                                    <small class="text-muted">({{ $p->siswa->kelas->nama ?? '-' }})</small>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($peserta->isEmpty())
                        <div class="text-muted text-center py-3">Tidak ada peserta di ruangan ini</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Tanda Tangan --}}
        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-pen"></i> Tanda Tangan Pengawas <span class="text-danger">*</span></h6>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearSignature()">
                            <i class="bi bi-eraser"></i> Hapus
                        </button>
                    </div>
                </div>
                <div class="card-body text-center">
                    <div style="border: 2px dashed #ccc; border-radius: 8px; background: #fff; position: relative;">
                        <canvas id="signatureCanvas" width="400" height="200" style="width:100%; cursor:crosshair;"></canvas>
                    </div>
                    <input type="hidden" name="ttd_pengawas" id="ttdPengawas" value="{{ $beritaAcara->ttd_pengawas ?? '' }}">
                    <div class="form-text mt-1">Tanda tangan di area atas menggunakan mouse/sentuhan</div>
                    @error('ttd_pengawas')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card bg-light">
                <div class="card-body">
                    <h6><i class="bi bi-info-circle"></i> Informasi Ujian</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted" width="140">Mata Pelajaran</td><td>: {{ $ujian->mapel->nama ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Kelas</td><td>: {{ $ujian->kelas->pluck('nama')->implode(', ') ?: '-' }}</td></tr>
                        <tr><td class="text-muted">Ruang</td><td>: {{ $ruang->nama }}</td></tr>
                        <tr><td class="text-muted">Total Peserta</td><td>: {{ $peserta->count() }} orang</td></tr>
                        <tr><td class="text-muted">Tanggal</td>
                            <td>: {{ $ujian->tanggal_mulai ? $ujian->tanggal_mulai->translatedFormat('l, d F Y') : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="text-end mt-3">
        <button type="submit" class="btn btn-success" onclick="return prepareSubmit()">
            <i class="bi bi-check-lg"></i> Simpan Berita Acara
        </button>
    </div>
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('signatureCanvas');
    const ctx = canvas.getContext('2d');
    let drawing = false;
    let hasDrawn = false;

    // Responsive canvas
    function resizeCanvas() {
        const rect = canvas.parentElement.getBoundingClientRect();
        const ratio = window.devicePixelRatio || 1;
        canvas.width = rect.width * ratio;
        canvas.height = 200 * ratio;
        canvas.style.height = '200px';
        ctx.scale(ratio, ratio);
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#000';

        // Restore existing signature
        const existing = document.getElementById('ttdPengawas').value;
        if (existing) {
            const img = new Image();
            img.onload = () => ctx.drawImage(img, 0, 0, rect.width, 200);
            img.src = existing;
            hasDrawn = true;
        }
    }
    resizeCanvas();

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return { x: clientX - rect.left, y: clientY - rect.top };
    }

    canvas.addEventListener('mousedown', (e) => { drawing = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); });
    canvas.addEventListener('mousemove', (e) => { if (!drawing) return; hasDrawn = true; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); });
    canvas.addEventListener('mouseup', () => drawing = false);
    canvas.addEventListener('mouseleave', () => drawing = false);

    // Touch events
    canvas.addEventListener('touchstart', (e) => { e.preventDefault(); drawing = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); }, {passive:false});
    canvas.addEventListener('touchmove', (e) => { e.preventDefault(); if (!drawing) return; hasDrawn = true; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); }, {passive:false});
    canvas.addEventListener('touchend', (e) => { e.preventDefault(); drawing = false; }, {passive:false});

    window.clearSignature = function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('ttdPengawas').value = '';
        hasDrawn = false;
    };

    window.prepareSubmit = function() {
        const existing = document.getElementById('ttdPengawas').value;
        if (hasDrawn) {
            // Create temp canvas at fixed size for consistent storage
            const tmpCanvas = document.createElement('canvas');
            tmpCanvas.width = 400;
            tmpCanvas.height = 200;
            const tmpCtx = tmpCanvas.getContext('2d');
            tmpCtx.drawImage(canvas, 0, 0, 400, 200);
            document.getElementById('ttdPengawas').value = tmpCanvas.toDataURL('image/png');
        }

        if (!document.getElementById('ttdPengawas').value) {
            alert('Tanda tangan pengawas wajib diisi!');
            return false;
        }
        return true;
    };
});
</script>
@endpush
