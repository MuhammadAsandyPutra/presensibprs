<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#174D37">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>@yield('judul', 'E-Presensi') | {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/icon/192x192.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/presensi.css') }}">
    @stack('head')
</head>

<body>
    <div id="loader">
        <div class="spinner-border text-primary" role="status"></div>
    </div>

    @php $adaHeader = trim($__env->yieldContent('header')) !== ''; @endphp

    @if ($adaHeader)
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="javascript:;" class="headerButton goBack"><ion-icon name="chevron-back-outline"></ion-icon></a>
        </div>
        <div class="pageTitle">@yield('header')</div>
        <div class="right">@yield('header-kanan')</div>
    </div>
    @endif

    <div id="appCapsule" class="{{ $adaHeader ? '' : 'tanpa-header' }}">
        @if (session('sukses') || session('galat') || $errors->any())
        <div class="px-3 pt-3">@include('partials.flash')</div>
        @endif
        @yield('content')
    </div>

    @include('layouts.nav-karyawan')

    <script src="{{ asset('assets/js/lib/jquery-3.4.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/bootstrap.min.js') }}"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="{{ asset('assets/js/plugins/owl-carousel/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('assets/js/base.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
    <script>
        // Foto privat yang gagal dimuat diganti placeholder (alt text tidak lagi tampil sendirian).
        document.addEventListener('error', function(e) {
            var el = e.target;
            if (el.tagName === 'IMG' && el.src.indexOf('/berkas/') !== -1 && !el.dataset.gagal) {
                el.dataset.gagal = '1';
                el.src = "{{ asset('assets/img/no_photo.png') }}";
            }
        }, true);
    </script>
</body>

</html>