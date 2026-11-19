<?php

namespace App\Enums;

use App\Models\Pengaturan;

enum KategoriKpi: string
{
    case SangatBaik = 'sangat_baik';
    case Baik = 'baik';
    case Cukup = 'cukup';
    case Kurang = 'kurang';

    public static function dariSkor(float $skor): self
    {
        return match (true) {
            $skor >= Pengaturan::ambil('kpi.batas_sangat_baik') => self::SangatBaik,
            $skor >= Pengaturan::ambil('kpi.batas_baik') => self::Baik,
            $skor >= Pengaturan::ambil('kpi.batas_cukup') => self::Cukup,
            default => self::Kurang,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::SangatBaik => 'Sangat Baik',
            self::Baik => 'Baik',
            self::Cukup => 'Cukup',
            self::Kurang => 'Perlu Pembinaan',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::SangatBaik => 'success',
            self::Baik => 'lime',
            self::Cukup => 'warning',
            self::Kurang => 'danger',
        };
    }
}
