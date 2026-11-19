<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusHarian;
use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\PengajuanIzin;
use App\Models\Presensi;
use App\Services\KpiService;
use App\Services\RekapService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(RekapService $rekap, KpiService $kpi): View
    {
        $hariIni = now()->startOfDay();

        // Status semua karyawan aktif hari ini (satu pipeline yang sama dengan laporan & KPI).
        $karyawan = Karyawan::query()->aktif()->with('departemen')->orderBy('nama_lengkap')->get();
        $ctx = $rekap->muat($karyawan, $hariIni, $hariIni);
        $statusHariIni = $karyawan->map(fn ($k) => ['karyawan' => $k, 'baris' => $rekap->baris($k, $ctx, $hariIni, $hariIni)->first()]);

        $hitung = fn (StatusHarian ...$s) => $statusHariIni->filter(fn ($x) => in_array($x['baris']['status'], $s, true))->count();

        // Grafik kehadiran 7 hari terakhir.
        $dari = $hariIni->copy()->subDays(6);
        $perTanggal = Presensi::query()->whereBetween('tanggal_presensi', [$dari->toDateString(), $hariIni->toDateString()])
            ->selectRaw('tanggal_presensi as tgl, COUNT(*) as jumlah')->groupBy('tanggal_presensi')->pluck('jumlah', 'tgl');
        $grafik = collect(range(0, 6))->map(function ($i) use ($dari, $perTanggal) {
            $tgl = $dari->copy()->addDays($i);

            return ['label' => $tgl->format('d/m'), 'jumlah' => (int) ($perTanggal[$tgl->toDateString()] ?? 0)];
        });

        $hasilKpi = $kpi->hitung($hariIni->month, $hariIni->year);

        return view('admin.dashboard.index', [
            'total' => $karyawan->count(),
            'hadir' => $hitung(StatusHarian::Hadir, StatusHarian::Terlambat),
            'terlambat' => $hitung(StatusHarian::Terlambat),
            'izin' => $hitung(StatusHarian::Izin, StatusHarian::Sakit, StatusHarian::Cuti),
            'belum' => $statusHariIni->filter(fn ($x) => $x['baris']['status'] === StatusHarian::Belum),
            'menunggu' => PengajuanIzin::query()->where('status_approved', 0)->count(),
            'perluReview' => Presensi::query()->where('perlu_review', true)->where('tanggal_presensi', '>=', $hariIni->copy()->subDays(7)->toDateString())->count(),
            'grafik' => $grafik,
            'kpiTerbaik' => $hasilKpi->take(5),
            'kpiTerendah' => $hasilKpi->reverse()->take(5)->values(),
        ]);
    }
}
