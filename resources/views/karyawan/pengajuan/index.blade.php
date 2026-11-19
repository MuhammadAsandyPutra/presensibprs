@extends('layouts.karyawan')

@section('judul', 'Izin / Cuti')
@section('header', 'Izin, Sakit & Cuti')

@section('content')
<div style="height:8px"></div>
@forelse ($daftar as $p)
    <div class="kartu" style="margin-top:0">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <span class="status-chip chip-{{ $p->jenis()->warna() }}">{{ $p->jenis()->label() }}</span>
                <div style="font-weight:600;margin-top:6px">
                    {{ $p->tgl_izin->translatedFormat('d M Y') }}@if ($p->tgl_selesai && ! $p->tgl_selesai->isSameDay($p->tgl_izin)) – {{ $p->tgl_selesai->translatedFormat('d M Y') }}@endif
                </div>
                <div style="font-size:12px;color:var(--redam)">{{ $p->jumlah_hari }} hari kerja</div>
            </div>
            <span class="status-chip chip-{{ $p->status_approved->warna() }}">{{ $p->status_approved->label() }}</span>
        </div>
        <div style="font-size:14px;margin-top:8px">{{ $p->keterangan }}</div>
        @if ($p->catatan_hr)
            <div style="font-size:13px;margin-top:8px;background:#f2f5f3;border-radius:10px;padding:8px 10px"><strong>Catatan HR:</strong> {{ $p->catatan_hr }}</div>
        @endif
        <div class="d-flex justify-content-between align-items-center mt-2">
            @if ($p->lampiran)
                <a href="{{ route('berkas.lampiran', $p) }}" target="_blank" rel="noopener" style="font-size:13px"><ion-icon name="attach-outline"></ion-icon> Lampiran</a>
            @else <span></span> @endif
            @if ($p->status_approved === \App\Enums\StatusPengajuan::Menunggu)
                <form method="POST" action="{{ route('izin.batal', $p) }}" onsubmit="return confirm('Batalkan pengajuan ini?')">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger">Batalkan</button>
                </form>
            @endif
        </div>
    </div>
@empty
    <div class="kosong">Belum ada pengajuan.<br>Ketuk tombol <strong>+</strong> untuk membuat pengajuan baru.</div>
@endforelse

<div class="px-3">{{ $daftar->links() }}</div>

<a href="{{ route('izin.create') }}" class="fab-baru" aria-label="Ajukan baru"><ion-icon name="add-outline"></ion-icon></a>
@endsection
