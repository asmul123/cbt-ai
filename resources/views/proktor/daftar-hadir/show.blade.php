@extends('layouts.app')
@section('title', 'Daftar Hadir: ' . $ujian->nama)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">Daftar Hadir: {{ $ujian->nama }}</h5>
        <small class="text-muted">Ruang: <strong>{{ $ruang->nama }}</strong> | Mapel: {{ $ujian->mapel->nama ?? '-' }}</small>
    </div>
    <a href="{{ route('proktor.berita-acara.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
</div>

{{-- Rekap kehadiran --}}
@php
    $tidakHadirIds = $beritaAcara->peserta_tidak_hadir ?? [];
    $totalPeserta = $peserta->count();
    $totalHadir = $totalPeserta - count($tidakHadirIds);
    $totalTidakHadir = count($tidakHadirIds);
    $totalSudahTtd = $ttdMap->count();
@endphp
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Total Peserta</div>
                <div class="fs-3 fw-bold text-primary">{{ $totalPeserta }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Hadir</div>
                <div class="fs-3 fw-bold text-success">{{ $totalHadir }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Tidak Hadir</div>
                <div class="fs-3 fw-bold text-danger">{{ $totalTidakHadir }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small">Sudah TTD</div>
                <div class="fs-3 fw-bold text-info">{{ $totalSudahTtd }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Tabel Peserta --}}
<div class="card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="bi bi-person-lines-fill"></i> Daftar Peserta</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">No</th>
                        <th width="100">NIS</th>
                        <th>Nama Peserta Didik</th>
                        <th width="100">Kehadiran</th>
                        <th width="160">Tanda Tangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($peserta as $i => $p)
                    @php
                        $isAbsen = in_array($p->id, $tidakHadirIds);
                        $hasTtd = $ttdMap->has($p->id);
                    @endphp
                    <tr class="{{ $isAbsen ? 'table-danger' : '' }}" id="row-{{ $p->id }}">
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $p->siswa->nis ?? '-' }}</td>
                        <td class="fw-semibold">{{ $p->siswa->nama ?? '-' }}</td>
                        <td class="text-center">
                            @if($isAbsen)
                                <span class="badge bg-danger">Tidak Hadir</span>
                            @else
                                <span class="badge bg-success">Hadir</span>
                            @endif
                        </td>
                        <td class="text-center" id="ttd-preview-{{ $p->id }}">
                            @if($hasTtd)
                                <img src="{{ $ttdMap[$p->id] }}" alt="TTD" style="max-height:40px; max-width:140px;">
                            @elseif($isAbsen)
                                <span class="text-muted">-</span>
                            @else
                                <span class="text-muted small">Belum TTD</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Tanda Tangan Pengawas (Daftar Hadir) --}}
<div class="card mb-3">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-pen"></i> Tanda Tangan Pengawas</h6>
            <div>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearPengawasSign()">
                    <i class="bi bi-eraser"></i> Hapus
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="savePengawasSign()">
                    <i class="bi bi-check-lg"></i> Simpan
                </button>
            </div>
        </div>
    </div>
    <div class="card-body text-center">
        <div style="border: 2px dashed #ccc; border-radius: 8px; background: #fff; max-width:450px; margin:0 auto;">
            <canvas id="pengawasCanvas" width="400" height="180" style="width:100%; cursor:crosshair;"></canvas>
        </div>
        <div class="form-text mt-1">Tanda tangan pengawas untuk daftar hadir</div>
        <div id="pengawasSaveStatus"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const beritaAcaraId = {{ $beritaAcara->id }};
const csrfToken = '{{ csrf_token() }}';

// ===== SIGNATURE PAD HELPER =====
function initCanvas(canvas) {
    const ctx = canvas.getContext('2d');
    let drawing = false;
    let drawn = false;

    function resize() {
        const rect = canvas.parentElement.getBoundingClientRect();
        const ratio = window.devicePixelRatio || 1;
        canvas.width = rect.width * ratio;
        canvas.height = (parseInt(canvas.getAttribute('height')) || 200) * ratio;
        canvas.style.height = (parseInt(canvas.getAttribute('height')) || 200) + 'px';
        ctx.scale(ratio, ratio);
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#000';
    }
    resize();

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const cx = e.touches ? e.touches[0].clientX : e.clientX;
        const cy = e.touches ? e.touches[0].clientY : e.clientY;
        return { x: cx - rect.left, y: cy - rect.top };
    }

    canvas.addEventListener('mousedown', e => { drawing = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); });
    canvas.addEventListener('mousemove', e => { if (!drawing) return; drawn = true; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); });
    canvas.addEventListener('mouseup', () => drawing = false);
    canvas.addEventListener('mouseleave', () => drawing = false);
    canvas.addEventListener('touchstart', e => { e.preventDefault(); drawing = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); }, {passive:false});
    canvas.addEventListener('touchmove', e => { e.preventDefault(); if (!drawing) return; drawn = true; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); }, {passive:false});
    canvas.addEventListener('touchend', e => { e.preventDefault(); drawing = false; }, {passive:false});

    return {
        clear() { ctx.clearRect(0, 0, canvas.width, canvas.height); drawn = false; },
        hasDrawn() { return drawn; },
        toDataURL() {
            const tmp = document.createElement('canvas');
            tmp.width = 400; tmp.height = parseInt(canvas.getAttribute('height')) || 200;
            tmp.getContext('2d').drawImage(canvas, 0, 0, 400, tmp.height);
            return tmp.toDataURL('image/png');
        },
        loadImage(src) {
            if (!src) return;
            const img = new Image();
            const rect = canvas.parentElement.getBoundingClientRect();
            img.onload = () => { ctx.drawImage(img, 0, 0, rect.width, parseInt(canvas.getAttribute('height')) || 200); drawn = true; };
            img.src = src;
        },
        resize
    };
}

// ===== PENGAWAS CANVAS =====
let pengawasPad = null;
document.addEventListener('DOMContentLoaded', function() {
    pengawasPad = initCanvas(document.getElementById('pengawasCanvas'));
    @if($beritaAcara->ttd_pengawas_hadir)
        pengawasPad.loadImage('{{ $beritaAcara->ttd_pengawas_hadir }}');
    @endif
});

function clearPengawasSign() { if (pengawasPad) pengawasPad.clear(); }

function savePengawasSign() {
    if (!pengawasPad || !pengawasPad.hasDrawn()) {
        @if(!$beritaAcara->ttd_pengawas_hadir)
            alert('Silakan tanda tangan terlebih dahulu!');
            return;
        @endif
    }

    const ttd = pengawasPad.hasDrawn() ? pengawasPad.toDataURL() :
        '{{ $beritaAcara->ttd_pengawas_hadir ?? '' }}';

    const statusEl = document.getElementById('pengawasSaveStatus');

    fetch('{{ route("proktor.daftar-hadir.ttdPengawas") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ berita_acara_id: beritaAcaraId, tanda_tangan: ttd })
    })
    .then(r => r.json())
    .then(data => {
        statusEl.innerHTML = data.success
            ? '<span class="text-success small"><i class="bi bi-check-circle"></i> Berhasil disimpan</span>'
            : '<span class="text-danger small">Gagal menyimpan</span>';
        setTimeout(() => statusEl.innerHTML = '', 3000);
    })
    .catch(() => { statusEl.innerHTML = '<span class="text-danger small">Terjadi kesalahan</span>'; });
}
</script>
@endpush
