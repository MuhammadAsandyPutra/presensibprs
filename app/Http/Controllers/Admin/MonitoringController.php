<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusHarian;
use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Karyawan;
use App\Models\Presensi;
use App\Services\RekapService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function index(Request $request, RekapService $rekap): View
    {
        $data = $request->validate([
            'tanggal' => ['nullable', 'date'],
            'kode_cabang' => ['nullable', 'string', 'max:10'],
        ]);
        $tanggal = Carbon::parse($data['tanggal'] ?? now()->toDateString())->startOfDay();
        $kodeCabang = $data['kode_cabang'] ?? null;

        $presensi = Presensi::query()->with(['karyawan.departemen', 'karyawan.cabang', 'jamKerja'])
            ->where('tanggal_presensi', $tanggal->toDateString())
            ->when($kodeCabang, fn ($q) => $q->whereHas('karyawan', fn ($k) => $k->where('kode_cabang', $kodeCabang)))
            ->orderBy('jam_in')
            ->get();

        // Karyawan yang seharusnya masuk tetapi belum/tidak presensi.
        $karyawan = Karyawan::query()->aktif()->with('departemen')
            ->when($kodeCabang, fn ($q) => $q->where('kode_cabang', $kodeCabang))->orderBy('nama_lengkap')->get();
        $ctx = $rekap->muat($karyawan, $tanggal, $tanggal);
        $belum = $karyawan->map(fn ($k) => ['karyawan' => $k, 'baris' => $rekap->baris($k, $ctx, $tanggal, $tanggal)->first()])
            ->filter(fn ($x) => in_array($x['baris']['status'], [StatusHarian::Belum, StatusHarian::Alpa], true))->values();

        return view('admin.monitoring.index', [
            'tanggal' => $tanggal,
            'kodeCabang' => $kodeCabang,
            'cabangList' => Cabang::query()->orderBy('nama_cabang')->get(),
            'presensi' => $presensi,
            'belum' => $belum,
        ]);
    }
}
