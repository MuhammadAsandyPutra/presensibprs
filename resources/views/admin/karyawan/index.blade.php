@extends('layouts.admin')

@section('judul', 'Data Karyawan')
@section('aksi')<a href="{{ route('panel.karyawan.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Tambah Karyawan</a>@endsection

@section('content')
<form method="GET" class="card card-body mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label">Cari nama / NIK</label><input type="text" name="cari" class="form-control" value="{{ $filter['cari'] ?? '' }}"></div>
        <div class="col-md-3"><label class="form-label">Departemen</label>
            <select name="kode_dept" class="form-select"><option value="">Semua</option>
                @foreach ($departemenList as $d)<option value="{{ $d->kode_dept }}" @selected(($filter['kode_dept'] ?? '') === $d->kode_dept)>{{ $d->nama_dept }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Cabang</label>
            <select name="kode_cabang" class="form-select"><option value="">Semua</option>
                @foreach ($cabangList as $c)<option value="{{ $c->kode_cabang }}" @selected(($filter['kode_cabang'] ?? '') === $c->kode_cabang)>{{ $c->nama_cabang }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label">Status</label>
            <select name="status" class="form-select"><option value="">Semua</option>
                <option value="aktif" @selected(($filter['status'] ?? '') === 'aktif')>Aktif</option><option value="nonaktif" @selected(($filter['status'] ?? '') === 'nonaktif')>Nonaktif</option></select></div>
        <div class="col-md-1"><button class="btn btn-primary w-100"><i class="ti ti-search"></i></button></div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table card-table table-vcenter">
            <thead><tr><th>Karyawan</th><th>Jabatan</th><th>Departemen</th><th>Cabang</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse ($daftar as $k)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="avatar me-2" style="background-image:url({{ $k->foto ? route('berkas.profil', $k) : asset('assets/img/no_photo.png') }})"></span>
                            <div><div class="fw-bold">{{ $k->nama_lengkap }}</div><div class="text-secondary small">{{ $k->nik }} · {{ $k->no_hp }}</div></div>
                        </div>
                    </td>
                    <td>{{ $k->jabatan }}</td>
                    <td>{{ $k->departemen?->nama_dept }}</td>
                    <td>{{ $k->cabang?->nama_cabang }}</td>
                    <td><span class="badge bg-{{ $k->aktif ? 'green' : 'secondary' }}-lt">{{ $k->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('panel.karyawan.jadwal', $k) }}" class="btn btn-sm btn-outline-secondary" title="Jadwal kerja"><i class="ti ti-calendar-time"></i></a>
                        <a href="{{ route('panel.kpi.show', $k) }}" class="btn btn-sm btn-outline-secondary" title="KPI bulan ini"><i class="ti ti-chart-bar"></i></a>
                        <a href="{{ route('panel.karyawan.edit', $k) }}" class="btn btn-sm btn-outline-primary" title="Ubah"><i class="ti ti-edit"></i></a>
                        <form method="POST" action="{{ route('panel.karyawan.reset-password', $k) }}" class="d-inline" onsubmit="return confirm('Reset kata sandi {{ $k->nama_lengkap }}? Sandi baru akan ditampilkan sekali.')">
                            @csrf <button class="btn btn-sm btn-outline-warning" title="Reset kata sandi"><i class="ti ti-key"></i></button>
                        </form>
                        @if ($k->aktif)
                            <form method="POST" action="{{ route('panel.karyawan.destroy', $k) }}" class="d-inline" onsubmit="return confirm('Nonaktifkan {{ $k->nama_lengkap }}? Riwayat presensi tetap tersimpan.')">
                                @csrf @method('DELETE') <button class="btn btn-sm btn-outline-danger" title="Nonaktifkan"><i class="ti ti-user-off"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-4">Tidak ada data.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $daftar->links() }}</div>
</div>
@endsection
