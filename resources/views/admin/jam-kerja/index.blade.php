@extends('layouts.admin')

@section('judul', 'Jam Kerja')
@section('aksi')<a href="{{ route('panel.jam-kerja.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Tambah Jam Kerja</a>@endsection

@section('content')
<div class="card"><div class="table-responsive">
    <table class="table card-table table-vcenter">
        <thead><tr><th>Kode</th><th>Nama</th><th>Mulai presensi</th><th>Jam masuk</th><th>Batas presensi masuk</th><th>Jam pulang</th><th>Toleransi</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse ($daftar as $j)
            <tr>
                <td>{{ $j->kode_jam_kerja }}</td><td>{{ $j->nama_jam_kerja }}</td>
                <td>{{ \App\Support\Waktu::hm($j->awal_jam_masuk) }}</td><td>{{ \App\Support\Waktu::hm($j->jam_masuk) }}</td>
                <td>{{ \App\Support\Waktu::hm($j->akhir_jam_masuk) }}</td><td>{{ \App\Support\Waktu::hm($j->jam_pulang) }}</td>
                <td>{{ $j->toleransi_menit }} menit</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('panel.jam-kerja.edit', $j) }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-edit"></i></a>
                    <form method="POST" action="{{ route('panel.jam-kerja.destroy', $j) }}" class="d-inline" onsubmit="return confirm('Hapus jam kerja {{ $j->nama_jam_kerja }}?')">
                        @csrf @method('DELETE') <button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-secondary py-4">Belum ada jam kerja. Tambahkan minimal satu agar karyawan dapat presensi.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
