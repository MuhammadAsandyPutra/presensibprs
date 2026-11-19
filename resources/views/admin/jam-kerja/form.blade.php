@extends('layouts.admin')

@php $edit = $jamKerja->exists; $hm = fn ($v) => $v ? substr($v, 0, 5) : ''; @endphp
@section('judul', $edit ? 'Ubah Jam Kerja' : 'Tambah Jam Kerja')

@section('content')
<form method="POST" class="card" style="max-width:720px" action="{{ $edit ? route('panel.jam-kerja.update', $jamKerja) : route('panel.jam-kerja.store') }}">
    @csrf @if ($edit) @method('PUT') @endif
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label required">Kode (4 karakter)</label>
                @if ($edit)<input type="text" class="form-control" value="{{ $jamKerja->kode_jam_kerja }}" disabled>
                @else<input type="text" name="kode_jam_kerja" class="form-control" maxlength="4" minlength="4" value="{{ old('kode_jam_kerja') }}" required>@endif</div>
            <div class="col-md-8"><label class="form-label required">Nama</label><input type="text" name="nama_jam_kerja" class="form-control" maxlength="50" value="{{ old('nama_jam_kerja', $jamKerja->nama_jam_kerja) }}" required></div>
            <div class="col-md-3"><label class="form-label required">Mulai presensi</label><input type="time" name="awal_jam_masuk" class="form-control" value="{{ old('awal_jam_masuk', $hm($jamKerja->awal_jam_masuk)) }}" required></div>
            <div class="col-md-3"><label class="form-label required">Jam masuk</label><input type="time" name="jam_masuk" class="form-control" value="{{ old('jam_masuk', $hm($jamKerja->jam_masuk)) }}" required></div>
            <div class="col-md-3"><label class="form-label required">Batas presensi masuk</label><input type="time" name="akhir_jam_masuk" class="form-control" value="{{ old('akhir_jam_masuk', $hm($jamKerja->akhir_jam_masuk)) }}" required></div>
            <div class="col-md-3"><label class="form-label required">Jam pulang</label><input type="time" name="jam_pulang" class="form-control" value="{{ old('jam_pulang', $hm($jamKerja->jam_pulang)) }}" required></div>
            <div class="col-md-4"><label class="form-label required">Toleransi terlambat (menit)</label><input type="number" name="toleransi_menit" class="form-control" min="0" max="120" value="{{ old('toleransi_menit', $jamKerja->toleransi_menit ?? 0) }}" required>
                <div class="form-hint">Tiba dalam batas ini tidak dianggap terlambat.</div></div>
        </div>
    </div>
    <div class="card-footer text-end"><a href="{{ route('panel.jam-kerja.index') }}" class="btn btn-link">Batal</a><button class="btn btn-primary">Simpan</button></div>
</form>
@endsection
