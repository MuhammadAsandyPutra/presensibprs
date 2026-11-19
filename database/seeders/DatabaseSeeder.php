<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\JamKerja;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Data awal yang AMAN untuk produksi: master minimal + 1 akun admin.
 * Jalankan:  php artisan db:seed
 * (Data contoh karyawan + presensi: php artisan db:seed --class=DemoSeeder)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Cabang::query()->firstOrCreate(['kode_cabang' => 'PST'], [
            'nama_cabang' => 'Kantor Pusat',
            // GANTI dengan koordinat GPS kantor yang sebenarnya (menu Admin > Lokasi Kantor).
            'lokasi_cabang' => '-3.388000,114.635000',
            'radius_cabang' => 100,
        ]);

        foreach (['OPS' => 'Operasional', 'FND' => 'Funding', 'FIN' => 'Pembiayaan', 'ITS' => 'Teknologi Informasi', 'HRD' => 'Sumber Daya Manusia'] as $kode => $nama) {
            Departemen::query()->firstOrCreate(['kode_dept' => $kode], ['nama_dept' => $nama]);
        }

        JamKerja::query()->firstOrCreate(['kode_jam_kerja' => 'JK01'], [
            'nama_jam_kerja' => 'Reguler (Senin-Jumat)',
            'awal_jam_masuk' => '07:30', 'jam_masuk' => '09:00', 'akhir_jam_masuk' => '10:00',
            'jam_pulang' => '17:00', 'toleransi_menit' => 5,
        ]);

        $email = env('ADMIN_EMAIL', 'admin@example.com');
        if (! User::query()->where('email', $email)->exists()) {
            $password = env('ADMIN_PASSWORD') ?: Str::password(14);
            User::query()->create([
                'name' => env('ADMIN_NAME', 'Administrator'),
                'email' => $email,
                'password' => $password,
                'role' => User::ROLE_ADMIN,
            ]);
            $this->command?->warn("Akun admin dibuat: {$email}");
            if (! env('ADMIN_PASSWORD')) {
                $this->command?->warn("Kata sandi sementara: {$password}  (catat, lalu segera ganti)");
            }
        }
    }
}
