@extends('layouts.admin')

@section('judul', 'Laporan & Rekap')

@section('content')
@php $bulanIni = now()->month; $tahunIni = now()->year; @endphp
<div class="row row-cards">
    <div class="col-lg-6">
        <form method="GET" action="{{ route('panel.laporan.presensi') }}" class="card">
            <div class="card-header"><h3 class="card-title">Laporan presensi per karyawan</h3></div>
            <div class="card-body">
                <div class="mb-3"><label class="form-label required">Karyawan</label>
                    <select name="nik" class="form-select" required>
                        <option value="">— Pilih —</option>
                        @foreach ($karyawanList as $k)<option value="{{ $k->nik }}">{{ $k->nama_lengkap }} ({{ $k->nik }})</option>@endforeach
                    </select></div>
                <div class="row g-2">
                    <div class="col-7"><label class="form-label">Bulan</label><select name="bulan" class="form-select">@foreach (\App\Support\Waktu::NAMA_BULAN as $no => $nama)<option value="{{ $no }}" @selected($no === $bulanIni)>{{ $nama }}</option>@endforeach</select></div>
                    <div class="col-5"><label class="form-label">Tahun</label><select name="tahun" class="form-select">@foreach (range($tahunIni, $tahunIni - 3) as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach</select></div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button name="aksi" value="csv" formaction="{{ route('panel.laporan.presensi.csv') }}" class="btn btn-outline-secondary"><i class="ti ti-download me-1"></i>CSV</button>
                <button class="btn btn-primary"><i class="ti ti-file-text me-1"></i>Tampilkan / Cetak</button>
            </div>
        </form>
    </div>
    <div class="col-lg-6">
        <form method="GET" action="{{ route('panel.laporan.rekap') }}" class="card">
            <div class="card-header"><h3 class="card-title">Rekap presensi bulanan (semua karyawan)</h3></div>
            <div class="card-body">
                <div class="mb-3"><label class="form-label">Departemen</label>
                    <select name="kode_dept" class="form-select"><option value="">Semua departemen</option>
                        @foreach ($departemenList as $d)<option value="{{ $d->kode_dept }}">{{ $d->nama_dept }}</option>@endforeach</select></div>
                <div class="row g-2">
                    <div class="col-7"><label class="form-label">Bulan</label><select name="bulan" class="form-select">@foreach (\App\Support\Waktu::NAMA_BULAN as $no => $nama)<option value="{{ $no }}" @selected($no === $bulanIni)>{{ $nama }}</option>@endforeach</select></div>
                    <div class="col-5"><label class="form-label">Tahun</label><select name="tahun" class="form-select">@foreach (range($tahunIni, $tahunIni - 3) as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach</select></div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button formaction="{{ route('panel.laporan.rekap.csv') }}" class="btn btn-outline-secondary"><i class="ti ti-download me-1"></i>CSV</button>
                <button class="btn btn-primary"><i class="ti ti-table me-1"></i>Tampilkan</button>
            </div>
        </form>
    </div>
</div>
@endsection
