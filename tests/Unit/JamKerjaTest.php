<?php

namespace Tests\Unit;

use App\Models\JamKerja;
use PHPUnit\Framework\TestCase;

class JamKerjaTest extends TestCase
{
    private function jam(int $toleransi = 5): JamKerja
    {
        return new JamKerja(['jam_masuk' => '09:00:00', 'jam_pulang' => '17:00:00', 'toleransi_menit' => $toleransi]);
    }

    public function test_dalam_toleransi_tidak_terlambat(): void
    {
        $this->assertSame(0, $this->jam(5)->menitTerlambat('09:05:00'));
        $this->assertSame(0, $this->jam(5)->menitTerlambat('08:40:00'));
    }

    public function test_melewati_toleransi_dihitung_dari_jam_masuk(): void
    {
        $this->assertSame(12, $this->jam(5)->menitTerlambat('09:12:30'));
    }

    public function test_tanpa_toleransi(): void
    {
        $this->assertSame(1, $this->jam(0)->menitTerlambat('09:01:00'));
    }

    public function test_pulang_cepat(): void
    {
        $this->assertTrue($this->jam()->pulangCepat('16:30:00'));
        $this->assertFalse($this->jam()->pulangCepat('17:00:00'));
        $this->assertFalse($this->jam()->pulangCepat(null));
    }
}
