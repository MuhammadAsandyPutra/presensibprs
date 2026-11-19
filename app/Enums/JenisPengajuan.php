<?php

namespace App\Enums;

/** Disimpan di kolom pengajuan_izin.status (1 = izin, 2 = sakit — kompatibel dengan data lama). */
enum JenisPengajuan: int
{
    case Izin = 1;
    case Sakit = 2;
    case Cuti = 3;

    public function label(): string
    {
        return match ($this) {
            self::Izin => 'Izin',
            self::Sakit => 'Sakit',
            self::Cuti => 'Cuti',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Izin => 'info',
            self::Sakit => 'warning',
            self::Cuti => 'primary',
        };
    }

    /** Izin & sakit dibatasi kuota bulanan; cuti dibatasi saldo tahunan. */
    public function memakaiKuotaBulanan(): bool
    {
        return $this !== self::Cuti;
    }
}
