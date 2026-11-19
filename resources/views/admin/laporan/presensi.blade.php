@extends('layouts.admin')

@section('judul', 'Laporan Presensi')
@section('aksi')<button onclick="window.print()" class="btn btn-primary d-print-none"><i class="ti ti-printer me-1"></i>Cetak / PDF</button>@endsection

@section('content')
@php $h = fn (\App\Enums\StatusHarian ...$s) => $baris->filter(fn ($b) => in_array($b['status'], $s, true))->count(); @endphp
<div class="card">
    <div class="card-body">
        <div class="text-center mb-3">
            <h3 class="mb-0">LAPORAN PRESENSI KARYAWAN</h3>
            <div class="text-secondary">PT BPRS Barkah Gemadana · Periode {{ $periode }}</div>
        </div>
        <table class="table table-sm table-borderless w-auto mb-3">
            <tr><td class="text-secondary pe-4">NIK</td><td>{{ $karyawan->nik }}</td></tr>
            <tr><td class="text-secondary pe-4">Nama</td><td>{{ $karyawan->nama_lengkap }}</td></tr>
            <tr><td class="text-secondary pe-4">Jabatan / Departemen</td><td>{{ $karyawan->jabatan }} / {{ $karyawan->departemen?->nama_dept }}</td></tr>
        </table>

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-vcenter">
                <thead><tr><th>Tanggal</th><th>Hari</th><th>Masuk</th><th>Pulang</th><th>Status</th><th>Terlambat</th><th>Keterangan</th></tr></thead>
                <tbody>
                @foreach ($baris as $b)
                    <tr class="{{ in_array($b['status']->value, ['libur', 'tanpa_jadwal'], true) ? 'bg-light text-secondary' : '' }}">
                        <td>{{ $b['tanggal']->format('d/m/Y') }}</td><td>{{ $b['hari'] }}</td>
                        <td>{{ \App\Support\Waktu::hm($b['jam_in']) }}</td><td>{{ \App\Support\Waktu::hm($b['jam_out']) }}</td>
                        <td>{{ $b['status']->label() }}</td>
                        <td>{{ $b['terlambat_menit'] > 0 ? $b['terlambat_menit'].' mnt' : '' }}</td>
                        <td>{{ $b['keterangan'] }}{{ $b['pulang_cepat'] ? ' Pulang lebih awal.' : '' }}{{ $b['presensi']?->perlu_review ? ' (ditandai review)' : '' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <strong>Ringkasan:</strong>
            Hadir {{ $h(\App\Enums\StatusHarian::Hadir, \App\Enums\StatusHarian::Terlambat) }} hari ·
            Terlambat {{ $h(\App\Enums\StatusHarian::Terlambat) }} kali ({{ $baris->sum('terlambat_menit') }} menit) ·
            Izin {{ $h(\App\Enums\StatusHarian::Izin) }} · Sakit {{ $h(\App\Enums\StatusHarian::Sakit) }} · Cuti {{ $h(\App\Enums\StatusHarian::Cuti) }} ·
            Tanpa keterangan {{ $h(\App\Enums\StatusHarian::Alpa) }}
        </div>
        <div class="text-secondary small mt-3">Dicetak {{ now()->translatedFormat('d F Y H:i') }}</div>
    </div>
</div>
@endsection
