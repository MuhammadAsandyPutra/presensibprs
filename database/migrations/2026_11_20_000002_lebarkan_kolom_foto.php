<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Pada database lama, kolom foto bisa terlalu pendek sehingga nama berkas terpotong
 * (berkas ada di disk, tetapi tidak ditemukan saat ditampilkan).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ([['karyawan', 'foto'], ['presensi', 'foto_in'], ['presensi', 'foto_out'], ['pengajuan_izin', 'lampiran']] as [$tabel, $kolom]) {
            if (Schema::hasColumn($tabel, $kolom)) {
                Schema::table($tabel, fn (Blueprint $t) => $t->string($kolom, 255)->nullable()->change());
            }
        }
    }

    public function down(): void
    {
        // Sengaja kosong.
    }
};
