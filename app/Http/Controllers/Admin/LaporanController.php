<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusHarian;
use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Karyawan;
use App\Services\RekapService;
use App\Support\Csv;
use App\Support\Waktu;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function __construct(private RekapService $rekap) {}

    public function index(): View
    {
        return view('admin.laporan.index', [
            'karyawanList' => Karyawan::query()->aktif()->orderBy('nama_lengkap')->get(['nik', 'nama_lengkap']),
            'departemenList' => Departemen::query()->orderBy('nama_dept')->get(),
        ]);
    }

    /** Laporan presensi satu karyawan (siap cetak). */
    public function presensi(Request $request): View
    {
        [$karyawan, $baris, $awal] = $this->dataPresensi($request);

        return view('admin.laporan.presensi', [
            'karyawan' => $karyawan,
            'baris' => $baris,
            'periode' => Waktu::namaBulan($awal->month).' '.$awal->year,
        ]);
    }

    public function presensiCsv(Request $request): StreamedResponse
    {
        [$karyawan, $baris, $awal] = $this->dataPresensi($request);

        return Csv::unduh(
            "presensi-{$karyawan->nik}-{$awal->format('Y-m')}.csv",
            ['Tanggal', 'Hari', 'Masuk', 'Pulang', 'Status', 'Terlambat (menit)', 'Pulang cepat', 'Keterangan'],
            $baris->map(fn ($b) => [
                $b['tanggal']->format('Y-m-d'), $b['hari'], Waktu::hm($b['jam_in']), Waktu::hm($b['jam_out']),
                $b['status']->label(), $b['terlambat_menit'], $b['pulang_cepat'] ? 'Ya' : 'Tidak', $b['keterangan'],
            ]),
        );
    }

    /** Rekap bulanan semua karyawan: baris = karyawan, kolom = tanggal. */
    public function rekap(Request $request): View
    {
        [$karyawan, $matriks, $awal, $jumlahHari] = $this->dataRekap($request);

        return view('admin.laporan.rekap', [
            'karyawan' => $karyawan,
            'matriks' => $matriks,
            'jumlahHari' => $jumlahHari,
            'periode' => Waktu::namaBulan($awal->month).' '.$awal->year,
            'legenda' => StatusHarian::cases(),
        ]);
    }

    public function rekapCsv(Request $request): StreamedResponse
    {
        [$karyawan, $matriks, $awal, $jumlahHari] = $this->dataRekap($request);

        $header = ['NIK', 'Nama', ...range(1, $jumlahHari), 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Cuti', 'Alpa'];
        $baris = $karyawan->map(function ($k) use ($matriks) {
            $hari = $matriks[$k->nik];
            $hitung = fn (StatusHarian ...$s) => $hari->filter(fn ($b) => in_array($b['status'], $s, true))->count();

            return [
                $k->nik, $k->nama_lengkap, ...$hari->map(fn ($b) => $b['status']->kode())->all(),
                $hitung(StatusHarian::Hadir, StatusHarian::Terlambat), $hitung(StatusHarian::Terlambat),
                $hitung(StatusHarian::Izin), $hitung(StatusHarian::Sakit), $hitung(StatusHarian::Cuti), $hitung(StatusHarian::Alpa),
            ];
        });

        return Csv::unduh("rekap-presensi-{$awal->format('Y-m')}.csv", $header, $baris);
    }

    /** @return array{0: Karyawan, 1: \Illuminate\Support\Collection, 2: Carbon} */
    private function dataPresensi(Request $request): array
    {
        $f = $request->validate([
            'nik' => ['required', 'string', 'max:20', 'exists:karyawan,nik'],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:2020,2100'],
        ]);

        $karyawan = Karyawan::query()->with('departemen', 'cabang')->findOrFail($f['nik']);
        $awal = Carbon::create((int) $f['tahun'], (int) $f['bulan'], 1)->startOfDay();
        $akhir = $awal->copy()->endOfMonth()->startOfDay();
        $baris = $this->rekap->baris($karyawan, $this->rekap->muat(collect([$karyawan]), $awal, $akhir), $awal, $akhir);

        return [$karyawan, $baris, $awal];
    }

    /** @return array{0: \Illuminate\Support\Collection, 1: array, 2: Carbon, 3: int} */
    private function dataRekap(Request $request): array
    {
        $f = $request->validate([
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:2020,2100'],
            'kode_dept' => ['nullable', 'string', 'max:10'],
        ]);

        $awal = Carbon::create((int) $f['tahun'], (int) $f['bulan'], 1)->startOfDay();
        $akhir = $awal->copy()->endOfMonth()->startOfDay();

        $karyawan = Karyawan::query()->aktif()->with('departemen')
            ->when($f['kode_dept'] ?? null, fn ($q, $d) => $q->where('kode_dept', $d))
            ->orderBy('nama_lengkap')->get();
        $ctx = $this->rekap->muat($karyawan, $awal, $akhir);

        $matriks = [];
        foreach ($karyawan as $k) {
            $matriks[$k->nik] = $this->rekap->baris($k, $ctx, $awal, $akhir);
        }

        return [$karyawan, $matriks, $awal, $awal->daysInMonth];
    }
}
