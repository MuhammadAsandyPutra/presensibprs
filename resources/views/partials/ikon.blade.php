{{-- Ikon web & aplikasi (dipakai semua layout). Ubah ?v= bila ikon diganti agar cache browser diperbarui. --}}
@php $v = '?v=2'; @endphp
<link rel="icon" href="{{ asset('favicon.ico') }}{{ $v }}" sizes="any">
<link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/favicon.svg') }}{{ $v }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon-32.png') }}{{ $v }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon-16.png') }}{{ $v }}">
<link rel="apple-touch-icon" href="{{ asset('assets/img/apple-touch-icon.png') }}{{ $v }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}{{ $v }}">