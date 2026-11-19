<?php

namespace Tests\Feature;

use App\Enums\StatusPengajuan;
use App\Exceptions\AturanBisnisException;
use App\Models\HariLibur;
use App\Models\PengajuanIzin;
use App\Services\PengajuanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Support\MembuatData;
use Tests\TestCase;

class PengajuanTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private PengajuanService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-16 08:00:00'); // Senin
        $this->siapkanMaster();
        $this->service = app(PengajuanService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function data(array $o = []): array
    {
        return array_merge(['jenis' => 3, 'tgl_izin' => '2026-11-23', 'tgl_selesai' => '2026-11-25', 'keterangan' => 'Acara keluarga'], $o);
    }

    public function test_hari_kerja_mengecualikan_akhir_pekan_dan_libur(): void
    {
        HariLibur::query()->create(['tanggal' => '2026-11-18', 'keterangan' => 'Libur uji']);
        $k = $this->buatKaryawan();

        // Sen 16 - Jum 20 (5 hari) dikurangi 1 libur = 4
        $this->assertSame(4, $this->service->hitungHariKerja($k, Carbon::parse('2026-11-16'), Carbon::parse('2026-11-20')));
        // Sen 16 - Sen 23 termasuk sabtu-minggu: 6 hari kerja - 1 libur = 5
        $this->assertSame(5, $this->service->hitungHariKerja($k, Carbon::parse('2026-11-16'), Carbon::parse('2026-11-23')));
    }

    public function test_cuti_tersimpan_menunggu_dan_mengurangi_sisa(): void
    {
        $k = $this->buatKaryawan();
        $p = $this->service->ajukan($k, $this->data());

        $this->assertSame(StatusPengajuan::Menunggu, $p->status_approved);
        $this->assertSame(3, $p->jumlah_hari);
        $this->assertSame(9, $this->service->sisaCuti($k, 2026));
    }

    public function test_cuti_melebihi_saldo_ditolak(): void
    {
        $k = $this->buatKaryawan('1001', ['jatah_cuti' => 2]);

        $this->expectException(AturanBisnisException::class);
        $this->service->ajukan($k, $this->data()); // 3 hari > 2
    }

    public function test_tanggal_bentrok_ditolak(): void
    {
        $k = $this->buatKaryawan();
        $this->service->ajukan($k, $this->data());

        $this->expectException(AturanBisnisException::class);
        $this->service->ajukan($k, $this->data(['jenis' => 1, 'tgl_izin' => '2026-11-24', 'tgl_selesai' => '2026-11-24']));
    }

    public function test_kuota_izin_bulanan(): void
    {
        $k = $this->buatKaryawan();
        foreach (['2026-11-17', '2026-11-18', '2026-11-19'] as $tgl) {
            $this->service->ajukan($k, $this->data(['jenis' => 1, 'tgl_izin' => $tgl, 'tgl_selesai' => $tgl]));
        }

        $this->expectException(AturanBisnisException::class);
        $this->service->ajukan($k, $this->data(['jenis' => 1, 'tgl_izin' => '2026-11-20', 'tgl_selesai' => '2026-11-20']));
    }

    public function test_hr_menyetujui_dan_bisa_membatalkan_keputusan(): void
    {
        $k = $this->buatKaryawan();
        $hr = $this->buatAdmin('hr');
        $p = $this->service->ajukan($k, $this->data());

        $this->service->putuskan($p, StatusPengajuan::Disetujui, $hr, 'OK');
        $this->assertSame(StatusPengajuan::Disetujui, $p->fresh()->status_approved);

        $this->service->kembalikanKeMenunggu($p->fresh());
        $this->assertSame(StatusPengajuan::Menunggu, $p->fresh()->status_approved);
    }

    public function test_karyawan_hanya_bisa_membatalkan_miliknya(): void
    {
        $a = $this->buatKaryawan('1001');
        $b = $this->buatKaryawan('1002');
        $p = $this->service->ajukan($a, $this->data());

        $this->expectException(AturanBisnisException::class);
        $this->service->batal($b, $p);
    }

    public function test_pengajuan_milik_orang_lain_tidak_bisa_dibatalkan_lewat_http(): void
    {
        $a = $this->buatKaryawan('1001');
        $b = $this->buatKaryawan('1002');
        $p = $this->service->ajukan($a, $this->data());

        $this->actingAs($b, 'karyawan')->post(route('izin.batal', $p))->assertSessionHas('galat');
        $this->assertSame(StatusPengajuan::Menunggu, PengajuanIzin::query()->find($p->id)->status_approved);
    }
}
