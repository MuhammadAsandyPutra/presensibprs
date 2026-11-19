<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pengaturan')) {
            Schema::create('pengaturan', function (Blueprint $t) {
                $t->string('kunci', 60)->primary(); // mis. kpi.bobot_kehadiran
                $t->text('nilai')->nullable();
            });
        }

        if (! Schema::hasTable('log_aktivitas')) {
            Schema::create('log_aktivitas', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->nullable();
                $t->string('nama', 100)->nullable();
                $t->string('aksi', 50);
                $t->text('deskripsi');
                $t->string('ip_address', 45)->nullable();
                $t->timestamp('created_at')->useCurrent();
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $t) {
                $t->string('role', 10)->default('hr'); // admin | hr
            });
        }
    }

    public function down(): void
    {
        // Sengaja kosong.
    }
};
