<?php

namespace App\Http\Controllers\Karyawan;

use App\Enums\StatusHarian;
use App\Http\Controllers\Controller;
use App\Models\PengajuanIzin;
use App\Services\PengajuanService;
use App\Services\RekapService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(RekapService $rekap, PengajuanService $pengajuan): View
    {
        $karyawan = Auth::guard('karyawan')->user()->load('cabang', 'departemen');

        $hariIni = now()->startOfDay();
        $awalBulan = $hariIni->copy()->startOfMonth();
        $baris = $rekap->baris($karyawan, $rekap->muat(collect([$karyawan]), $awalBulan, $hariIni), $awalBulan, $hariIni);

        $hitung = fn (StatusHarian ...$s) => $baris->filter(fn ($b) => in_array($b['status'], $s, true))->count();

        $ringkasan = [
            'hadir' => $hitung(StatusHarian::Hadir, StatusHarian::Terlambat),
            'terlambat' => $hitung(StatusHarian::Terlambat),
            'izin' => $hitung(StatusHarian::Izin, StatusHarian::Sakit),
            'cuti' => $hitung(StatusHarian::Cuti),
            'alpa' => $hitung(StatusHarian::Alpa),
        ];

        return view('karyawan.dashboard', [
            'karyawan' => $karyawan,
            'hariIni' => $baris->last(),
            'ringkasan' => $ringkasan,
            'riwayat' => $baris->filter(fn ($b) => $b['presensi'])->reverse()->take(7)->values(),
            'sisaCuti' => $pengajuan->sisaCuti($karyawan, $hariIni->year),
            'menunggu' => PengajuanIzin::query()->where('nik', $karyawan->nik)->where('status_approved', 0)->count(),
        ]);
    }
}
