<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/** Unduhan CSV yang langsung terbuka benar di Excel (BOM UTF-8) dan aman dari CSV/formula injection. */
final class Csv
{
    /**
     * @param  array<int, string>  $header
     * @param  iterable<int, array<int, mixed>>  $baris
     */
    public static function unduh(string $namaBerkas, array $header, iterable $baris): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $baris) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $header, ';', '"', '\\');
            foreach ($baris as $row) {
                fputcsv($out, array_map([self::class, 'aman'], $row), ';', '"', '\\');
            }
            fclose($out);
        }, $namaBerkas, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Awali sel berisi = + - @ dengan apostrof supaya tidak dieksekusi sebagai rumus. */
    public static function aman(mixed $nilai): mixed
    {
        if (is_string($nilai) && $nilai !== '' && in_array($nilai[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$nilai;
        }

        return $nilai;
    }
}
