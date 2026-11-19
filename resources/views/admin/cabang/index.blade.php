@extends('layouts.admin')

@section('judul', 'Lokasi Kantor (Cabang)')
@section('aksi')<a href="{{ route('panel.cabang.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Tambah Cabang</a>@endsection

@section('content')
<div class="card"><div class="table-responsive">
    <table class="table card-table table-vcenter">
        <thead><tr><th>Kode</th><th>Nama</th><th>Koordinat</th><th>Radius</th><th>Karyawan</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse ($daftar as $c)
            <tr>
                <td>{{ $c->kode_cabang }}</td><td>{{ $c->nama_cabang }}</td>
                <td><code>{{ $c->lokasi_cabang }}</code></td><td>{{ $c->radius_cabang }} m</td><td>{{ $c->karyawan_count }}</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('panel.cabang.edit', $c) }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-edit"></i></a>
                    <form method="POST" action="{{ route('panel.cabang.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Hapus cabang {{ $c->nama_cabang }}?')">
                        @csrf @method('DELETE') <button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada cabang.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
