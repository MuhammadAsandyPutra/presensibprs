<?php

namespace App\Services;

use App\Enums\JenisPengajuan;
use App\Enums\StatusHarian;
use App\Enums\StatusPengajuan;
use App\Models\HariLibur;
use App\Models\JadwalKaryawan;
use App\Models\JamKerja;
use App\Models\Karyawan;
use App\Models\PengajuanIzin;
use App\Models\Presensi;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Satu-satunya tempat yang menerjemahkan data mentah (presensi, jadwal, libur, izin)
 * menjadi STATUS PER HARI. Dipakai oleh laporan, rekap bulanan, dashboard, dan KPI
 * sehingga angka di semua halaman selalu konsisten.
 *
 * Pemakaian:  $ctx = $rekap->muat($daftarKaryawan, $awal, $akhir);
 *             $baris = $rekap->baris($karyawan, $ctx, $awal, $akhir);
 */
class RekapService
{
    /**
     * Muat semua data yang dibutuhkan untuk banyak karyawan sekaligus (menghindari N+1 query).
     *
     * @param  Collection<int, Karyawan>  $karyawan
     * @return array<string, mixed>
     */
    public function muat(Collection $karyawan, CarbonInterface $awal, CarbonInterface $akhir): array
    {
        $niks = $karyawan->pluck('nik')->all();
        $mulai = $awal->toDateString();
        $selesai = $akhir->toDateString();

        $jamKerja = JamKerja::query()->get()->keyBy('kode_jam_kerja');
        $kodeDefault = $jamKerja->keys()->sort()->first();

        $barisJadwal = JadwalKaryawan::query()->whereIn('nik', $niks)->get()->groupBy('nik');
        $jadwal = [];
        foreach ($niks as $nik) {
            $jadwal[$nik] = JadwalService::susunPeta($barisJadwal->get($nik, collect()), $kodeDefault);
        }

        $presensi = Presensi::query()->with('jamKerja')
            ->whereIn('nik', $niks)
            ->whereBetween('tanggal_presensi', [$mulai, $selesai])
            ->get()
            ->groupBy('nik')
            ->map(fn ($grup) => $grup->keyBy(fn ($p) => $p->tanggal_presensi->toDateString()))
            ->all();

        // Tanggal yang dikecualikan karena izin/sakit/cuti yang SUDAH DISETUJUI.
        $dikecualikan = [];
        PengajuanIzin::query()
            ->whereIn('nik', $niks)
            ->where('status_approved', StatusPengajuan::Disetujui->value)
            ->berimpitDengan($mulai, $selesai)
            ->get()
            ->each(function (PengajuanIzin $p) use (&$dikecualikan, $awal, $akhir) {
                $hari = $p->tgl_izin->copy()->max($awal)->startOfDay();
                $batas = $p->tanggalAkhir()->min($akhir);
                while ($hari->lte($batas)) {
                    $dikecualikan[$p->nik][$hari->toDateString()] ??= $p->jenis();
                    $hari->addDay();
                }
            });

        $libur = HariLibur::query()->whereBetween('tanggal', [$mulai, $selesai])->get()
            ->mapWithKeys(fn ($h) => [$h->tanggal->toDateString() => $h->keterangan])
            ->all();

        return compact('jamKerja', 'jadwal', 'presensi', 'dikecualikan', 'libur');
    }

    /**
     * Status satu karyawan untuk setiap tanggal dalam rentang.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function baris(Karyawan $karyawan, array $ctx, CarbonInterface $awal, CarbonInterface $akhir): Collection
    {
        $hariIni = now()->startOfDay();
        $hasil = collect();

        for ($tgl = $awal->copy()->startOfDay(); $tgl->lte($akhir); $tgl->addDay()) {
            $hasil->push($this->satuHari($karyawan, $ctx, $tgl->copy(), $hariIni));
        }

        return $hasil;
    }

    /** @return array<string, mixed> */
    private function satuHari(Karyawan $karyawan, array $ctx, CarbonInterface $tgl, CarbonInterface $hariIni): array
    {
        $nik = $karyawan->nik;
        $tglStr = $tgl->toDateString();
        $namaHari = config('presensi.hari')[$tgl->dayOfWeekIso];
        $kodeJam = $ctx['jadwal'][$nik][$namaHari] ?? null;
        $presensi = ($ctx['presensi'][$nik] ?? collect())->get($tglStr);

        $baris = [
            'tanggal' => $tgl,
            'hari' => $namaHari,
            'status' => StatusHarian::Belum,
            'presensi' => $presensi,
            'jam_in' => $presensi?->jam_in,
            'jam_out' => $presensi?->jam_out,
            'terlambat_menit' => 0,
            'pulang_cepat' => false,
            'keterangan' => null,
        ];

        if (isset($ctx['libur'][$tglStr])) {
            return [...$baris, 'status' => StatusHarian::Libur, 'keterangan' => $ctx['libur'][$tglStr]];
        }
        if ($kodeJam === null) {
            return [...$baris, 'status' => StatusHarian::TanpaJadwal];
        }

        if ($presensi) {
            $jam = $presensi->jamKerja ?? ($ctx['jamKerja'][$kodeJam] ?? null);
            $menit = $jam?->menitTerlambat($presensi->jam_in) ?? 0;

            return [
                ...$baris,
                'status' => $menit > 0 ? StatusHarian::Terlambat : StatusHarian::Hadir,
                'terlambat_menit' => $menit,
                'pulang_cepat' => $jam?->pulangCepat($presensi->jam_out) ?? false,
            ];
        }

        if ($jenis = ($ctx['dikecualikan'][$nik][$tglStr] ?? null)) {
            return [...$baris, 'status' => match ($jenis) {
                JenisPengajuan::Izin => StatusHarian::Izin,
                JenisPengajuan::Sakit => StatusHarian::Sakit,
                JenisPengajuan::Cuti => StatusHarian::Cuti,
            }];
        }

        // Hari ini belum berakhir, jadi belum bisa disebut tanpa keterangan.
        return [...$baris, 'status' => $tgl->lt($hariIni) ? StatusHarian::Alpa : StatusHarian::Belum];
    }
}
