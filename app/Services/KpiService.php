<?php

namespace App\Services;

use App\Enums\KategoriKpi;
use App\Enums\StatusHarian;
use App\Models\Karyawan;
use App\Models\Pengaturan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * KPI DISIPLIN KEHADIRAN (bukan KPI kinerja pekerjaan).
 *
 *   hari efektif = hari kerja - (izin + sakit + cuti yang disetujui)
 *   kehadiran %  = hadir / hari efektif x 100
 *   ketepatan %  = (hadir - terlambat) / hadir x 100
 *   disiplin %   = 100 - (menit terlambat x penalti_menit) - (alpa x penalti_alpa)
 *                      - (pulang cepat x penalti_pulang_cepat)         [min 0]
 *   skor         = rata-rata berbobot ketiganya (bobot bisa diubah HR)
 *
 * Hari mendatang dan hari ini yang belum presensi TIDAK dihitung.
 */
class KpiService
{
    public function __construct(private RekapService $rekap) {}

    /** @return Collection<int, KpiHasil> terurut dari skor tertinggi */
    public function hitung(int $bulan, int $tahun, ?string $kodeDept = null): Collection
    {
        $awal = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $hariIni = now()->startOfDay();
        if ($awal->gt($hariIni)) {
            return collect(); // periode belum dimulai
        }

        $akhirBulan = $awal->copy()->endOfMonth()->startOfDay();
        $akhir = $akhirBulan->lte($hariIni) ? $akhirBulan : $hariIni;

        $karyawan = Karyawan::query()->aktif()->with('departemen')
            ->when($kodeDept, fn ($q) => $q->where('kode_dept', $kodeDept))
            ->orderBy('nama_lengkap')
            ->get();
        if ($karyawan->isEmpty()) {
            return collect();
        }

        $ctx = $this->rekap->muat($karyawan, $awal, $akhir);

        return $karyawan
            ->map(fn (Karyawan $k) => $this->ringkas($k, $this->rekap->baris($k, $ctx, $awal, $akhir)))
            ->sortByDesc(fn (KpiHasil $h) => $h->skor)
            ->values();
    }

    /** @param  Collection<int, array<string, mixed>>  $baris */
    public function ringkas(Karyawan $karyawan, Collection $baris): KpiHasil
    {
        $hitung = fn (StatusHarian ...$status) => $baris->filter(fn ($b) => in_array($b['status'], $status, true))->count();

        $hadir = $hitung(StatusHarian::Hadir, StatusHarian::Terlambat);
        $terlambatKali = $hitung(StatusHarian::Terlambat);
        $izin = $hitung(StatusHarian::Izin);
        $sakit = $hitung(StatusHarian::Sakit);
        $cuti = $hitung(StatusHarian::Cuti);
        $alpa = $hitung(StatusHarian::Alpa);
        $menit = (int) $baris->sum('terlambat_menit');
        $pulangCepat = $baris->where('pulang_cepat', true)->count();

        $hariKerja = $hadir + $izin + $sakit + $cuti + $alpa;
        $hariEfektif = $hariKerja - $izin - $sakit - $cuti;

        $kehadiran = $hariEfektif > 0 ? $hadir / $hariEfektif * 100 : 100.0;
        $ketepatan = $hadir > 0 ? ($hadir - $terlambatKali) / $hadir * 100 : ($hariEfektif > 0 ? 0.0 : 100.0);
        $disiplin = max(0.0, min(100.0, 100
            - $menit * (float) Pengaturan::ambil('kpi.penalti_per_menit')
            - $alpa * (float) Pengaturan::ambil('kpi.penalti_alpa')
            - $pulangCepat * (float) Pengaturan::ambil('kpi.penalti_pulang_cepat')));

        $bobotH = (float) Pengaturan::ambil('kpi.bobot_kehadiran');
        $bobotT = (float) Pengaturan::ambil('kpi.bobot_ketepatan');
        $bobotD = (float) Pengaturan::ambil('kpi.bobot_disiplin');
        $totalBobot = max(1.0, $bobotH + $bobotT + $bobotD);
        $skor = ($kehadiran * $bobotH + $ketepatan * $bobotT + $disiplin * $bobotD) / $totalBobot;

        return new KpiHasil(
            $karyawan, $hariKerja, $hadir, $terlambatKali, $menit, $pulangCepat,
            $izin, $sakit, $cuti, $alpa, $kehadiran, $ketepatan, $disiplin, $skor,
            KategoriKpi::dariSkor($skor),
        );
    }

    /**
     * Rata-rata skor per departemen.
     *
     * @param  Collection<int, KpiHasil>  $hasil
     * @return Collection<string, array{nama: string, jumlah: int, skor: float}>
     */
    public function perDepartemen(Collection $hasil): Collection
    {
        return $hasil
            ->groupBy(fn (KpiHasil $h) => $h->karyawan->departemen?->nama_dept ?? 'Tanpa departemen')
            ->map(fn (Collection $grup, string $nama) => [
                'nama' => $nama,
                'jumlah' => $grup->count(),
                'skor' => (float) $grup->avg(fn (KpiHasil $h) => $h->skor),
            ])
            ->sortByDesc('skor')
            ->values();
    }
}
