{{-- Pesan sukses/galat dan daftar kesalahan validasi (dipakai kedua layout) --}}
@if (session('sukses'))
    <div class="alert alert-success" role="alert">{{ session('sukses') }}</div>
@endif
@if (session('galat'))
    <div class="alert alert-danger" role="alert">{{ session('galat') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Periksa kembali isian Anda:</strong>
        <ul class="mb-0 pl-3 ps-3">
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
@endif
