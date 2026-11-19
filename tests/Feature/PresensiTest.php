<?php

namespace Tests\Feature;

use App\Models\Presensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\Support\MembuatData;
use Tests\TestCase;

class PresensiTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private const DI_KANTOR = '-3.388050,114.635020';

    private const DI_LUAR = '-3.390500,114.635000'; // ± 278 m

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->siapkanMaster();
    }

    private function kirim(string $lokasi, float $akurasi = 10.0)
    {
        return $this->postJson(route('presensi.store'), ['lokasi' => $lokasi, 'akurasi' => $akurasi, 'foto' => $this->fotoDataUrl()]);
    }

    public function test_presensi_masuk_dalam_radius_berhasil_dan_tercatat_terlambat(): void
    {
        Carbon::setTestNow('2026-11-16 09:12:00'); // Senin
        $k = $this->buatKaryawan();

        $this->actingAs($k, 'karyawan');
        $this->kirim(self::DI_KANTOR)->assertOk()->assertJson(['ok' => true, 'jenis' => 'masuk', 'terlambat_menit' => 12]);

        $p = Presensi::query()->where('nik', '1001')->firstOrFail();
        $this->assertSame('09:12:00', $p->jam_in);
        $this->assertNotNull($p->foto_in);
        Storage::disk('local')->assertExists($p->foto_in);
    }

    public function test_presensi_di_luar_radius_ditolak(): void
    {
        Carbon::setTestNow('2026-11-16 08:55:00');
        $this->actingAs($this->buatKaryawan(), 'karyawan');

        $this->kirim(self::DI_LUAR)->assertStatus(422)->assertJson(['ok' => false, 'kode' => 'radius']);
        $this->assertDatabaseCount('presensi', 0);
    }

    public function test_akurasi_gps_buruk_ditolak(): void
    {
        Carbon::setTestNow('2026-11-16 08:55:00');
        $this->actingAs($this->buatKaryawan(), 'karyawan');

        $this->kirim(self::DI_KANTOR, 450)->assertStatus(422)->assertJson(['kode' => 'akurasi']);
    }

    public function test_tidak_bisa_presensi_di_hari_minggu(): void
    {
        Carbon::setTestNow('2026-11-15 09:00:00'); // Minggu
        $this->actingAs($this->buatKaryawan(), 'karyawan');

        $this->kirim(self::DI_KANTOR)->assertStatus(422)->assertJson(['kode' => 'jadwal']);
    }

    public function test_alur_masuk_lalu_pulang_dan_tidak_bisa_ganda(): void
    {
        $this->actingAs($this->buatKaryawan(), 'karyawan');

        Carbon::setTestNow('2026-11-16 08:50:00');
        $this->kirim(self::DI_KANTOR)->assertOk()->assertJson(['jenis' => 'masuk', 'terlambat_menit' => 0]);

        Carbon::setTestNow('2026-11-16 12:00:00'); // belum waktunya pulang
        $this->kirim(self::DI_KANTOR)->assertStatus(422)->assertJson(['kode' => 'waktu']);

        Carbon::setTestNow('2026-11-16 17:05:00');
        $this->kirim(self::DI_KANTOR)->assertOk()->assertJson(['jenis' => 'pulang']);

        $this->kirim(self::DI_KANTOR)->assertStatus(422)->assertJson(['kode' => 'selesai']);
        $this->assertSame('17:05:00', Presensi::query()->first()->jam_out);
    }

    public function test_foto_bukan_gambar_ditolak(): void
    {
        Carbon::setTestNow('2026-11-16 08:55:00');
        $this->actingAs($this->buatKaryawan(), 'karyawan');

        $this->postJson(route('presensi.store'), ['lokasi' => self::DI_KANTOR, 'akurasi' => 10, 'foto' => 'data:text/html;base64,PGgxPmhhY2s8L2gxPg=='])
            ->assertStatus(422)->assertJson(['kode' => 'foto']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
}
