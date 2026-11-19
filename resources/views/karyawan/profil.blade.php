@extends('layouts.karyawan')

@section('judul', 'Profil')
@section('header', 'Profil Saya')

@section('content')
<div class="kartu text-center" style="margin-top:12px">
    <img src="{{ $karyawan->foto ? route('berkas.profil', $karyawan) : asset('assets/img/no_photo.png') }}" alt="Foto" class="rounded-circle" width="88" height="88" style="object-fit:cover">
    <div style="font-size:18px;font-weight:700;margin-top:8px">{{ $karyawan->nama_lengkap }}</div>
    <div style="font-size:13px;color:var(--redam)">NIK {{ $karyawan->nik }} · {{ $karyawan->jabatan }}</div>
    <div style="font-size:13px;color:var(--redam)">{{ $karyawan->departemen?->nama_dept }} · {{ $karyawan->cabang?->nama_cabang }}</div>
</div>

<form method="POST" action="{{ route('profil.update') }}" enctype="multipart/form-data" class="kartu form-mobile">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="nama_lengkap">Nama lengkap</label>
        <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap', $karyawan->nama_lengkap) }}" required>
    </div>
    <div class="form-group">
        <label for="no_hp">No. HP</label>
        <input type="tel" id="no_hp" name="no_hp" class="form-control" value="{{ old('no_hp', $karyawan->no_hp) }}">
    </div>
    <div class="form-group">
        <label for="foto">Foto profil (JPG/PNG, maks 2 MB)</label>
        <input type="file" id="foto" name="foto" class="form-control" accept="image/png,image/jpeg" style="padding:6px">
    </div>

    <hr>
    <div style="font-weight:600;margin-bottom:6px">Ganti kata sandi <span class="text-muted" style="font-weight:400">(kosongkan bila tidak diganti)</span></div>
    <div class="form-group">
        <label for="password_lama">Kata sandi lama</label>
        <input type="password" id="password_lama" name="password_lama" class="form-control" autocomplete="current-password">
    </div>
    <div class="form-group">
        <label for="password">Kata sandi baru (min. 8 karakter)</label>
        <input type="password" id="password" name="password" class="form-control" autocomplete="new-password">
    </div>
    <div class="form-group">
        <label for="password_confirmation">Ulangi kata sandi baru</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg">Simpan Perubahan</button>
</form>

<form method="POST" action="{{ route('logout') }}" class="px-3">
    @csrf
    <button type="submit" class="btn btn-outline-danger btn-block mb-3">Keluar dari akun</button>
</form>
@endsection
