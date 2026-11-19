@extends('layouts.admin')

@php $edit = $karyawan->exists; @endphp
@section('judul', $edit ? 'Ubah Karyawan' : 'Tambah Karyawan')

@section('content')
<form method="POST" enctype="multipart/form-data" class="card"
      action="{{ $edit ? route('panel.karyawan.update', $karyawan) : route('panel.karyawan.store') }}">
    @csrf
    @if ($edit) @method('PUT') @endif
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label required">NIK</label>
                @if ($edit)
                    <input type="text" class="form-control" value="{{ $karyawan->nik }}" disabled>
                @else
                    <input type="text" name="nik" class="form-control" value="{{ old('nik') }}" maxlength="20" required>
                @endif
            </div>
            <div class="col-md-8"><label class="form-label required">Nama lengkap</label><input type="text" name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap', $karyawan->nama_lengkap) }}" maxlength="100" required></div>
            <div class="col-md-4"><label class="form-label">Jabatan</label><input type="text" name="jabatan" class="form-control" value="{{ old('jabatan', $karyawan->jabatan) }}" maxlength="60"></div>
            <div class="col-md-4"><label class="form-label">No. HP</label><input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $karyawan->no_hp) }}" maxlength="20"></div>
            <div class="col-md-4"><label class="form-label">Jatah cuti / tahun (hari)</label><input type="number" name="jatah_cuti" class="form-control" min="0" max="60" value="{{ old('jatah_cuti', $karyawan->jatah_cuti ?? \App\Models\Pengaturan::ambil('cuti.jatah_tahunan')) }}"></div>
            <div class="col-md-6"><label class="form-label">Departemen</label>
                <select name="kode_dept" class="form-select"><option value="">— Pilih —</option>
                    @foreach ($departemenList as $d)<option value="{{ $d->kode_dept }}" @selected(old('kode_dept', $karyawan->kode_dept) === $d->kode_dept)>{{ $d->nama_dept }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label required">Cabang / lokasi presensi</label>
                <select name="kode_cabang" class="form-select" required><option value="">— Pilih —</option>
                    @foreach ($cabangList as $c)<option value="{{ $c->kode_cabang }}" @selected(old('kode_cabang', $karyawan->kode_cabang) === $c->kode_cabang)>{{ $c->nama_cabang }}</option>@endforeach</select></div>
            <div class="col-md-6">
                <label class="form-label {{ $edit ? '' : 'required' }}">Kata sandi {{ $edit ? '(kosongkan bila tidak diubah)' : '' }}</label>
                <input type="password" name="password" class="form-control" minlength="8" autocomplete="new-password" {{ $edit ? '' : 'required' }}>
                <div class="form-hint">Minimal 8 karakter.</div>
            </div>
            <div class="col-md-6"><label class="form-label">Foto profil (opsional)</label><input type="file" name="foto" class="form-control" accept="image/png,image/jpeg"></div>
            <div class="col-12">
                <input type="hidden" name="aktif" value="0">
                <label class="form-check"><input type="checkbox" name="aktif" value="1" class="form-check-input" @checked(old('aktif', $karyawan->aktif))><span class="form-check-label">Karyawan aktif (dapat login &amp; presensi)</span></label>
            </div>
        </div>
    </div>
    <div class="card-footer text-end"><a href="{{ route('panel.karyawan.index') }}" class="btn btn-link">Batal</a><button class="btn btn-primary">Simpan</button></div>
</form>
@endsection
