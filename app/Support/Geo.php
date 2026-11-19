<?php

namespace App\Support;

/** Fungsi geografi murni (tanpa database) untuk validasi lokasi presensi. */
final class Geo
{
    private const RADIUS_BUMI_METER = 6371000;

    /** Jarak dua titik (meter) dengan rumus Haversine, dibulatkan. */
    public static function jarak(float $lat1, float $lng1, float $lat2, float $lng2): int
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $a = min(1.0, max(0.0, $a));

        return (int) round(self::RADIUS_BUMI_METER * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    /**
     * Ubah teks "lat,lng" menjadi [lat, lng]; null bila format/rentang tidak valid.
     *
     * @return array{0: float, 1: float}|null
     */
    public static function parse(?string $lokasi): ?array
    {
        if ($lokasi === null) {
            return null;
        }
        $bagian = array_map('trim', explode(',', $lokasi));
        if (count($bagian) !== 2 || ! is_numeric($bagian[0]) || ! is_numeric($bagian[1])) {
            return null;
        }
        [$lat, $lng] = [(float) $bagian[0], (float) $bagian[1]];

        return ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) ? [$lat, $lng] : null;
    }
}
