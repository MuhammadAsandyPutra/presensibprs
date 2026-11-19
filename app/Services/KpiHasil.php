<?php

namespace App\Services;

use App\Enums\KategoriKpi;
use App\Models\Karyawan;

/** Hasil perhitungan KPI disiplin satu karyawan untuk satu periode. */
final class KpiHasil
{
    public function __construct(
        public readonly Karyawan $karyawan,
        public readonly int $hariKerja,
        public readonly int $hadir,
        public readonly int $terlambatKali,
        public readonly int $menitTerlambat,
        public readonly int $pulangCepat,
        public readonly int $izin,
        public readonly int $sakit,
        public readonly int $cuti,
        public readonly int $alpa,
        public readonly float $kehadiran,
        public readonly float $ketepatan,
        public readonly float $disiplin,
        public readonly float $skor,
        public readonly KategoriKpi $kategori,
    ) {}

    public function hariEfektif(): int
    {
        return $this->hariKerja - $this->izin - $this->sakit - $this->cuti;
    }
}
