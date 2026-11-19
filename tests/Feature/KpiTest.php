<?php

namespace Tests\Feature;

use App\Enums\KategoriKpi;
use App\Models\Presensi;
use App\Services\KpiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Support\MembuatData;
use Tests\TestCase;

class KpiTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMaster();
        // Rabu 18 Nov 2026 pukul 12:00 => hari kerja berjalan: Sen 2 s/d Rab 18 (hari ini belum dihitung alpa).
        Carbon::setTestNow('2026-11-18 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function hadir(string $nik, string $tanggal, string $masuk = '08:50:00', string $pulang = '17:05:00'): void
    {
        Presensi::query()->create(['nik' => $nik, 'tanggal_presensi' => $tanggal, 'jam_in' => $masuk, 'jam_out' => $pulang, 'kode_jam_kerja' => 'JK01']);
    }

    public function test_karyawan_sempurna_mendapat_skor_100(): void
    {
        $this->buatKaryawan('1001');
        // Nov 2026: hari kerja 2-6, 9-13, 16-18 = 13 hari; hari ini (18) belum presensi => tidak dihitung
        foreach (['02', '03', '04', '05', '06', '09', '10', '11', '12', '13', '16', '17'] as $tgl) {
            $this->hadir('1001', "2026-11-{$tgl}");
        }

        $hasil = app(KpiService::class)->hitung(11, 2026)->first();

        $this->assertSame(12, $hasil->hadir);
        $this->assertSame(0, $hasil->alpa);
        $this->assertEqualsWithDelta(100.0, $hasil->skor, 0.01);
        $this->assertSame(KategoriKpi::SangatBaik, $hasil->kategori);
    }

    public function test_terlambat_dan_alpa_menurunkan_skor(): void
    {
        $this->buatKaryawan('1002');
        foreach (['02', '03', '04', '05', '06', '09', '10', '11', '12'] as $tgl) {
            $this->hadir('1002', "2026-11-{$tgl}");
        }
        $this->hadir('1002', '2026-11-13', '09:30:00'); // terlambat 30 menit
        // 16 & 17 Nov: tidak hadir tanpa keterangan => alpa 2

        $h = app(KpiService::class)->hitung(11, 2026)->first();

        $this->assertSame(10, $h->hadir);
        $this->assertSame(1, $h->terlambatKali);
        $this->assertSame(30, $h->menitTerlambat);
        $this->assertSame(2, $h->alpa);
        $this->assertEqualsWithDelta(10 / 12 * 100, $h->kehadiran, 0.01);
        // disiplin = 100 - 30*0.2 - 2*10 = 74
        $this->assertEqualsWithDelta(74.0, $h->disiplin, 0.01);
        $this->assertLessThan(90, $h->skor);
    }

    public function test_peringkat_terurut_dari_skor_tertinggi(): void
    {
        $this->buatKaryawan('1001');
        $this->buatKaryawan('1002');
        foreach (['02', '03', '04', '05', '06', '09', '10', '11', '12', '13', '16', '17'] as $tgl) {
            $this->hadir('1001', "2026-11-{$tgl}");
        }

        $hasil = app(KpiService::class)->hitung(11, 2026);

        $this->assertSame('1001', $hasil->first()->karyawan->nik);
        $this->assertSame('1002', $hasil->last()->karyawan->nik);
    }

    public function test_periode_masa_depan_kosong(): void
    {
        $this->buatKaryawan();
        $this->assertTrue(app(KpiService::class)->hitung(12, 2026)->isEmpty());
    }

    public function test_halaman_kpi_dan_unduh_csv_dapat_dibuka_admin(): void
    {
        $this->buatKaryawan();
        $admin = $this->buatAdmin();

        $this->actingAs($admin, 'user')->get(route('panel.kpi', ['bulan' => 11, 'tahun' => 2026]))->assertOk()->assertSee('KPI Disiplin');
        $this->actingAs($admin, 'user')->get(route('panel.kpi.csv', ['bulan' => 11, 'tahun' => 2026]))->assertOk();
    }
}
