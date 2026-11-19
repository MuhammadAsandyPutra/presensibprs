<?php

namespace Tests\Support;

use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\JamKerja;
use App\Models\Karyawan;
use App\Models\Pengaturan;
use App\Models\User;

/** Pembuat data uji (tanpa factory agar mudah dibaca). */
trait MembuatData
{
    protected function siapkanMaster(): array
    {
        Pengaturan::lupakan();

        $cabang = Cabang::query()->create(['kode_cabang' => 'PST', 'nama_cabang' => 'Pusat', 'lokasi_cabang' => '-3.388000,114.635000', 'radius_cabang' => 100]);
        Departemen::query()->create(['kode_dept' => 'OPS', 'nama_dept' => 'Operasional']);
        $jam = JamKerja::query()->create([
            'kode_jam_kerja' => 'JK01', 'nama_jam_kerja' => 'Reguler', 'awal_jam_masuk' => '07:30',
            'jam_masuk' => '09:00', 'akhir_jam_masuk' => '10:00', 'jam_pulang' => '17:00', 'toleransi_menit' => 5,
        ]);

        return [$cabang, $jam];
    }

    protected function buatKaryawan(string $nik = '1001', array $lebih = []): Karyawan
    {
        return Karyawan::query()->create(array_merge([
            'nik' => $nik, 'nama_lengkap' => 'Karyawan '.$nik, 'jabatan' => 'Staf', 'kode_dept' => 'OPS',
            'kode_cabang' => 'PST', 'password' => 'rahasia123', 'aktif' => true, 'jatah_cuti' => 12,
        ], $lebih));
    }

    protected function buatAdmin(string $role = User::ROLE_ADMIN): User
    {
        return User::query()->create(['name' => 'Admin Uji', 'email' => "{$role}@uji.test", 'password' => 'rahasia123', 'role' => $role]);
    }

    /** Foto JPEG valid (>1 KB) dalam bentuk data URL, seperti yang dikirim browser. */
    protected function fotoDataUrl(): string
    {
        $gambar = imagecreatetruecolor(120, 120);
        for ($x = 0; $x < 120; $x++) {
            for ($y = 0; $y < 120; $y++) {
                imagesetpixel($gambar, $x, $y, imagecolorallocate($gambar, ($x * 7) % 256, ($y * 13) % 256, ($x * $y) % 256));
            }
        }
        ob_start();
        imagejpeg($gambar, null, 90);
        $biner = ob_get_clean();

        return 'data:image/jpeg;base64,'.base64_encode($biner);
    }
}
