@extends('layouts.admin')

@section('judul', 'Hari Libur')
@section('aksi')<a href="{{ route('panel.hari-libur.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Tambah Hari Libur</a>@endsection

@section('content')
<form method="GET" class="mb-3 d-flex gap-2" style="max-width:260px">
    <select name="tahun" class="form-select" onchange="this.form.submit()">
        @foreach (range(now()->year + 1, now()->year - 2) as $t)<option value="{{ $t }}" @selected($t === $tahun)>{{ $t }}</option>@endforeach
    </select>
</form>
<div class="card"><div class="table-responsive">
    <table class="table card-table table-vcenter">
        <thead><tr><th>Tanggal</th><th>Keterangan</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse ($daftar as $h)
            <tr>
                <td>{{ $h->tanggal->translatedFormat('l, d F Y') }}</td><td>{{ $h->keterangan }}</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('panel.hari-libur.edit', $h) }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-edit"></i></a>
                    <form method="POST" action="{{ route('panel.hari-libur.destroy', $h) }}" class="d-inline" onsubmit="return confirm('Hapus hari libur ini?')">
                        @csrf @method('DELETE') <button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-center text-secondary py-4">Belum ada hari libur untuk {{ $tahun }}. Hari libur tidak dihitung sebagai hari kerja pada presensi, cuti, dan KPI.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
