<?php

namespace App\Support;

/**
 * Helper waktu berbasis string "HH:MM[:SS]" (format kolom TIME di database).
 * Sengaja memakai bilangan bulat (detik) agar bebas masalah zona waktu dan mudah diuji.
 */
final class Waktu
{
    public const NAMA_BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    /** "08:30:15" => 30615 */
    public static function detik(?string $waktu): int
    {
        if ($waktu === null || $waktu === '') {
            return 0;
        }
        $bagian = explode(':', $waktu);

        return ((int) $bagian[0]) * 3600 + ((int) ($bagian[1] ?? 0)) * 60 + (int) ($bagian[2] ?? 0);
    }

    /** "08:30:15" => "08:30"; null => "--:--" */
    public static function hm(?string $waktu): string
    {
        return $waktu ? substr($waktu, 0, 5) : '--:--';
    }

    public static function namaBulan(int $bulan): string
    {
        return self::NAMA_BULAN[$bulan] ?? '-';
    }
}
