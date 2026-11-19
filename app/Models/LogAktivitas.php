<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/** Jejak audit aksi sensitif di panel (siapa, apa, kapan, dari IP mana). */
class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';

    public $timestamps = false;

    protected $fillable = ['user_id', 'nama', 'aksi', 'deskripsi', 'ip_address', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public static function catat(string $aksi, string $deskripsi): void
    {
        $user = Auth::guard('user')->user();

        self::query()->create([
            'user_id' => $user?->getKey(),
            'nama' => $user?->name,
            'aksi' => $aksi,
            'deskripsi' => $deskripsi,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
