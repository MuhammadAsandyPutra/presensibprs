@extends('layouts.admin')

@section('judul', 'Rekap Presensi '.$periode)
@section('aksi')
    <a href="{{ route('panel.laporan.rekap.csv', request()->query()) }}" class="btn btn-outline-secondary"><i class="ti ti-download me-1"></i>CSV</a>
    <button onclick="window.print()" class="btn btn-primary d-print-none"><i class="ti ti-printer me-1"></i>Cetak</button>
@endsection

@section('content')
<div class="card mb-3 d-print-none"><div class="card-body py-2">
    @foreach ($legenda as $l)
        <span class="me-3"><span class="badge bg-{{ $l->warna() }}-lt">{{ $l->kode() }}</span> {{ $l->label() }}</span>
    @endforeach
</div></div>

<div class="card"><div class="table-responsive">
    <table class="table table-sm table-bordered table-vcenter text-center" style="font-size:12px">
        <thead>
            <tr>
                <th class="text-start">Karyawan</th>
                @for ($i = 1; $i <= $jumlahHari; $i++)<th>{{ $i }}</th>@endfor
                <th>H</th><th>T</th><th>I</th><th>S</th><th>C</th><th>A</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($karyawan as $k)
            @php
                $hari = $matriks[$k->nik];
                $n = fn (\App\Enums\StatusHarian ...$s) => $hari->filter(fn ($b) => in_array($b['status'], $s, true))->count();
            @endphp
            <tr>
                <td class="text-start text-nowrap">{{ $k->nama_lengkap }}</td>
                @foreach ($hari as $b)
                    <td class="p-0"><span class="d-block py-1 bg-{{ $b['status']->warna() }}-lt" title="{{ $b['tanggal']->format('d/m') }}: {{ $b['status']->label() }}">{{ $b['status']->kode() }}</span></td>
                @endforeach
                <td class="fw-bold">{{ $n(\App\Enums\StatusHarian::Hadir, \App\Enums\StatusHarian::Terlambat) }}</td>
                <td>{{ $n(\App\Enums\StatusHarian::Terlambat) }}</td><td>{{ $n(\App\Enums\StatusHarian::Izin) }}</td>
                <td>{{ $n(\App\Enums\StatusHarian::Sakit) }}</td><td>{{ $n(\App\Enums\StatusHarian::Cuti) }}</td>
                <td class="text-danger fw-bold">{{ $n(\App\Enums\StatusHarian::Alpa) }}</td>
            </tr>
        @empty
            <tr><td colspan="{{ $jumlahHari + 7 }}" class="text-center text-secondary py-4">Tidak ada karyawan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
