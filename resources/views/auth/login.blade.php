<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - CBT Ujian Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #0f2447 0%, #1e3a5f 50%, #2c5282 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        /* Background decorative circles */
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
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
        }
        .login-card {
            background: rgba(255,255,255,0.97);
            border-radius: 20px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.1);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #0f2447 0%, #2c5282 100%);
            color: white;
            padding: 36px 30px 28px;
            text-align: center;
            position: relative;
        }
        .login-header .icon-wrap {
            width: 72px; height: 72px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            border: 2px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
        }
        .login-header .icon-wrap i { font-size: 2rem; }
        .login-header h4 { font-weight: 700; font-size: 1.3rem; margin-bottom: 4px; }
        .login-header small { opacity: 0.7; font-size: 0.82rem; letter-spacing: 0.5px; }
        .login-body { padding: 32px 30px 28px; }

        /* Custom floating-style input */
        .field-group {
            position: relative;
            margin-bottom: 20px;
        }
        .field-group label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            margin-bottom: 8px;
        }
        .field-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .field-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 1rem;
            pointer-events: none;
            transition: color 0.2s;
            z-index: 2;
        }
        .field-input-wrap input {
            width: 100%;
            padding: 13px 14px 13px 42px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            background: #f8fafc;
            color: #1e293b;
            outline: none;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
            -webkit-appearance: none;
        }
        .field-input-wrap input:focus {
            border-color: #2c5282;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(44, 82, 130, 0.1);
        }
        .field-input-wrap input.is-invalid {
            border-color: #ef4444;
            background: #fff5f5;
        }
        .field-input-wrap input:focus ~ .field-icon,
        .field-input-wrap:focus-within .field-icon {
            color: #2c5282;
        }
        .toggle-pw {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px 6px;
            border-radius: 6px;
            transition: color 0.2s, background 0.2s;
            z-index: 2;
        }
        .toggle-pw:hover { color: #2c5282; background: #f1f5f9; }
        .password-input { padding-right: 44px !important; }

        .error-msg { font-size: 0.8rem; color: #ef4444; margin-top: 6px; display: flex; align-items: center; gap: 4px; }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 22px;
        }
        .remember-row input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: #2c5282;
            cursor: pointer;
        }
        .remember-row label {
            font-size: 0.88rem;
            color: #475569;
            cursor: pointer;
            margin: 0;
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #0f2447 0%, #2c5282 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: 0.3px;
            transition: opacity 0.2s, transform 0.1s, box-shadow 0.2s;
            box-shadow: 0 4px 15px rgba(44, 82, 130, 0.35);
        }
        .btn-login:hover { opacity: 0.92; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(44, 82, 130, 0.45); }
        .btn-login:active { transform: translateY(0); }
        .btn-login i { margin-right: 6px; }

        .footer-text {
            text-align: center;
            margin-top: 22px;
            font-size: 0.78rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="icon-wrap">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <h4>CBT Ujian Sekolah</h4>
                <small>Computer Based Testing System</small>
            </div>

            <div class="login-body">
                @if(session('status'))
                    <div class="alert alert-success py-2 small">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- Username --}}
                    <div class="field-group">
                        <label for="username">Username</label>
                        <div class="field-input-wrap">
                            <i class="bi bi-person field-icon"></i>
                            <input type="text"
                                   name="username"
                                   id="username"
                                   value="{{ old('username') }}"
                                   placeholder="Masukkan username"
                                   class="{{ $errors->has('username') ? 'is-invalid' : '' }}"
                                   required autofocus autocomplete="username">
                        </div>
                        @error('username')
                            <div class="error-msg"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="field-group">
                        <label for="passwordInput">Password</label>
                        <div class="field-input-wrap">
                            <i class="bi bi-lock field-icon"></i>
                            <input type="password"
                                   name="password"
                                   id="passwordInput"
                                   placeholder="Masukkan password"
                                   class="password-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                   required autocomplete="current-password">
                            <button class="toggle-pw" type="button" id="togglePassword" tabindex="-1">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="error-msg"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Remember --}}
                    <div class="remember-row">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">Ingat Saya</label>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="bi bi-box-arrow-in-right"></i> Masuk
                    </button>
                </form>

                <div class="footer-text">&copy; {{ date('Y') }} CBT Ujian Sekolah &mdash; SMK</div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    </script>
</body>
</html>
