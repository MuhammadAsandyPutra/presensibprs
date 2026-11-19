@extends('layouts.admin')

@section('judul', 'Jadwal Kerja: '.$karyawan->nama_lengkap)

@section('content')
<form method="POST" action="{{ route('panel.karyawan.jadwal.update', $karyawan) }}" class="card" style="max-width:640px">
    @csrf @method('PUT')
    <div class="card-body">
        @unless ($adaJadwal)
            <div class="alert alert-info">Karyawan ini belum punya jadwal khusus, sehingga memakai jam kerja pertama untuk Senin–Jumat. Simpan di bawah untuk mengatur sendiri.</div>
        @endunless
        <p class="text-secondary">Pilih jam kerja untuk setiap hari. Kosongkan untuk hari libur mingguan.</p>
        @foreach (config('presensi.hari') as $nama)
            <div class="row mb-2 align-items-center">
                <div class="col-4 fw-bold">{{ $nama }}</div>
                <div class="col-8">
                    <select name="hari[{{ $nama }}]" class="form-select">
                        <option value="">Libur</option>
                        @foreach ($jamKerjaList as $j)
                            <option value="{{ $j->kode_jam_kerja }}" @selected(($peta[$nama] ?? null) === $j->kode_jam_kerja)>
                                {{ $j->nama_jam_kerja }} ({{ \App\Support\Waktu::hm($j->jam_masuk) }}–{{ \App\Support\Waktu::hm($j->jam_pulang) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endforeach
    </div>
    <div class="card-footer text-end"><a href="{{ route('panel.karyawan.index') }}" class="btn btn-link">Batal</a><button class="btn btn-primary">Simpan Jadwal</button></div>
</form>
@endsection
