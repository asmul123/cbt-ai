@extends('layouts.app')
@section('title', 'Daftar Hadir Ujian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-pen"></i> Tanda Tangan Daftar Hadir</h5>
</div>

@if(count($items) === 0)
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Belum ada ujian yang memerlukan tanda tangan daftar hadir.
</div>
@else
<div class="row g-3">
    @foreach($items as $item)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 {{ $item->tidak_hadir ? 'border-danger' : ($item->sudah_ttd ? 'border-success' : '') }}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-truncate">{{ $item->ujian->nama }}</h6>
                @if($item->tidak_hadir)
                    <span class="badge bg-danger">Tidak Hadir</span>
                @elseif($item->sudah_ttd)
                    <span class="badge bg-success">Sudah TTD</span>
                @else
                    <span class="badge bg-warning text-dark">Belum TTD</span>
                @endif
            </div>
            <div class="card-body">
                <table class="small mb-3">
                    <tr><td class="text-muted pe-2">Mapel</td><td>: {{ $item->ujian->mapel->nama ?? '-' }}</td></tr>
                    <tr><td class="text-muted pe-2">Ruang</td><td>: {{ $item->ruang->nama ?? '-' }}</td></tr>
                    <tr>
                        <td class="text-muted pe-2">Tanggal</td>
                        <td>: {{ $item->ujian->tanggal_mulai ? \Carbon\Carbon::parse($item->ujian->tanggal_mulai)->translatedFormat('d M Y') : '-' }}</td>
                    </tr>
                </table>

                @if($item->tidak_hadir)
                    <div class="alert alert-danger small mb-0 py-2">
                        <i class="bi bi-exclamation-triangle"></i> Anda tercatat tidak hadir pada ujian ini.
                    </div>
                @else
                    {{-- Preview TTD existing --}}
                    <div id="ttd-preview-{{ $item->berita_acara->id }}" class="text-center mb-2">
                        @if($item->sudah_ttd && $item->tanda_tangan)
                            <img src="{{ $item->tanda_tangan }}" alt="TTD" style="max-height:60px; max-width:200px; border:1px solid #ddd; border-radius:4px; padding:4px;">
                        @endif
                    </div>

                    {{-- Canvas TTD --}}
                    <div id="canvas-wrap-{{ $item->berita_acara->id }}" style="display:none;">
                        <div style="border: 2px dashed #ccc; border-radius: 8px; background: #fff;">
                            <canvas id="canvas-{{ $item->berita_acara->id }}" width="400" height="160" style="width:100%; cursor:crosshair;"></canvas>
                        </div>
                        <div class="d-flex gap-2 justify-content-center mt-2">
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearSign({{ $item->berita_acara->id }})">
                                <i class="bi bi-eraser"></i> Hapus
                            </button>
                            <button type="button" class="btn btn-success btn-sm" onclick="saveSign({{ $item->berita_acara->id }})">
                                <i class="bi bi-check-lg"></i> Simpan
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleCanvas({{ $item->berita_acara->id }}, false)">
                                Batal
                            </button>
                        </div>
                        <div id="status-{{ $item->berita_acara->id }}" class="text-center mt-1"></div>
                    </div>

                    {{-- Tombol buka canvas --}}
                    <div id="btn-wrap-{{ $item->berita_acara->id }}" class="text-center">
                        <button type="button" class="btn {{ $item->sudah_ttd ? 'btn-outline-success' : 'btn-warning' }} btn-sm"
                                onclick="toggleCanvas({{ $item->berita_acara->id }}, true)">
                            <i class="bi bi-pen"></i> {{ $item->sudah_ttd ? 'Edit Tanda Tangan' : 'Tanda Tangan' }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';
const pads = {};

function initCanvas(canvas) {
    const ctx = canvas.getContext('2d');
    let drawing = false;
    let drawn = false;

    function resize() {
        const rect = canvas.parentElement.getBoundingClientRect();
        const ratio = window.devicePixelRatio || 1;
        const h = parseInt(canvas.getAttribute('height')) || 160;
        canvas.width = rect.width * ratio;
        canvas.height = h * ratio;
        canvas.style.height = h + 'px';
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
            tmp.width = 400; tmp.height = parseInt(canvas.getAttribute('height')) || 160;
            tmp.getContext('2d').drawImage(canvas, 0, 0, 400, tmp.height);
            return tmp.toDataURL('image/png');
        },
        loadImage(src) {
            if (!src) return;
            const img = new Image();
            const rect = canvas.parentElement.getBoundingClientRect();
            img.onload = () => { ctx.drawImage(img, 0, 0, rect.width, parseInt(canvas.getAttribute('height')) || 160); drawn = true; };
            img.src = src;
        }
    };
}

function toggleCanvas(baId, show) {
    document.getElementById('canvas-wrap-' + baId).style.display = show ? 'block' : 'none';
    document.getElementById('btn-wrap-' + baId).style.display = show ? 'none' : 'block';

    if (show && !pads[baId]) {
        const canvas = document.getElementById('canvas-' + baId);
        pads[baId] = initCanvas(canvas);
    }
}

function clearSign(baId) {
    if (pads[baId]) pads[baId].clear();
}

function saveSign(baId) {
    const pad = pads[baId];
    if (!pad || !pad.hasDrawn()) {
        alert('Silakan tanda tangan terlebih dahulu!');
        return;
    }

    const ttd = pad.toDataURL();
    const statusEl = document.getElementById('status-' + baId);
    statusEl.innerHTML = '<span class="text-muted small">Menyimpan...</span>';

    fetch('{{ route("siswa.daftar-hadir.simpanTtd") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ berita_acara_id: baId, tanda_tangan: ttd })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            statusEl.innerHTML = '<span class="text-success small"><i class="bi bi-check-circle"></i> Berhasil disimpan</span>';
            // Update preview
            document.getElementById('ttd-preview-' + baId).innerHTML =
                '<img src="' + ttd + '" alt="TTD" style="max-height:60px; max-width:200px; border:1px solid #ddd; border-radius:4px; padding:4px;">';
            // Update button text
            document.getElementById('btn-wrap-' + baId).innerHTML =
                '<button type="button" class="btn btn-outline-success btn-sm" onclick="toggleCanvas(' + baId + ', true)"><i class="bi bi-pen"></i> Edit Tanda Tangan</button>';
            setTimeout(() => toggleCanvas(baId, false), 1500);
        } else {
            statusEl.innerHTML = '<span class="text-danger small">' + (data.message || 'Gagal menyimpan') + '</span>';
        }
    })
    .catch(() => {
        statusEl.innerHTML = '<span class="text-danger small">Terjadi kesalahan</span>';
    });
}
</script>
@endpush
