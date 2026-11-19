<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Karyawan;
use App\Services\KpiService;
use App\Services\RekapService;
use App\Support\Csv;
use App\Support\Waktu;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KpiController extends Controller
{
    public function __construct(private KpiService $kpi) {}

    public function index(Request $request): View
    {
        [$bulan, $tahun, $dept] = $this->filter($request);
        $hasil = $this->kpi->hitung($bulan, $tahun, $dept);

        return view('admin.kpi.index', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'kodeDept' => $dept,
            'hasil' => $hasil,
            'perDept' => $this->kpi->perDepartemen($hasil),
            'rataRata' => $hasil->isEmpty() ? null : (float) $hasil->avg(fn ($h) => $h->skor),
            'departemenList' => Departemen::query()->orderBy('nama_dept')->get(),
        ]);
    }

    public function csv(Request $request): StreamedResponse
    {
        [$bulan, $tahun, $dept] = $this->filter($request);

        return Csv::unduh(
            sprintf('kpi-presensi-%04d-%02d.csv', $tahun, $bulan),
            ['Peringkat', 'NIK', 'Nama', 'Departemen', 'Hari kerja', 'Hadir', 'Terlambat (kali)', 'Menit terlambat', 'Pulang cepat',
                'Izin', 'Sakit', 'Cuti', 'Tanpa keterangan', 'Kehadiran %', 'Ketepatan %', 'Disiplin %', 'Skor', 'Kategori'],
            $this->kpi->hitung($bulan, $tahun, $dept)->values()->map(fn ($h, $i) => [
                $i + 1, $h->karyawan->nik, $h->karyawan->nama_lengkap, $h->karyawan->departemen?->nama_dept, $h->hariKerja, $h->hadir,
                $h->terlambatKali, $h->menitTerlambat, $h->pulangCepat, $h->izin, $h->sakit, $h->cuti, $h->alpa,
                round($h->kehadiran, 1), round($h->ketepatan, 1), round($h->disiplin, 1), round($h->skor, 1), $h->kategori->label(),
            ]),
        );
    }

    /** Rincian harian satu karyawan pada periode KPI (agar HR bisa menelusuri asal angkanya). */
    public function show(Request $request, Karyawan $karyawan, RekapService $rekap): View
    {
        [$bulan, $tahun] = $this->filter($request);
        $awal = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $hariIni = now()->startOfDay();
        $akhir = $awal->copy()->endOfMonth()->startOfDay();
        $akhir = $akhir->gt($hariIni) ? $hariIni : $akhir;

        $baris = $awal->gt($hariIni) ? collect() : $rekap->baris($karyawan, $rekap->muat(collect([$karyawan]), $awal, $akhir), $awal, $akhir);

        return view('admin.kpi.show', [
            'karyawan' => $karyawan->load('departemen'),
            'baris' => $baris,
            'ringkas' => $this->kpi->ringkas($karyawan, $baris),
            'periode' => Waktu::namaBulan($bulan).' '.$tahun,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
    }

    /** @return array{0: int, 1: int, 2: string|null} */
    private function filter(Request $request): array
    {
        $f = $request->validate([
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'between:2020,2100'],
            'kode_dept' => ['nullable', 'string', 'max:10'],
        ]);

        return [(int) ($f['bulan'] ?? now()->month), (int) ($f['tahun'] ?? now()->year), $f['kode_dept'] ?? null];
    }
}
