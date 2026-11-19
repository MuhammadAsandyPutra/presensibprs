<?php

namespace App\Services;

use App\Models\HariLibur;
use App\Models\JadwalKaryawan;
use App\Models\JamKerja;
use App\Models\Karyawan;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Menentukan jam kerja seorang karyawan pada tanggal tertentu. */
class JadwalService
{
    /** Jam kerja yang berlaku pada tanggal tsb, atau null bila libur / bukan hari kerjanya. */
    public function untukTanggal(Karyawan $karyawan, CarbonInterface $tanggal): ?JamKerja
    {
        if (HariLibur::query()->whereDate('tanggal', $tanggal->toDateString())->exists()) {
            return null;
        }

        $namaHari = config('presensi.hari')[$tanggal->dayOfWeekIso];
        $kode = $this->petaUntuk($karyawan)[$namaHari] ?? null;

        return $kode ? JamKerja::query()->find($kode) : null;
    }

    /**
     * Peta jadwal satu karyawan.
     *
     * @return array<string, string> nama hari => kode_jam_kerja
     */
    public function petaUntuk(Karyawan $karyawan): array
    {
        return self::susunPeta($karyawan->jadwal()->get(), self::kodeDefault());
    }

    public static function kodeDefault(): ?string
    {
        return JamKerja::query()->orderBy('kode_jam_kerja')->value('kode_jam_kerja');
    }

    /**
     * Bila karyawan belum punya jadwal sama sekali, pakai jam kerja pertama untuk Senin-Jumat
     * sehingga karyawan baru langsung bisa presensi.
     *
     * @return array<string, string>
     */
    public static function susunPeta(Collection $baris, ?string $kodeDefault): array
    {
        if ($baris->isNotEmpty()) {
            return $baris->pluck('kode_jam_kerja', 'hari')->all();
        }
        if ($kodeDefault === null) {
            return [];
        }

        $peta = [];
        foreach (config('presensi.hari_kerja_default') as $iso) {
            $peta[config('presensi.hari')[$iso]] = $kodeDefault;
        }

        return $peta;
    }

    /** @param  array<string, string|null>  $perHari  nama hari => kode_jam_kerja (kosong = libur) */
    public function simpan(Karyawan $karyawan, array $perHari): void
    {
        DB::transaction(function () use ($karyawan, $perHari) {
            JadwalKaryawan::query()->where('nik', $karyawan->nik)->delete();

            foreach (config('presensi.hari') as $nama) {
                if (! empty($perHari[$nama])) {
                    JadwalKaryawan::query()->create([
                        'nik' => $karyawan->nik,
                        'hari' => $nama,
                        'kode_jam_kerja' => $perHari[$nama],
                    ]);
                }
            }
        });
    }
}
