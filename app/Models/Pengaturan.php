<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan yang bisa diubah HR dari panel. Bila belum pernah diubah, nilai diambil
 * dari config/presensi.php dengan kunci yang sama (mis. 'kpi.bobot_kehadiran').
 */
class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $primaryKey = 'kunci';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['kunci', 'nilai'];

    /** @var array<string, string|null>|null cache per-request */
    private static ?array $memo = null;

    public static function ambil(string $kunci, mixed $default = null): mixed
    {
        self::$memo ??= self::query()->pluck('nilai', 'kunci')->all();

        $nilai = self::$memo[$kunci] ?? null;
        if ($nilai !== null && $nilai !== '') {
            return is_numeric($nilai) ? $nilai + 0 : $nilai;
        }

        return config('presensi.'.$kunci, $default);
    }

    public static function simpan(string $kunci, mixed $nilai): void
    {
        self::query()->updateOrInsert(['kunci' => $kunci], ['nilai' => (string) $nilai]);
        self::lupakan();
    }

    public static function lupakan(): void
    {
        self::$memo = null;
    }
}
