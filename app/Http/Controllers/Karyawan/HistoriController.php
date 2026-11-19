<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Services\RekapService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HistoriController extends Controller
{
    public function index(Request $request, RekapService $rekap): View
    {
        $data = $request->validate([
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'between:2020,2100'],
        ]);
        $bulan = (int) ($data['bulan'] ?? now()->month);
        $tahun = (int) ($data['tahun'] ?? now()->year);

        $karyawan = Auth::guard('karyawan')->user();
        $awal = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $akhir = $awal->copy()->endOfMonth()->startOfDay();

        $baris = $rekap->baris($karyawan, $rekap->muat(collect([$karyawan]), $awal, $akhir), $awal, $akhir);

        return view('karyawan.histori', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'baris' => $baris->filter(fn ($b) => $b['presensi'] || ! in_array($b['status']->value, ['belum', 'tanpa_jadwal'], true))->reverse()->values(),
        ]);
    }
}
