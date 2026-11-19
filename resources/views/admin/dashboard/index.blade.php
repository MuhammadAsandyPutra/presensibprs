@extends('layouts.admin')

@section('judul', 'Dashboard')
@section('aksi')<span class="text-secondary">{{ now()->translatedFormat('l, d F Y') }}</span>@endsection

@section('content')
@php $maks = max(1, $grafik->max('jumlah')); @endphp

<div class="row row-cards mb-3">
    @foreach ([
        ['Karyawan aktif', $total, 'ti-users', 'primary', route('panel.karyawan.index')],
        ['Hadir hari ini', $hadir, 'ti-user-check', 'green', route('panel.monitoring')],
        ['Terlambat', $terlambat, 'ti-clock-exclamation', 'orange', route('panel.monitoring')],
        ['Izin / Sakit / Cuti', $izin, 'ti-calendar-event', 'azure', route('panel.pengajuan.index')],
        ['Belum presensi', $belum->count(), 'ti-user-question', 'red', route('panel.monitoring')],
        ['Pengajuan menunggu', $menunggu, 'ti-hourglass', 'yellow', route('panel.pengajuan.index', ['status' => 0])],
    ] as [$label, $nilai, $ikon, $warna, $url])
        <div class="col-6 col-lg-2">
            <a href="{{ $url }}" class="card card-sm text-decoration-none">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <span class="avatar bg-{{ $warna }}-lt me-3"><i class="ti {{ $ikon }} fs-2"></i></span>
                        <div><div class="h2 mb-0">{{ $nilai }}</div><div class="text-secondary small">{{ $label }}</div></div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

@if ($perluReview > 0)
    <div class="alert alert-warning"><i class="ti ti-alert-triangle me-1"></i>
        {{ $perluReview }} presensi 7 hari terakhir ditandai <strong>perlu review</strong> (akurasi GPS rendah atau koordinat berulang). Periksa di <a href="{{ route('panel.monitoring') }}">Monitoring</a>.
    </div>
@endif

<div class="row row-cards">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Kehadiran 7 hari terakhir</h3></div>
            <div class="card-body">
                <div class="d-flex align-items-end justify-content-between" style="height:180px;gap:10px">
                    @foreach ($grafik as $g)
                        <div class="text-center flex-fill">
                            <div class="small fw-bold">{{ $g['jumlah'] }}</div>
                            <div class="bg-primary rounded-top" style="height:{{ max(4, $g['jumlah'] / $maks * 130) }}px"></div>
                            <div class="small text-secondary mt-1">{{ $g['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Belum presensi hari ini</h3><div class="ms-auto badge bg-red-lt">{{ $belum->count() }}</div></div>
            <div class="list-group list-group-flush" style="max-height:220px;overflow:auto">
                @forelse ($belum as $x)
                    <div class="list-group-item d-flex justify-content-between">
                        <span>{{ $x['karyawan']->nama_lengkap }}</span>
                        <span class="text-secondary small">{{ $x['karyawan']->departemen?->nama_dept }}</span>
                    </div>
                @empty
                    <div class="list-group-item text-secondary">Semua karyawan sudah presensi atau tidak berjadwal.</div>
                @endforelse
            </div>
        </div>
    </div>

    @foreach ([['KPI tertinggi bulan ini', $kpiTerbaik], ['KPI perlu perhatian', $kpiTerendah]] as [$judul, $daftar])
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">{{ $judul }}</h3><div class="ms-auto"><a href="{{ route('panel.kpi') }}">Selengkapnya</a></div></div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <tbody>
                        @forelse ($daftar as $h)
                            <tr>
                                <td><a href="{{ route('panel.kpi.show', $h->karyawan) }}">{{ $h->karyawan->nama_lengkap }}</a><div class="text-secondary small">{{ $h->karyawan->departemen?->nama_dept }}</div></td>
                                <td class="text-end"><span class="badge bg-{{ $h->kategori->warna() }}-lt">{{ $h->kategori->label() }}</span></td>
                                <td class="text-end fw-bold" style="width:70px">{{ number_format($h->skor, 1) }}</td>
                            </tr>
                        @empty
                            <tr><td class="text-secondary">Belum ada data.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
