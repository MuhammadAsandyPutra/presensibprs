@php
    $user = auth('user')->user();
    $menu = fn (string $pola) => request()->routeIs($pola) ? 'active' : '';
@endphp
<aside class="navbar navbar-vertical navbar-expand-lg d-print-none" data-bs-theme="dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu-samping" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark">
            <a href="{{ route('panel.dashboard') }}" class="d-flex align-items-center gap-2 text-white text-decoration-none">
                <img src="{{ asset('assets/img/logo_bprs.jpeg') }}" width="32" height="32" class="rounded" alt="Logo">
                <span class="fs-4">E-Presensi</span>
            </a>
        </h1>
        <div class="collapse navbar-collapse" id="menu-samping">
            <ul class="navbar-nav pt-lg-3">
                <li class="nav-item"><a class="nav-link {{ $menu('panel.dashboard') }}" href="{{ route('panel.dashboard') }}"><span class="nav-link-icon"><i class="ti ti-layout-dashboard"></i></span><span class="nav-link-title">Dashboard</span></a></li>
                <li class="nav-item"><a class="nav-link {{ $menu('panel.monitoring') }}" href="{{ route('panel.monitoring') }}"><span class="nav-link-icon"><i class="ti ti-map-pin"></i></span><span class="nav-link-title">Monitoring Presensi</span></a></li>
                <li class="nav-item"><a class="nav-link {{ $menu('panel.pengajuan.*') }}" href="{{ route('panel.pengajuan.index') }}"><span class="nav-link-icon"><i class="ti ti-calendar-event"></i></span><span class="nav-link-title">Izin / Sakit / Cuti</span>
                    @if ($jumlahMenunggu > 0)<span class="badge bg-red ms-auto">{{ $jumlahMenunggu }}</span>@endif</a></li>
                <li class="nav-item"><a class="nav-link {{ $menu('panel.kpi*') }}" href="{{ route('panel.kpi') }}"><span class="nav-link-icon"><i class="ti ti-chart-bar"></i></span><span class="nav-link-title">KPI Karyawan</span></a></li>
                <li class="nav-item"><a class="nav-link {{ $menu('panel.laporan*') }}" href="{{ route('panel.laporan') }}"><span class="nav-link-icon"><i class="ti ti-file-text"></i></span><span class="nav-link-title">Laporan &amp; Rekap</span></a></li>

                <li class="nav-item mt-3"><span class="nav-link text-secondary text-uppercase fs-5 fw-bold">Data Master</span></li>
                <li class="nav-item"><a class="nav-link {{ $menu('panel.karyawan.*') }}" href="{{ route('panel.karyawan.index') }}"><span class="nav-link-icon"><i class="ti ti-users"></i></span><span class="nav-link-title">Karyawan</span></a></li>
                <li class="nav-item"><a class="nav-link {{ $menu('panel.departemen.*') }}" href="{{ route('panel.departemen.index') }}"><span class="nav-link-icon"><i class="ti ti-building"></i></span><span class="nav-link-title">Departemen</span></a></li>
                <li class="nav-item"><a class="nav-link {{ $menu('panel.hari-libur.*') }}" href="{{ route('panel.hari-libur.index') }}"><span class="nav-link-icon"><i class="ti ti-beach"></i></span><span class="nav-link-title">Hari Libur</span></a></li>
                <li class="nav-item"><a class="nav-link {{ $menu('panel.pengaturan*') }}" href="{{ route('panel.pengaturan') }}"><span class="nav-link-icon"><i class="ti ti-adjustments"></i></span><span class="nav-link-title">Pengaturan KPI &amp; Kebijakan</span></a></li>

                @if ($user->isAdmin())
                    <li class="nav-item mt-3"><span class="nav-link text-secondary text-uppercase fs-5 fw-bold">Konfigurasi (Admin)</span></li>
                    <li class="nav-item"><a class="nav-link {{ $menu('panel.cabang.*') }}" href="{{ route('panel.cabang.index') }}"><span class="nav-link-icon"><i class="ti ti-map-2"></i></span><span class="nav-link-title">Lokasi Kantor</span></a></li>
                    <li class="nav-item"><a class="nav-link {{ $menu('panel.jam-kerja.*') }}" href="{{ route('panel.jam-kerja.index') }}"><span class="nav-link-icon"><i class="ti ti-clock"></i></span><span class="nav-link-title">Jam Kerja</span></a></li>
                    <li class="nav-item"><a class="nav-link {{ $menu('panel.log') }}" href="{{ route('panel.log') }}"><span class="nav-link-icon"><i class="ti ti-history"></i></span><span class="nav-link-title">Log Aktivitas</span></a></li>
                @endif
            </ul>
            <div class="mt-auto pt-4 pb-2 w-100">
                <div class="text-white small mb-2">{{ $user->name }} <span class="badge bg-yellow-lt text-uppercase ms-1">{{ $user->role }}</span></div>
                <form method="POST" action="{{ route('panel.logout') }}">
                    @csrf
                    <button class="btn btn-outline-light btn-sm w-100"><i class="ti ti-logout me-1"></i>Keluar</button>
                </form>
            </div>
        </div>
    </div>
</aside>
