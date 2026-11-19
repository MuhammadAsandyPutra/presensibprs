<?php

namespace App\Enums;

/** Disimpan di kolom pengajuan_izin.status_approved (0 = menunggu, 1 = disetujui, 2 = ditolak). */
enum StatusPengajuan: int
{
    case Menunggu = 0;
    case Disetujui = 1;
    case Ditolak = 2;
    case Dibatalkan = 3;

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu',
            self::Disetujui => 'Disetujui',
            self::Ditolak => 'Ditolak',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Menunggu => 'warning',
            self::Disetujui => 'success',
            self::Ditolak => 'danger',
            self::Dibatalkan => 'secondary',
        };
    }

    /** Status yang masih "memakan" kuota/saldo dan tanggalnya tidak boleh tumpang tindih. */
    public static function aktif(): array
    {
        return [self::Menunggu->value, self::Disetujui->value];
    }
}
