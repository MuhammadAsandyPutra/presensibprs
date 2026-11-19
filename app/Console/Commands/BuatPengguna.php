<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BuatPengguna extends Command
{
    protected $signature = 'presensi:buat-pengguna {email} {nama} {--role=hr : admin atau hr} {--password= : kosongkan untuk dibuat acak}';

    protected $description = 'Buat akun panel (Admin/HR)';

    public function handle(): int
    {
        $role = $this->option('role');
        if (! in_array($role, [User::ROLE_ADMIN, User::ROLE_HR], true)) {
            $this->error('Role harus "admin" atau "hr".');

            return self::FAILURE;
        }
        if (User::query()->where('email', $this->argument('email'))->exists()) {
            $this->error('Email sudah terdaftar.');

            return self::FAILURE;
        }

        $password = $this->option('password') ?: Str::password(14);
        User::query()->create([
            'name' => $this->argument('nama'),
            'email' => $this->argument('email'),
            'password' => $password,
            'role' => $role,
        ]);

        $this->info("Akun {$role} dibuat: {$this->argument('email')}");
        if (! $this->option('password')) {
            $this->warn("Kata sandi sementara: {$password}  (catat, lalu segera ganti)");
        }

        return self::SUCCESS;
    }
}
