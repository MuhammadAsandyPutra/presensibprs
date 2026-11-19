@extends('layouts.admin')

@php $edit = $departemen->exists; @endphp
@section('judul', $edit ? 'Ubah Departemen' : 'Tambah Departemen')

@section('content')
<form method="POST" class="card" style="max-width:560px" action="{{ $edit ? route('panel.departemen.update', $departemen) : route('panel.departemen.store') }}">
    @csrf @if ($edit) @method('PUT') @endif
    <div class="card-body">
        <div class="mb-3"><label class="form-label required">Kode</label>
            @if ($edit)<input type="text" class="form-control" value="{{ $departemen->kode_dept }}" disabled>
            @else<input type="text" name="kode_dept" class="form-control" value="{{ old('kode_dept') }}" maxlength="10" required>@endif</div>
        <div class="mb-3"><label class="form-label required">Nama departemen</label><input type="text" name="nama_dept" class="form-control" value="{{ old('nama_dept', $departemen->nama_dept) }}" maxlength="100" required></div>
    </div>
    <div class="card-footer text-end"><a href="{{ route('panel.departemen.index') }}" class="btn btn-link">Batal</a><button class="btn btn-primary">Simpan</button></div>
</form>
@endsection
