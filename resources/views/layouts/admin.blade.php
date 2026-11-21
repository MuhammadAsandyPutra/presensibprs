<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('judul', 'Panel') | {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    {{-- Tabler dimuat dari CDN. Ingin offline? Unduh paketnya ke public/tabler dan ganti URL di bawah. --}}
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        :root {
            --tblr-primary: #174d37;
            --tblr-primary-rgb: 23, 77, 55;
            --tblr-link-color: #174d37;
            --tblr-link-color-rgb: 23, 77, 55;
        }

        .navbar-vertical {
            background: #103b2a !important;
        }

        .navbar-vertical .nav-link.active {
            background: rgba(198, 161, 91, .18);
            color: #f1d9a3 !important;
        }

        .progress-skor {
            height: 8px;
        }

        @media print {

            .navbar-vertical,
            .page-header,
            .d-print-none {
                display: none !important;
            }

            .page-wrapper {
                margin: 0 !important;
            }
        }
    </style>
    @stack('head')
</head>

<body>
    <div class="page">
        @include('layouts.sidebar-admin')

        <div class="page-wrapper">
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <h2 class="page-title">@yield('judul')</h2>
                        </div>
                        <div class="col-auto ms-auto d-print-none">@yield('aksi')</div>
                    </div>
                </div>
            </div>
            <div class="page-body">
                <div class="container-xl">
                    @include('partials.flash')
                    @yield('content')
                </div>
            </div>
            <footer class="footer footer-transparent d-print-none">
                <div class="container-xl text-secondary">&copy; {{ date('Y') }} PT BPRS Barkah Gemadana &middot; Sistem Presensi</div>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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