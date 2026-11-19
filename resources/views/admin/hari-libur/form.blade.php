@extends('layouts.admin')

@php $edit = $hariLibur->exists; @endphp
@section('judul', $edit ? 'Ubah Hari Libur' : 'Tambah Hari Libur')

@section('content')
<form method="POST" class="card" style="max-width:520px" action="{{ $edit ? route('panel.hari-libur.update', $hariLibur) : route('panel.hari-libur.store') }}">
    @csrf @if ($edit) @method('PUT') @endif
    <div class="card-body">
        <div class="mb-3"><label class="form-label required">Tanggal</label><input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', $hariLibur->tanggal?->toDateString()) }}" required></div>
        <div class="mb-3"><label class="form-label required">Keterangan</label><input type="text" name="keterangan" class="form-control" maxlength="100" value="{{ old('keterangan', $hariLibur->keterangan) }}" required></div>
    </div>
    <div class="card-footer text-end"><a href="{{ route('panel.hari-libur.index') }}" class="btn btn-link">Batal</a><button class="btn btn-primary">Simpan</button></div>
</form>
@endsection
