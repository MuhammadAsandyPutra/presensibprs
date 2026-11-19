<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('konfigurasi_jamkerja')) {
            Schema::create('konfigurasi_jamkerja', function (Blueprint $t) {
                $t->id();
                $t->string('nik', 20)->index();
                $t->string('hari', 10); // Senin..Minggu
                $t->char('kode_jam_kerja', 4);
                $t->unique(['nik', 'hari']);
            });
        }

        if (! Schema::hasTable('presensi')) {
            Schema::create('presensi', function (Blueprint $t) {
                $t->id();
                $t->string('nik', 20)->index();
                $t->date('tanggal_presensi')->index();
                $t->time('jam_in')->nullable();
                $t->time('jam_out')->nullable();
                $t->string('foto_in')->nullable();
                $t->string('foto_out')->nullable();
                $t->string('lokasi_in', 60)->nullable();  // "lat,lng"
                $t->string('lokasi_out', 60)->nullable();
                $t->char('kode_jam_kerja', 4)->nullable();
                $t->unsignedInteger('jarak_in')->nullable();  // meter dari kantor
                $t->unsignedInteger('jarak_out')->nullable();
                $t->unsignedSmallInteger('akurasi_in')->nullable(); // meter
                $t->boolean('perlu_review')->default(false);
                $t->string('catatan')->nullable();
                $t->string('ip_address', 45)->nullable();
                $t->unique(['nik', 'tanggal_presensi']); // satu baris per karyawan per hari
            });
        } else {
            Schema::table('presensi', function (Blueprint $t) {
                foreach ([
                    'kode_jam_kerja' => fn () => $t->char('kode_jam_kerja', 4)->nullable(),
                    'jarak_in' => fn () => $t->unsignedInteger('jarak_in')->nullable(),
                    'jarak_out' => fn () => $t->unsignedInteger('jarak_out')->nullable(),
                    'akurasi_in' => fn () => $t->unsignedSmallInteger('akurasi_in')->nullable(),
                    'perlu_review' => fn () => $t->boolean('perlu_review')->default(false),
                    'catatan' => fn () => $t->string('catatan')->nullable(),
                    'ip_address' => fn () => $t->string('ip_address', 45)->nullable(),
                ] as $kolom => $tambah) {
                    if (! Schema::hasColumn('presensi', $kolom)) {
                        $tambah();
                    }
                }
            });
        }

        if (! Schema::hasTable('pengajuan_izin')) {
            Schema::create('pengajuan_izin', function (Blueprint $t) {
                $t->id();
                $t->string('nik', 20)->index();
                $t->date('tgl_izin');                       // tanggal mulai
                $t->date('tgl_selesai')->nullable();        // null = sama dengan tgl_izin
                $t->unsignedTinyInteger('status')->default(1);          // JenisPengajuan
                $t->text('keterangan')->nullable();
                $t->unsignedTinyInteger('status_approved')->default(0); // StatusPengajuan
                $t->unsignedSmallInteger('jumlah_hari')->default(1);
                $t->string('lampiran')->nullable();
                $t->string('catatan_hr')->nullable();
                $t->unsignedBigInteger('diputuskan_oleh')->nullable();
                $t->timestamp('diputuskan_pada')->nullable();
                $t->timestamps();
            });
        } else {
            Schema::table('pengajuan_izin', function (Blueprint $t) {
                if (! Schema::hasColumn('pengajuan_izin', 'tgl_selesai')) {
                    $t->date('tgl_selesai')->nullable();
                }
                if (! Schema::hasColumn('pengajuan_izin', 'jumlah_hari')) {
                    $t->unsignedSmallInteger('jumlah_hari')->default(1);
                }
                if (! Schema::hasColumn('pengajuan_izin', 'lampiran')) {
                    $t->string('lampiran')->nullable();
                }
                if (! Schema::hasColumn('pengajuan_izin', 'catatan_hr')) {
                    $t->string('catatan_hr')->nullable();
                }
                if (! Schema::hasColumn('pengajuan_izin', 'diputuskan_oleh')) {
                    $t->unsignedBigInteger('diputuskan_oleh')->nullable();
                }
                if (! Schema::hasColumn('pengajuan_izin', 'diputuskan_pada')) {
                    $t->timestamp('diputuskan_pada')->nullable();
                }
                if (! Schema::hasColumn('pengajuan_izin', 'created_at')) {
                    $t->timestamps();
                }
            });
        }

        if (! Schema::hasTable('hari_libur')) {
            Schema::create('hari_libur', function (Blueprint $t) {
                $t->id();
                $t->date('tanggal')->unique();
                $t->string('keterangan', 100);
            });
        }
    }

    public function down(): void
    {
        // Sengaja kosong (lihat catatan pada migrasi tabel master).
    }
};
