@extends('layouts.admin')

@section('judul', 'KPI: '.$karyawan->nama_lengkap)
@section('aksi')
    <form method="GET" class="d-flex gap-2">
        <select name="bulan" class="form-select" onchange="this.form.submit()">@foreach (\App\Support\Waktu::NAMA_BULAN as $no => $nama)<option value="{{ $no }}" @selected($no === $bulan)>{{ $nama }}</option>@endforeach</select>
        <select name="tahun" class="form-select" onchange="this.form.submit()">@foreach (range(now()->year, now()->year - 3) as $t)<option value="{{ $t }}" @selected($t === $tahun)>{{ $t }}</option>@endforeach</select>
    </form>
@endsection

@section('content')
<div class="row row-cards mb-3">
    <div class="col-6 col-lg-3"><div class="card card-sm"><div class="card-body"><div class="text-secondary small">Skor KPI · {{ $periode }}</div><div class="h1 mb-0">{{ number_format($ringkas->skor, 1) }}</div><span class="badge bg-{{ $ringkas->kategori->warna() }}-lt">{{ $ringkas->kategori->label() }}</span></div></div></div>
    <div class="col-6 col-lg-3"><div class="card card-sm"><div class="card-body"><div class="text-secondary small">Kehadiran</div><div class="h1 mb-0">{{ number_format($ringkas->kehadiran, 0) }}%</div><div class="small text-secondary">{{ $ringkas->hadir }} dari {{ $ringkas->hariEfektif() }} hari efektif</div></div></div></div>
    <div class="col-6 col-lg-3"><div class="card card-sm"><div class="card-body"><div class="text-secondary small">Ketepatan waktu</div><div class="h1 mb-0">{{ number_format($ringkas->ketepatan, 0) }}%</div><div class="small text-secondary">{{ $ringkas->terlambatKali }}× terlambat ({{ $ringkas->menitTerlambat }} mnt)</div></div></div></div>
    <div class="col-6 col-lg-3"><div class="card card-sm"><div class="card-body"><div class="text-secondary small">Disiplin</div><div class="h1 mb-0">{{ number_format($ringkas->disiplin, 0) }}%</div><div class="small text-secondary">{{ $ringkas->alpa }} alpa · {{ $ringkas->pulangCepat }} pulang cepat</div></div></div></div>
</div>

<div class="card"><div class="table-responsive">
    <table class="table table-sm card-table table-vcenter">
        <thead><tr><th>Tanggal</th><th>Hari</th><th>Masuk</th><th>Pulang</th><th>Status</th><th>Terlambat</th><th>Catatan</th></tr></thead>
        <tbody>
        @forelse ($baris as $b)
            <tr>
                <td>{{ $b['tanggal']->format('d/m/Y') }}</td><td>{{ $b['hari'] }}</td>
                <td>{{ \App\Support\Waktu::hm($b['jam_in']) }}</td><td>{{ \App\Support\Waktu::hm($b['jam_out']) }}</td>
                <td><span class="badge bg-{{ $b['status']->warna() }}-lt">{{ $b['status']->label() }}</span></td>
                <td>{{ $b['terlambat_menit'] > 0 ? $b['terlambat_menit'].' mnt' : '' }}</td>
                <td>{{ $b['keterangan'] }}{{ $b['pulang_cepat'] ? 'Pulang lebih awal' : '' }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-secondary py-4">Periode belum dimulai.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
