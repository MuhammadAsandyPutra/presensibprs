<?php

namespace App\Enums;

/** Status satu karyawan pada satu tanggal (dasar laporan, rekap, dan KPI). */
enum StatusHarian: string
{
    case Hadir = 'hadir';
    case Terlambat = 'terlambat';
    case Izin = 'izin';
    case Sakit = 'sakit';
    case Cuti = 'cuti';
    case Libur = 'libur';
    case TanpaJadwal = 'tanpa_jadwal';
    case Alpa = 'alpa';
    case Belum = 'belum';

    public function label(): string
    {
        return match ($this) {
            self::Hadir => 'Hadir',
            self::Terlambat => 'Terlambat',
            self::Izin => 'Izin',
            self::Sakit => 'Sakit',
            self::Cuti => 'Cuti',
            self::Libur => 'Libur',
            self::TanpaJadwal => 'Tidak ada jadwal',
            self::Alpa => 'Tanpa keterangan',
            self::Belum => 'Belum',
        };
    }

    /** Kode satu huruf untuk tabel rekap bulanan. */
    public function kode(): string
    {
        return match ($this) {
            self::Hadir => 'H',
            self::Terlambat => 'T',
            self::Izin => 'I',
            self::Sakit => 'S',
            self::Cuti => 'C',
            self::Libur => 'L',
            self::TanpaJadwal => '-',
            self::Alpa => 'A',
            self::Belum => '.',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Hadir => 'success',
            self::Terlambat => 'orange',
            self::Izin => 'info',
            self::Sakit => 'warning',
            self::Cuti => 'primary',
            self::Alpa => 'danger',
            default => 'secondary',
        };
    }
}
