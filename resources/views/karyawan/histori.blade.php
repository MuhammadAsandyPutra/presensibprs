@extends('layouts.karyawan')

@section('judul', 'Riwayat')
@section('header', 'Riwayat Presensi')

@section('content')
<form method="GET" class="kartu form-mobile">
    <div class="row">
        <div class="col-7">
            <label for="bulan">Bulan</label>
            <select id="bulan" name="bulan" class="form-control" onchange="this.form.submit()">
                @foreach (\App\Support\Waktu::NAMA_BULAN as $no => $nama)
                    <option value="{{ $no }}" @selected($no === $bulan)>{{ $nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-5">
            <label for="tahun">Tahun</label>
            <select id="tahun" name="tahun" class="form-control" onchange="this.form.submit()">
                @foreach (range(now()->year, now()->year - 3) as $t)
                    <option value="{{ $t }}" @selected($t === $tahun)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
    </div>
</form>

@forelse ($baris as $b)
    <div class="baris-riwayat">
        <div class="d-flex align-items-center" style="gap:10px">
            @if ($b['presensi']?->foto_in)
                <img src="{{ route('berkas.presensi', [$b['presensi'], 'in']) }}" width="40" height="40" class="rounded" style="object-fit:cover" alt="Foto" loading="lazy">
            @endif
            <div>
                <div class="tgl">{{ $b['tanggal']->translatedFormat('D, d M Y') }}</div>
                <div class="jam">
                    @if ($b['presensi'])
                        {{ \App\Support\Waktu::hm($b['jam_in']) }} – {{ \App\Support\Waktu::hm($b['jam_out']) }}
                        @if ($b['pulang_cepat']) · pulang cepat @endif
                    @else
                        {{ $b['keterangan'] ?? '—' }}
                    @endif
                </div>
            </div>
        </div>
        <span class="status-chip chip-{{ $b['status']->warna() }}">{{ $b['terlambat_menit'] > 0 ? 'Terlambat '.$b['terlambat_menit'].' mnt' : $b['status']->label() }}</span>
    </div>
@empty
    <div class="kosong">Belum ada data pada periode ini.</div>
@endforelse
@endsection
