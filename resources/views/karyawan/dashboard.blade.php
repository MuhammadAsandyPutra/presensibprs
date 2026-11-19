@extends('layouts.karyawan')

@section('judul', 'Beranda')

@section('content')
@php
    $jam = now()->hour;
    $salam = $jam < 11 ? 'pagi' : ($jam < 15 ? 'siang' : ($jam < 18 ? 'sore' : 'malam'));
    $status = $hariIni['status'];
    $presensi = $hariIni['presensi'];
@endphp

<div class="kepala-beranda">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="keluar" aria-label="Keluar"><ion-icon name="log-out-outline"></ion-icon></button>
    </form>
    <div class="d-flex align-items-center" style="gap:12px">
        <img src="{{ $karyawan->foto ? route('berkas.profil', $karyawan) : asset('assets/img/no_photo.png') }}"
             alt="Foto" class="rounded-circle" width="56" height="56" style="object-fit:cover;border:2px solid rgba(255,255,255,.5)">
        <div>
            <div class="sapaan">Selamat {{ $salam }},</div>
            <div class="nama">{{ $karyawan->nama_lengkap }}</div>
            <div class="meta">{{ $karyawan->jabatan }}{{ $karyawan->departemen ? ' · '.$karyawan->departemen->nama_dept : '' }}</div>
        </div>
    </div>
</div>

<div class="kartu-hari">
    <div class="d-flex justify-content-between align-items-center">
        <div class="jam">{{ now()->translatedFormat('l, d F Y') }}</div>
        <span class="status-chip chip-{{ $status->warna() }}">{{ $status->label() }}</span>
    </div>

    @if ($presensi)
        <div class="d-flex mt-3 text-center">
            <div class="flex-fill"><div class="jam">Masuk</div><div class="waktu">{{ \App\Support\Waktu::hm($presensi->jam_in) }}</div></div>
            <div class="flex-fill"><div class="jam">Pulang</div><div class="waktu">{{ \App\Support\Waktu::hm($presensi->jam_out) }}</div></div>
        </div>
        @if ($hariIni['terlambat_menit'] > 0)
            <div class="text-center mt-1" style="font-size:12px;color:#b3660d">Terlambat {{ $hariIni['terlambat_menit'] }} menit</div>
        @endif
        @if (! $presensi->jam_out)
            <div class="garis"></div>
            <a href="{{ route('presensi.create') }}" class="tombol-hijau merah"><ion-icon name="camera-outline"></ion-icon> Presensi Pulang</a>
        @endif
    @elseif ($status === \App\Enums\StatusHarian::Belum)
        <div class="garis"></div>
        <a href="{{ route('presensi.create') }}" class="tombol-hijau"><ion-icon name="camera-outline"></ion-icon> Presensi Masuk</a>
    @elseif ($status === \App\Enums\StatusHarian::Libur)
        <p class="mb-0 mt-3 text-center text-muted">{{ $hariIni['keterangan'] ?? 'Hari libur' }}. Selamat beristirahat.</p>
    @elseif ($status === \App\Enums\StatusHarian::TanpaJadwal)
        <p class="mb-0 mt-3 text-center text-muted">Hari ini bukan jadwal kerja Anda.</p>
    @else
        <p class="mb-0 mt-3 text-center text-muted">Presensi tidak diperlukan hari ini.</p>
    @endif
</div>

<div class="grid-ringkas">
    <div class="sel"><div class="angka">{{ $ringkasan['hadir'] }}</div><div class="label">Hadir</div></div>
    <div class="sel"><div class="angka" style="color:#b3660d">{{ $ringkasan['terlambat'] }}</div><div class="label">Terlambat</div></div>
    <div class="sel"><div class="angka" style="color:#1d5fb4">{{ $ringkasan['izin'] + $ringkasan['cuti'] }}</div><div class="label">Izin/Cuti</div></div>
    <div class="sel"><div class="angka" style="color:#b3261e">{{ $ringkasan['alpa'] }}</div><div class="label">Tanpa ket.</div></div>
</div>
<div class="text-center mt-1" style="font-size:12px;color:var(--redam)">Ringkasan bulan {{ now()->translatedFormat('F Y') }}</div>

<a href="{{ route('izin.index') }}" class="kartu d-flex justify-content-between align-items-center text-decoration-none" style="color:inherit">
    <div>
        <div style="font-weight:600">Sisa cuti tahun {{ now()->year }}</div>
        <div style="font-size:12px;color:var(--redam)">{{ $menunggu > 0 ? $menunggu.' pengajuan menunggu persetujuan' : 'Tidak ada pengajuan menunggu' }}</div>
    </div>
    <div style="font-size:24px;font-weight:700;color:var(--hijau)">{{ $sisaCuti }}<span style="font-size:12px;font-weight:500"> hari</span></div>
</a>

<div class="judul-bagian"><span>7 presensi terakhir</span><a href="{{ route('histori') }}">Lihat semua</a></div>
@forelse ($riwayat as $r)
    <div class="baris-riwayat">
        <div>
            <div class="tgl">{{ $r['tanggal']->translatedFormat('D, d M') }}</div>
            <div class="jam">{{ \App\Support\Waktu::hm($r['jam_in']) }} – {{ \App\Support\Waktu::hm($r['jam_out']) }}</div>
        </div>
        <span class="status-chip chip-{{ $r['status']->warna() }}">{{ $r['terlambat_menit'] > 0 ? '+'.$r['terlambat_menit'].' mnt' : $r['status']->label() }}</span>
    </div>
@empty
    <div class="kosong">Belum ada presensi bulan ini.</div>
@endforelse
@endsection
