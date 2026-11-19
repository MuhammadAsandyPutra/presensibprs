@extends('layouts.admin')

@section('judul', 'Departemen')
@section('aksi')<a href="{{ route('panel.departemen.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Tambah Departemen</a>@endsection

@section('content')
<div class="card"><div class="table-responsive">
    <table class="table card-table table-vcenter">
        <thead><tr><th>Kode</th><th>Nama</th><th>Jumlah karyawan</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse ($daftar as $d)
            <tr>
                <td>{{ $d->kode_dept }}</td><td>{{ $d->nama_dept }}</td><td>{{ $d->karyawan_count }}</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('panel.departemen.edit', $d) }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-edit"></i></a>
                    <form method="POST" action="{{ route('panel.departemen.destroy', $d) }}" class="d-inline" onsubmit="return confirm('Hapus departemen {{ $d->nama_dept }}?')">
                        @csrf @method('DELETE') <button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada departemen.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
