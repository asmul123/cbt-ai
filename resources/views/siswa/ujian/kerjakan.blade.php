<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ujian: {{ $ujian->nama }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .exam-header {
            background: linear-gradient(135deg, #1e3a5f, #2c5282);
            color: white;
            padding: 12px 20px;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .timer { font-size: 1.5rem; font-weight: 700; font-family: monospace; }
        .timer.warning { color: #fbbf24; animation: blink 1s infinite; }
        .timer.danger { color: #ef4444; animation: blink 0.5s infinite; }
        @keyframes blink { 50% { opacity: 0.5; } }
        .soal-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .nav-soal { display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; }
        .nav-soal .btn-nav {
            width: 100%; aspect-ratio: 1; border-radius: 8px;
            font-weight: 600; font-size: 0.85rem; border: 2px solid #e2e8f0;
            background: white; cursor: pointer; transition: all 0.2s;
        }
        .nav-soal .btn-nav.answered { background: #10b981; color: white; border-color: #10b981; }
        .nav-soal .btn-nav.current { background: #3b82f6; color: white; border-color: #3b82f6; }
        .nav-soal .btn-nav.ragu { background: #f59e0b; color: white; border-color: #f59e0b; }
        .opsi-item {
            padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 8px;
            margin-bottom: 8px; cursor: pointer; transition: all 0.2s;
        }
        .opsi-item:hover { border-color: #3b82f6; background: #eff6ff; }
        .opsi-item.selected { border-color: #3b82f6; background: #dbeafe; }
        .opsi-item input[type="radio"], .opsi-item input[type="checkbox"] { margin-right: 10px; }
        /* Anti-cheat overlay */
        #cheatWarning {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.9); z-index: 9999; color: white;
            justify-content: center; align-items: center; flex-direction: column;
        }
    </style>
</head>
<body>
    <!-- Anti-cheat Warning Overlay -->
    <div id="cheatWarning">
        <i class="bi bi-exclamation-triangle-fill" style="font-size: 4rem; color: #ef4444;"></i>
        <h3 class="mt-3">Peringatan!</h3>
        <p>Anda terdeteksi meninggalkan halaman ujian.</p>
        <p>Pelanggaran: <span id="violationCount">{{ $peserta->jumlah_pelanggaran ?? 0 }}</span> / 5</p>
        <p class="text-warning small">Jika mencapai 5 pelanggaran, ujian akan otomatis dikumpulkan!</p>
        <button class="btn btn-warning btn-lg" onclick="dismissWarning()">Kembali ke Ujian</button>
    </div>

    <!-- Header -->
    <div class="exam-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0">{{ $ujian->nama }}</h6>
                <small class="opacity-75">{{ $ujian->mapel->nama ?? '' }} | Soal {{ $nomor }}/{{ $totalSoal }}</small>
            </div>
            <div class="text-center">
                <div class="timer" id="timer">--:--:--</div>
                <small class="opacity-75">Sisa Waktu</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if(($peserta->jumlah_pelanggaran ?? 0) > 0)
                <span class="badge bg-danger" id="headerViolation" title="Jumlah pelanggaran">
                    <i class="bi bi-exclamation-triangle"></i> <span id="headerViolationCount">{{ $peserta->jumlah_pelanggaran }}</span>/5
                </span>
                @else
                <span class="badge bg-danger d-none" id="headerViolation" title="Jumlah pelanggaran">
                    <i class="bi bi-exclamation-triangle"></i> <span id="headerViolationCount">0</span>/5
                </span>
                @endif
                <a href="{{ route('siswa.ujian.submitKonfirmasi', $ujian) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-send"></i> Selesai & Kumpulkan
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-3">
        <div class="row">
            <!-- Soal Area -->
            <div class="col-md-9">
                <div class="soal-card p-4 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Soal {{ $nomor }}</h5>
                        <span class="badge bg-secondary">{{ strtoupper($soal->tipe_soal) }}</span>
                    </div>

                    <div class="mb-4 fs-5 soal-content">{!! $soal->soal !!}</div>

                    @if($soal->gambar)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $soal->gambar) }}" class="img-fluid rounded" style="max-height: 300px">
                        </div>
                    @endif

                    <form id="jawabanForm" action="{{ route('siswa.ujian.jawab', $ujian) }}" method="POST">
                        @csrf
                        <input type="hidden" name="soal_id" value="{{ $soal->id }}">
                        <input type="hidden" name="nomor" value="{{ $nomor }}">
                        <input type="hidden" name="ragu_ragu" id="raguRaguInput" value="{{ $jawaban && $jawaban->ragu_ragu ? '1' : '0' }}">

                        @if($soal->tipe_soal == 'pg')
                            @foreach($opsiList as $idx => $opsi)
                            @php $displayLabel = chr(65 + $idx); @endphp
                            <label class="opsi-item d-flex align-items-start {{ $jawaban && $jawaban->jawaban == $opsi->label ? 'selected' : '' }}">
                                <input type="radio" name="jawaban" value="{{ $opsi->label }}"
                                    {{ $jawaban && $jawaban->jawaban == $opsi->label ? 'checked' : '' }}
                                    onchange="this.form.classList.add('dirty')">
                                <div><strong>{{ $displayLabel }}.</strong> {!! $opsi->teks !!}</div>
                            </label>
                            @endforeach

                        @elseif($soal->tipe_soal == 'pg_kompleks')
                            @php $selected = $jawaban && $jawaban->jawaban ? (json_decode($jawaban->jawaban, true) ?? []) : []; @endphp
                            @foreach($opsiList as $idx => $opsi)
                            @php $displayLabel = chr(65 + $idx); @endphp
                            <label class="opsi-item d-flex align-items-start {{ in_array($opsi->label, $selected) ? 'selected' : '' }}">
                                <input type="checkbox" name="jawaban[]" value="{{ $opsi->label }}"
                                    {{ in_array($opsi->label, $selected) ? 'checked' : '' }}
                                    onchange="updateCheckboxSelection()">
                                <div><strong>{{ $displayLabel }}.</strong> {!! $opsi->teks !!}</div>
                            </label>
                            @endforeach

                        @elseif($soal->tipe_soal == 'isian')
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jawaban Singkat:</label>
                                <input type="text" name="jawaban" class="form-control form-control-lg"
                                       value="{{ $jawaban->jawaban ?? '' }}" placeholder="Ketik jawaban Anda..." autofocus>
                            </div>

                        @elseif($soal->tipe_soal == 'essay')
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jawaban Essay:</label>
                                <textarea name="jawaban" class="form-control" rows="8"
                                          placeholder="Tulis jawaban Anda di sini...">{{ $jawaban->jawaban ?? '' }}</textarea>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                @if($nomor > 1)
                                    <a href="{{ route('siswa.ujian.kerjakan', [$ujian, $nomor - 1]) }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-chevron-left"></i> Sebelumnya
                                    </a>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" name="action" value="save" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan
                                </button>
                                @if($nomor < $totalSoal)
                                    <button type="submit" name="action" value="next" class="btn btn-success">
                                        Selanjutnya <i class="bi bi-chevron-right"></i>
                                    </button>
                                @else
                                    <a href="{{ route('siswa.ujian.submitKonfirmasi', $ujian) }}" class="btn btn-warning">
                                        <i class="bi bi-send"></i> Selesai
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Navigation Panel -->
            <div class="col-md-3">
                <div class="soal-card p-3 position-sticky" style="top: 80px;">
                    <h6 class="mb-3"><i class="bi bi-grid-3x3"></i> Navigasi Soal</h6>
                    <div class="nav-soal">
                        @foreach($soalOrder as $idx => $soalId)
                        @php
                            $no = $idx + 1;
                            $jwbData = $jawabanStatus[$soalId] ?? null;
                            $class = '';
                            if ($no == $nomor) $class = 'current';
                            elseif ($jwbData && $jwbData->ragu_ragu) $class = 'ragu';
                            elseif ($jwbData && $jwbData->jawaban !== null && $jwbData->jawaban !== '') $class = 'answered';
                        @endphp
                        <a href="{{ route('siswa.ujian.kerjakan', [$ujian, $no]) }}" class="btn-nav text-decoration-none d-flex align-items-center justify-content-center {{ $class }}">
                            {{ $no }}
                        </a>
                        @endforeach
                    </div>

                    <div class="mt-3 small">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="d-inline-block rounded" style="width:16px;height:16px;background:#3b82f6"></span> Soal Sekarang
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="d-inline-block rounded" style="width:16px;height:16px;background:#10b981"></span> Sudah Dijawab
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="d-inline-block rounded" style="width:16px;height:16px;background:#f59e0b"></span> Ragu-ragu
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-block rounded border" style="width:16px;height:16px;background:white"></span> Belum Dijawab
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="raguRagu"
                                {{ $jawaban && $jawaban->ragu_ragu ? 'checked' : '' }}
                                onchange="toggleRagu()">
                            <label class="form-check-label text-warning fw-semibold" for="raguRagu">
                                <i class="bi bi-flag-fill"></i> Tandai Ragu-ragu
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- MathJax for equation rendering -->
    <script>
        window.MathJax = {
            tex: { inlineMath: [['\\(', '\\)']], displayMath: [['\\[', '\\]']] },
            options: { skipHtmlTags: ['script', 'noscript', 'style', 'textarea', 'pre'] }
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js" async></script>

    <script>
        // ========== TIMER ==========
        const sisaWaktu = {{ $peserta->sisaWaktu() ?? 0 }};
        let remaining = sisaWaktu;
        const timerEl = document.getElementById('timer');

        function updateTimer() {
            if (remaining <= 0) {
                timerEl.textContent = '00:00:00';
                // Auto submit
                document.getElementById('jawabanForm').action = '{{ route("siswa.ujian.submit", $ujian) }}';
                document.getElementById('jawabanForm').submit();
                return;
            }
            remaining--;
            const h = Math.floor(remaining / 3600);
            const m = Math.floor((remaining % 3600) / 60);
            const s = remaining % 60;
            timerEl.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;

            if (remaining <= 60) timerEl.className = 'timer danger';
            else if (remaining <= 300) timerEl.className = 'timer warning';
        }
        updateTimer();
        setInterval(updateTimer, 1000);

        // ========== ANTI-CHEAT ==========
        let violations = {{ $peserta->jumlah_pelanggaran ?? 0 }};
        let isNavigating = false;
        const maxViolations = 5;
        const warningEl = document.getElementById('cheatWarning');
        const violationEl = document.getElementById('violationCount');
        const logUrl = '{{ route("siswa.logAktivitas") }}';
        const ujianId = {{ $ujian->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const submitUrl = '{{ route("siswa.ujian.submit", $ujian) }}';

        function logActivity(tipe, detail) {
            return fetch(logUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ tipe, detail, ujian_id: ujianId })
            })
            .then(r => r.json())
            .then(data => {
                if (data.jumlah_pelanggaran) {
                    violations = data.jumlah_pelanggaran;
                    violationEl.textContent = violations;
                }
                if (data.auto_submit) {
                    alert('Anda telah melakukan ' + maxViolations + ' pelanggaran. Ujian otomatis dikumpulkan!');
                    // Submit form via POST
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = submitUrl;
                    form.innerHTML = '<input type="hidden" name="_token" value="' + csrfToken + '">';
                    document.body.appendChild(form);
                    form.submit();
                }
            })
            .catch(() => {});
        }

        function handleViolation(tipe, detail) {
            violations++;
            violationEl.textContent = violations;
            warningEl.style.display = 'flex';
            // Update header badge
            const headerBadge = document.getElementById('headerViolation');
            const headerCount = document.getElementById('headerViolationCount');
            if (headerBadge) { headerBadge.classList.remove('d-none'); }
            if (headerCount) { headerCount.textContent = violations; }
            logActivity(tipe, detail);
        }

        // Detect tab switch
        document.addEventListener('visibilitychange', function() {
            if (document.hidden && !isNavigating) {
                handleViolation('tab_switch', 'Siswa berpindah tab (pelanggaran ke-' + (violations + 1) + ')');
            }
        });

        // Detect window blur (debounce to avoid double-fire with visibilitychange)
        let blurTimeout = null;
        window.addEventListener('blur', function() {
            if (blurTimeout) clearTimeout(blurTimeout);
            blurTimeout = setTimeout(function() {
                if (!document.hidden && !isNavigating) {
                    handleViolation('window_blur', 'Window kehilangan fokus (pelanggaran ke-' + (violations + 1) + ')');
                }
            }, 200);
        });

        // Mark navigation so form submits and link clicks don't trigger violations
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function() { isNavigating = true; });
        });
        document.querySelectorAll('a[href]').forEach(function(link) {
            // Only for internal exam links (not # or javascript:)
            if (link.href && !link.href.startsWith('javascript:') && link.href !== '#') {
                link.addEventListener('click', function() { isNavigating = true; });
            }
        });
        // Also set on beforeunload as a safety net
        window.addEventListener('beforeunload', function() { isNavigating = true; });

        function dismissWarning() {
            warningEl.style.display = 'none';
        }

        // Disable right-click
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            logActivity('right_click', 'Siswa mencoba klik kanan');
        });

        // Disable copy/paste
        document.addEventListener('copy', function(e) { e.preventDefault(); logActivity('copy', 'Siswa mencoba copy'); });
        document.addEventListener('cut', function(e) { e.preventDefault(); logActivity('cut', 'Siswa mencoba cut'); });

        // Disable keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // F12, Ctrl+Shift+I, Ctrl+U, Ctrl+C, Ctrl+V, Ctrl+A, PrintScreen
            if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I') ||
                (e.ctrlKey && e.key === 'u') || e.key === 'PrintScreen') {
                e.preventDefault();
                logActivity('blocked_key', 'Blocked key: ' + e.key);
            }
        });

        // Request fullscreen
        function goFullscreen() {
            const el = document.documentElement;
            if (el.requestFullscreen) el.requestFullscreen();
            else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
        }

        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement) {
                logActivity('exit_fullscreen', 'Siswa keluar dari fullscreen');
            }
        });

        // ========== OPSI INTERACTION ==========
        document.querySelectorAll('.opsi-item').forEach(item => {
            item.addEventListener('click', function() {
                const input = this.querySelector('input');
                if (input.type === 'radio') {
                    document.querySelectorAll('.opsi-item').forEach(i => i.classList.remove('selected'));
                    this.classList.add('selected');
                    input.checked = true;
                }
            });
        });

        function updateCheckboxSelection() {
            document.querySelectorAll('.opsi-item').forEach(item => {
                const cb = item.querySelector('input[type="checkbox"]');
                if (cb) item.classList.toggle('selected', cb.checked);
            });
        }

        function toggleRagu() {
            document.getElementById('raguRaguInput').value = document.getElementById('raguRagu').checked ? '1' : '0';
        }

        // Log page load
        logActivity('page_load', 'Soal nomor {{ $nomor }}');
    </script>
</body>
</html>
