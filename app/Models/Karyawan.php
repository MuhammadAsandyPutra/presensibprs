<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/** Akun karyawan (guard `karyawan`, login memakai NIK). */
class Karyawan extends Authenticatable
{
    protected $table = 'karyawan';

    protected $primaryKey = 'nik';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'nik', 'nama_lengkap', 'jabatan', 'no_hp', 'foto', 'kode_dept', 'kode_cabang',
        'password', 'aktif', 'jatah_cuti',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'aktif' => 'boolean',
            'jatah_cuti' => 'integer',
        ];
    }

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class, 'kode_dept', 'kode_dept');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'kode_cabang', 'kode_cabang');
    }

    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'nik', 'nik');
    }

    public function pengajuan(): HasMany
    {
        return $this->hasMany(PengajuanIzin::class, 'nik', 'nik');
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalKaryawan::class, 'nik', 'nik');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    public function namaDepan(): string
    {
        return explode(' ', trim($this->nama_lengkap))[0] ?? $this->nama_lengkap;
    }

    /** Jatah cuti tahunan: nilai per karyawan, atau default dari pengaturan. */
    public function jatahCutiTahunan(): int
    {
        return (int) ($this->jatah_cuti ?: Pengaturan::ambil('cuti.jatah_tahunan'));
    }
}
