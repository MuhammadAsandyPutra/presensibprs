@extends('layouts.admin')

@section('judul', 'KPI Disiplin Karyawan')
@section('aksi')
    <a href="{{ route('panel.pengaturan') }}" class="btn btn-outline-secondary"><i class="ti ti-adjustments me-1"></i>Atur bobot</a>
    <a href="{{ route('panel.kpi.csv', request()->query()) }}" class="btn btn-primary"><i class="ti ti-download me-1"></i>Unduh CSV</a>
@endsection

@section('content')
<form method="GET" class="card card-body mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label">Bulan</label><select name="bulan" class="form-select">@foreach (\App\Support\Waktu::NAMA_BULAN as $no => $nama)<option value="{{ $no }}" @selected($no === $bulan)>{{ $nama }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label">Tahun</label><select name="tahun" class="form-select">@foreach (range(now()->year, now()->year - 3) as $t)<option value="{{ $t }}" @selected($t === $tahun)>{{ $t }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label">Departemen</label>
            <select name="kode_dept" class="form-select"><option value="">Semua departemen</option>
                @foreach ($departemenList as $d)<option value="{{ $d->kode_dept }}" @selected($kodeDept === $d->kode_dept)>{{ $d->nama_dept }}</option>@endforeach</select></div>
        <div class="col-md-2"><button class="btn btn-primary w-100"><i class="ti ti-search me-1"></i>Tampilkan</button></div>
    </div>
</form>

@if ($hasil->isEmpty())
    <div class="card card-body text-center text-secondary py-5">Belum ada data KPI untuk periode ini.</div>
@else
    <div class="row row-cards mb-3">
        <div class="col-6 col-lg-3"><div class="card card-sm"><div class="card-body"><div class="text-secondary small">Rata-rata skor</div><div class="h1 mb-0">{{ number_format($rataRata, 1) }}</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card card-sm"><div class="card-body"><div class="text-secondary small">Sangat Baik</div><div class="h1 mb-0 text-green">{{ $hasil->filter(fn ($h) => $h->kategori === \App\Enums\KategoriKpi::SangatBaik)->count() }}</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card card-sm"><div class="card-body"><div class="text-secondary small">Total menit terlambat</div><div class="h1 mb-0 text-orange">{{ $hasil->sum('menitTerlambat') }}</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card card-sm"><div class="card-body"><div class="text-secondary small">Perlu pembinaan</div><div class="h1 mb-0 text-red">{{ $hasil->filter(fn ($h) => $h->kategori === \App\Enums\KategoriKpi::Kurang)->count() }}</div></div></div></div>
    </div>

    <div class="row row-cards">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Peringkat · {{ \App\Support\Waktu::namaBulan($bulan) }} {{ $tahun }}</h3></div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead><tr><th>#</th><th>Karyawan</th><th>Hari kerja</th><th>Hadir</th><th>Terlambat</th><th>Alpa</th><th>Kehadiran</th><th>Ketepatan</th><th style="min-width:160px">Skor</th></tr></thead>
                        <tbody>
                        @foreach ($hasil as $i => $h)
                            <tr>
                                <td class="text-secondary">{{ $i + 1 }}</td>
                                <td><a href="{{ route('panel.kpi.show', ['karyawan' => $h->karyawan, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="fw-bold">{{ $h->karyawan->nama_lengkap }}</a>
                                    <div class="text-secondary small">{{ $h->karyawan->departemen?->nama_dept }}</div></td>
                                <td>{{ $h->hariKerja }}</td><td>{{ $h->hadir }}</td>
                                <td>{{ $h->terlambatKali }}× <span class="text-secondary small">({{ $h->menitTerlambat }} mnt)</span></td>
                                <td class="{{ $h->alpa ? 'text-danger fw-bold' : '' }}">{{ $h->alpa }}</td>
                                <td>{{ number_format($h->kehadiran, 0) }}%</td><td>{{ number_format($h->ketepatan, 0) }}%</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress progress-skor flex-fill"><div class="progress-bar bg-{{ $h->kategori->warna() }}" style="width:{{ min(100, $h->skor) }}%"></div></div>
                                        <strong>{{ number_format($h->skor, 1) }}</strong>
                                    </div>
                                    <span class="badge bg-{{ $h->kategori->warna() }}-lt mt-1">{{ $h->kategori->label() }}</span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Rata-rata per departemen</h3></div>
                <div class="list-group list-group-flush">
                    @foreach ($perDept as $d)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between"><span>{{ $d['nama'] }}</span><strong>{{ number_format($d['skor'], 1) }}</strong></div>
                            <div class="progress progress-skor mt-1"><div class="progress-bar" style="width:{{ min(100, $d['skor']) }}%"></div></div>
                            <div class="text-secondary small">{{ $d['jumlah'] }} karyawan</div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card mt-3"><div class="card-body small text-secondary">
                <strong>Cara hitung:</strong> skor = rata-rata berbobot dari kehadiran, ketepatan waktu, dan disiplin. Izin/sakit/cuti yang disetujui dan hari libur tidak mengurangi kehadiran. Ubah bobot di menu Pengaturan.
            </div></div>
        </div>
    </div>
@endif
@endsection
