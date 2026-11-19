@extends('layouts.auth', ['peran' => 'admin'])

@section('judul', 'Masuk Panel')

@section('form')
    <div class="section-heading">
        <h2>Panel HR &amp; Admin</h2>
        <p>Khusus petugas HR dan administrator sistem.</p>
    </div>

    <form action="{{ route('panel.login.proses') }}" method="POST" autocomplete="on">
        @csrf
        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <div class="input-wrapper">
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" autocomplete="username" required autofocus>
            </div>
        </div>
        <div class="form-group">
            <label for="password" class="form-label">Kata Sandi</label>
            <div class="input-wrapper">
                <input type="password" id="password" name="password" class="form-control" autocomplete="current-password" required>
            </div>
        </div>
        <button type="submit" class="btn-login">Masuk</button>
    </form>

    <p class="catatan-login"><a href="{{ route('login') }}">&larr; Portal karyawan</a></p>
@endsection
