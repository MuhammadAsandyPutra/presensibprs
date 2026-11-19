<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use App\Models\Presensi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * DATA CONTOH untuk mencoba aplikasi (jangan dipakai di produksi).
 *   php artisan db:seed --class=DemoSeeder
 * Login karyawan: NIK 1001..1006, kata sandi "karyawan123".
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DatabaseSeeder::class);

        $contoh = [
            ['1001', 'Raka Pratama', 'Teller', 'OPS', [0, 0, 0, 12, 0, 0, 0, 21, 0, 0, 0, 0]],
            ['1002', 'Sinta Dewi', 'Staf Operasional', 'OPS', [0, 0, 0, 0, 0, 9, 0, 0, 0, 0, 0, 0]],
            ['1003', 'Budi Santoso', 'Marketing Pembiayaan', 'FIN', [0, 25, 0, -1, 18, 0, 0, 21, 0, 0, 0, 0]],
            ['1004', 'Maya Putri', 'Funding Officer', 'FND', [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]],
            ['1005', 'Andi Wijaya', 'Staf IT', 'ITS', [0, 0, 20, 0, 0, 22, 0, 0, 0, 0, 0, 0]],
            ['1006', 'Lina Marlina', 'Customer Service', 'OPS', [0, 0, 0, 0, 0, 0, 9, 0, 0, 0, 0, 0]],
        ];

        $hari = collect();
        for ($d = now()->subDays(20)->startOfDay(); $d->lt(now()->startOfDay()); $d->addDay()) {
            if ($d->isWeekday()) {
                $hari->push($d->copy());
            }
        }
        $hari = $hari->take(-12)->values();

        foreach ($contoh as [$nik, $nama, $jabatan, $dept, $pola]) {
            Karyawan::query()->updateOrCreate(['nik' => $nik], [
                'nama_lengkap' => $nama, 'jabatan' => $jabatan, 'kode_dept' => $dept, 'kode_cabang' => 'PST',
                'password' => 'karyawan123', 'aktif' => true, 'jatah_cuti' => 12,
            ]);

            foreach ($hari as $i => $tanggal) {
                $terlambat = $pola[$i] ?? 0; // -1 = tidak hadir (tanpa keterangan)
                if ($terlambat < 0 || Presensi::query()->where('nik', $nik)->where('tanggal_presensi', $tanggal->toDateString())->exists()) {
                    continue;
                }
                $masuk = Carbon::parse('09:00')->addMinutes($terlambat > 0 ? $terlambat : -(5 + $i % 20));
                Presensi::query()->create([
                    'nik' => $nik,
                    'tanggal_presensi' => $tanggal->toDateString(),
                    'jam_in' => $masuk->format('H:i:s'),
                    'jam_out' => Carbon::parse('17:00')->addMinutes($i % 7)->format('H:i:s'),
                    'lokasi_in' => '-3.388100,114.635050',
                    'kode_jam_kerja' => 'JK01',
                    'jarak_in' => 12 + ($i * 7) % 60,
                    'akurasi_in' => 15,
                ]);
            }
        }
    }
}
