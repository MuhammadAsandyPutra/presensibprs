@extends('layouts.admin')

@section('judul', 'Log Aktivitas')

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table card-table table-vcenter table-sm">
            <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Keterangan</th><th>IP</th></tr></thead>
            <tbody>
            @forelse ($daftar as $l)
                <tr>
                    <td class="text-nowrap">{{ $l->created_at?->translatedFormat('d M Y H:i') }}</td><td>{{ $l->nama }}</td>
                    <td><span class="badge bg-blue-lt">{{ $l->aksi }}</span></td><td>{{ $l->deskripsi }}</td><td class="text-secondary">{{ $l->ip_address }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada aktivitas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $daftar->links() }}</div>
</div>
@endsection
