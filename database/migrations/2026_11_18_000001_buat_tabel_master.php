<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Tabel master. Setiap blok dijaga `hasTable/hasColumn` agar migrasi AMAN dijalankan
 * pada database lama (tabel sudah ada) maupun database baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('departemen')) {
            Schema::create('departemen', function (Blueprint $t) {
                $t->string('kode_dept', 10)->primary();
                $t->string('nama_dept', 100);
            });
        }

        if (! Schema::hasTable('cabang')) {
            Schema::create('cabang', function (Blueprint $t) {
                $t->string('kode_cabang', 10)->primary();
                $t->string('nama_cabang', 100);
                $t->string('lokasi_cabang', 60); // "latitude,longitude"
                $t->unsignedSmallInteger('radius_cabang')->default(100); // meter
            });
        }

        if (! Schema::hasTable('jam_kerja')) {
            Schema::create('jam_kerja', function (Blueprint $t) {
                $t->char('kode_jam_kerja', 4)->primary();
                $t->string('nama_jam_kerja', 50);
                $t->time('awal_jam_masuk'); // paling awal boleh presensi masuk
                $t->time('jam_masuk');      // jadwal masuk
                $t->time('akhir_jam_masuk'); // batas akhir presensi masuk
                $t->time('jam_pulang');
                $t->unsignedTinyInteger('toleransi_menit')->default(0);
            });
        } elseif (! Schema::hasColumn('jam_kerja', 'toleransi_menit')) {
            Schema::table('jam_kerja', function (Blueprint $t) {
                $t->unsignedTinyInteger('toleransi_menit')->default(0);
            });
        }

        if (! Schema::hasTable('karyawan')) {
            Schema::create('karyawan', function (Blueprint $t) {
                $t->string('nik', 20)->primary();
                $t->string('nama_lengkap', 100);
                $t->string('jabatan', 60)->nullable();
                $t->string('no_hp', 20)->nullable();
                $t->string('foto')->nullable();
                $t->string('kode_dept', 10)->nullable()->index();
                $t->string('kode_cabang', 10)->nullable()->index();
                $t->string('password');
                $t->boolean('aktif')->default(true);
                $t->unsignedTinyInteger('jatah_cuti')->default(12);
                $t->rememberToken();
            });
        } else {
            Schema::table('karyawan', function (Blueprint $t) {
                if (! Schema::hasColumn('karyawan', 'aktif')) {
                    $t->boolean('aktif')->default(true);
                }
                if (! Schema::hasColumn('karyawan', 'jatah_cuti')) {
                    $t->unsignedTinyInteger('jatah_cuti')->default(12);
                }
                if (! Schema::hasColumn('karyawan', 'remember_token')) {
                    $t->rememberToken();
                }
            });
        }
    }

    public function down(): void
    {
        // Sengaja kosong: tabel master berisi data penting, jangan dihapus otomatis.
    }
};
