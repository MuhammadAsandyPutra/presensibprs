<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#123B2A">
    <title>@yield('judul') | {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .alert { padding: 10px 12px; border-radius: 10px; font-size: 14px; margin-bottom: 14px; }
        .alert-danger { background: #fbe0dc; color: #b3261e; }
        .alert-success { background: #dff3e6; color: #1b7a43; }
        .alert ul { margin: 6px 0 0; padding-left: 18px; }
        .catatan-login { text-align: center; font-size: 13px; color: var(--muted); margin-top: 18px; }
        .catatan-login a { color: var(--primary); font-weight: 600; text-decoration: none; }
    </style>
</head>
<body>
<main class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="brand-section">
                <img src="{{ asset('assets/img/IcLogin/ic_bprs.jpeg') }}" alt="Logo BPRS" class="brand-logo">
                <h1 class="brand-title">E-Presensi BPRS</h1>
                <div class="gold-line"></div>
                <p class="brand-subtitle">Sistem Informasi Kehadiran Pegawai</p>
            </div>

            @include('partials.flash')
            @yield('form')
        </div>
    </div>
</main>
</body>
</html>
