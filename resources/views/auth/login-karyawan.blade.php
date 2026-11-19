@extends('layouts.auth', ['peran' => 'karyawan'])

@section('judul', 'Masuk Karyawan')

@section('form')
    <div class="section-heading">
        <h2>Selamat Datang</h2>
        <p>Masuk menggunakan NIK dan kata sandi Anda.</p>
    </div>

    <form action="{{ route('login.proses') }}" method="POST" autocomplete="on">
        @csrf
        <div class="form-group">
            <label for="nik" class="form-label">Nomor Induk Karyawan (NIK)</label>
            <div class="input-wrapper">
                <input type="text" id="nik" name="nik" class="form-control" value="{{ old('nik') }}" inputmode="text" autocomplete="username" required autofocus>
            </div>
        </div>
        <div class="form-group">
            <label for="password" class="form-label">Kata Sandi</label>
            <div class="input-wrapper">
                <input type="password" id="password" name="password" class="form-control" autocomplete="current-password" required>
            </div>
        </div>
        <label class="d-flex align-items-center mb-3" style="gap:8px;font-size:14px">
            <input type="checkbox" name="ingat" value="1"> Ingat saya di perangkat ini
        </label>
        <button type="submit" class="btn-login">Masuk</button>
    </form>

    <p class="catatan-login">Lupa kata sandi? Hubungi HR untuk mereset akun Anda.</p>
@endsection
