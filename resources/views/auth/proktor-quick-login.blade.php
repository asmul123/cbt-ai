<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quick Login Proktor - CBT Ujian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #0f2447 0%, #1e3a5f 50%, #2c5282 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: rgba(255,255,255,0.03);
            top: -150px; right: -100px;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            width: 350px; height: 350px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            bottom: -100px; left: -80px;
            pointer-events: none;
        }

        .page-header {
            text-align: center;
            color: white;
            padding: 30px 0 20px;
        }
        .page-header .icon-wrap {
            width: 72px; height: 72px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            border: 2px solid rgba(255,255,255,0.2);
        }
        .page-header h4 { font-weight: 700; margin-bottom: 4px; }
        .page-header small { opacity: 0.7; font-size: 0.85rem; }

        .ruang-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 16px;
            max-width: 1100px;
            margin: 0 auto;
            padding-bottom: 40px;
        }

        .ruang-card {
            background: rgba(255,255,255,0.97);
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            transition: all 0.25s ease;
            cursor: pointer;
        }
        .ruang-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 40px rgba(0,0,0,0.3);
        }
        .ruang-card.no-proktor {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .ruang-card.no-proktor:hover {
            transform: none;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
        }

        .ruang-top {
            background: linear-gradient(135deg, #1e3a5f, #2c5282);
            padding: 20px;
            color: white;
            text-align: center;
        }
        .ruang-top .room-icon {
            width: 52px; height: 52px;
            background: rgba(255,255,255,0.2);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            font-size: 1.5rem;
        }
        .ruang-top h6 {
            font-weight: 700;
            margin-bottom: 2px;
            font-size: 1.05rem;
        }
        .ruang-top .kode {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .ruang-body {
            padding: 16px 20px 20px;
        }
        .ruang-info {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-bottom: 14px;
            font-size: 0.82rem;
            color: #64748b;
        }

        .proktor-btn {
            width: 100%;
            padding: 11px;
            background: linear-gradient(135deg, #0f2447, #2c5282);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
            box-shadow: 0 4px 12px rgba(44, 82, 130, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        .proktor-btn:hover { opacity: 0.9; transform: translateY(-1px); }
        .proktor-btn:active { transform: translateY(0); }

        .no-proktor-text {
            text-align: center;
            color: #94a3b8;
            font-size: 0.85rem;
            padding: 8px 0;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }
        .back-link:hover { color: white; }

        /* Modal password */
        .modal-content { border-radius: 16px; border: none; }
        .modal-header {
            background: linear-gradient(135deg, #0f2447, #2c5282);
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 20px 24px;
        }
        .modal-header .btn-close { filter: brightness(0) invert(1); }
        .modal-body { padding: 28px 24px; }

        .pw-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .pw-input-wrap input {
            width: 100%;
            padding: 13px 44px 13px 42px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            background: #f8fafc;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .pw-input-wrap input:focus {
            border-color: #2c5282;
            box-shadow: 0 0 0 4px rgba(44, 82, 130, 0.1);
            background: #fff;
        }
        .pw-input-wrap .pw-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            pointer-events: none;
        }
        .pw-input-wrap .toggle-pw {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px 6px;
            border-radius: 6px;
        }
        .pw-input-wrap .toggle-pw:hover { color: #2c5282; background: #f1f5f9; }

        .alert-float {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 300px;
            max-width: 500px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
            border-radius: 10px;
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateX(-50%) translateY(-20px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }

        .empty-state {
            text-align: center;
            color: rgba(255,255,255,0.5);
            padding: 60px 20px;
        }
        .empty-state i { font-size: 4rem; margin-bottom: 16px; display: block; }
    </style>
</head>
<body>
    {{-- Flash Alert --}}
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show alert-float" id="flashAlert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Header --}}
    <div class="page-header">
        <div class="icon-wrap">
            <img src="{{ asset('smk1logo.png') }}" alt="Logo" style="width:48px;height:48px;object-fit:contain;">
        </div>
        <h4>Quick Login Proktor</h4>
        <small>Pilih ruang ujian untuk masuk sebagai proktor</small>
    </div>

    {{-- Grid Ruang --}}
    <div class="ruang-grid">
        @forelse($ruangan as $ruang)
        <div class="ruang-card {{ $ruang->proktor->isEmpty() ? 'no-proktor' : '' }}">
            <div class="ruang-top">
                <div class="room-icon"><i class="bi bi-display"></i></div>
                <h6>{{ $ruang->nama }}</h6>
                <span class="kode">{{ $ruang->kode }}</span>
            </div>
            <div class="ruang-body">
                <div class="ruang-info">
                    @if($ruang->lokasi)
                    <span><i class="bi bi-geo-alt"></i> {{ $ruang->lokasi }}</span>
                    @endif
                    <span><i class="bi bi-people"></i> {{ $ruang->kapasitas ?? '-' }} kursi</span>
                </div>

                @if($ruang->proktor->isNotEmpty())
                    @foreach($ruang->proktor as $proktor)
                    <button type="button" class="proktor-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#loginModal"
                            data-user-id="{{ $proktor->id }}"
                            data-user-name="{{ $proktor->name }}"
                            data-ruang-nama="{{ $ruang->nama }}">
                        <i class="bi bi-person-badge"></i>
                        {{ $proktor->name }}
                    </button>
                    @endforeach
                @else
                    <div class="no-proktor-text">
                        <i class="bi bi-person-slash"></i> Belum ada proktor
                    </div>
                @endif
            </div>
        </div>
        @empty
        <div class="empty-state" style="grid-column: 1 / -1;">
            <i class="bi bi-geo-alt"></i>
            <h6>Belum ada ruang ujian aktif</h6>
        </div>
        @endforelse
    </div>

    {{-- Back to login --}}
    <div class="text-center pb-4">
        <a href="{{ route('login') }}" class="back-link">
            <i class="bi bi-arrow-left"></i> Kembali ke halaman login
        </a>
    </div>

    {{-- Login Modal --}}
    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h6 class="mb-0 fw-bold" id="modalTitle">Login Proktor</h6>
                        <small class="opacity-75" id="modalSubtitle">Ruang</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('proktor.quick-login.login') }}" id="loginForm">
                        @csrf
                        <input type="hidden" name="user_id" id="modalUserId">

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">PASSWORD</label>
                            <div class="pw-input-wrap">
                                <i class="bi bi-lock pw-icon"></i>
                                <input type="password" name="password" id="modalPassword"
                                       placeholder="Masukkan password proktor" required autofocus>
                                <button type="button" class="toggle-pw" onclick="toggleModalPassword()">
                                    <i class="bi bi-eye" id="modalToggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="proktor-btn" style="margin-bottom:0;">
                            <i class="bi bi-box-arrow-in-right"></i> Masuk
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set modal data when proktor button clicked
        const loginModal = document.getElementById('loginModal');
        loginModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            document.getElementById('modalUserId').value = btn.dataset.userId;
            document.getElementById('modalTitle').textContent = btn.dataset.userName;
            document.getElementById('modalSubtitle').textContent = btn.dataset.ruangNama;
            document.getElementById('modalPassword').value = '';
        });

        // Auto focus password field when modal shown
        loginModal.addEventListener('shown.bs.modal', function() {
            document.getElementById('modalPassword').focus();
        });

        // Toggle password visibility
        function toggleModalPassword() {
            const input = document.getElementById('modalPassword');
            const icon = document.getElementById('modalToggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        // Auto dismiss flash alert
        const flashAlert = document.getElementById('flashAlert');
        if (flashAlert) {
            setTimeout(() => {
                flashAlert.classList.remove('show');
                setTimeout(() => flashAlert.remove(), 300);
            }, 4000);
        }
    </script>
</body>
</html>
