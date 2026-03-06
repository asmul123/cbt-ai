<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') - @yield('title') | CBT Ujian</title>
    <link rel="icon" type="image/png" href="{{ asset('smk1logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 50%, #7c3aed 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
        }

        .error-card {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 24px;
            padding: 48px 40px;
            text-align: center;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .error-logo {
            width: 64px;
            height: 64px;
            object-fit: contain;
            margin-bottom: 16px;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
        }

        .error-school {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.7);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 32px;
        }

        .error-icon-wrap {
            width: 90px;
            height: 90px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            color: #fff;
        }

        .error-code {
            font-size: 5rem;
            font-weight: 800;
            color: #fff;
            line-height: 1;
            margin-bottom: 8px;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .error-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 12px;
        }

        .error-desc {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.75);
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .btn-home {
            background: #fff;
            color: #1e3a5f;
            border: none;
            padding: 12px 28px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-home:hover {
            background: #f0f4ff;
            color: #1e3a5f;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.25);
        }

        .btn-back {
            background: transparent;
            color: rgba(255,255,255,0.8);
            border: 1px solid rgba(255,255,255,0.4);
            padding: 12px 28px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            margin-left: 12px;
        }

        .btn-back:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
            border-color: rgba(255,255,255,0.7);
        }

        .divider {
            width: 60px;
            height: 3px;
            background: rgba(255,255,255,0.4);
            border-radius: 2px;
            margin: 0 auto 24px;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <img src="{{ asset('smk1logo.png') }}" alt="Logo SMK" class="error-logo">
        <p class="error-school">SMK Negeri 1 Garut &mdash; CBT Ujian</p>

        <div class="error-icon-wrap">
            <i class="bi @yield('icon')"></i>
        </div>

        <div class="error-code">@yield('code')</div>
        <div class="divider"></div>
        <div class="error-title">@yield('title')</div>
        <div class="error-desc">@yield('description')</div>

        <div>
            <a href="{{ url('/') }}" class="btn-home">
                <i class="bi bi-house-door-fill"></i> Ke Beranda
            </a>
            <a href="javascript:history.back()" class="btn-back">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</body>
</html>
