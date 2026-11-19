<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MembuatData;
use Tests\TestCase;

class PemisahanPortalTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMaster();
    }

    public function test_tamu_diarahkan_ke_login_portal_yang_sesuai(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/panel/dashboard')->assertRedirect(route('panel.login'));
    }

    public function test_karyawan_tidak_bisa_membuka_panel(): void
    {
        $k = $this->buatKaryawan();

        $this->actingAs($k, 'karyawan')->get('/dashboard')->assertOk();
        $this->actingAs($k, 'karyawan')->get('/panel/dashboard')->assertRedirect(route('panel.login'));
    }

    public function test_admin_tidak_bisa_membuka_portal_karyawan(): void
    {
        $this->actingAs($this->buatAdmin(), 'user')->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_hr_tidak_bisa_membuka_konfigurasi_khusus_admin(): void
    {
        $hr = $this->buatAdmin(User::ROLE_HR);

        $this->actingAs($hr, 'user')->get('/panel/dashboard')->assertOk();
        $this->actingAs($hr, 'user')->get('/panel/cabang')->assertForbidden();
        $this->actingAs($hr, 'user')->get('/panel/jam-kerja')->assertForbidden();
    }

    public function test_login_karyawan_nonaktif_ditolak(): void
    {
        $this->buatKaryawan('2002', ['aktif' => false]);

        $this->post('/login', ['nik' => '2002', 'password' => 'rahasia123'])->assertSessionHas('galat');
        $this->assertGuest('karyawan');
    }

    public function test_login_karyawan_berhasil(): void
    {
        $this->buatKaryawan('2003');

        $this->post('/login', ['nik' => '2003', 'password' => 'rahasia123'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticated('karyawan');
    }

    public function test_karyawan_tidak_bisa_membuka_foto_milik_orang_lain(): void
    {
        $a = $this->buatKaryawan('3001');
        $b = $this->buatKaryawan('3002', ['foto' => 'profil/x.jpg']);

        $this->actingAs($a, 'karyawan')->get(route('berkas.profil', $b))->assertForbidden();
    }
}
