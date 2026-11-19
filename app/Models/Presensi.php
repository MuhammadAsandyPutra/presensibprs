<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Presensi extends Model
{
    protected $table = 'presensi';

    public $timestamps = false;

    protected $fillable = [
        'nik', 'tanggal_presensi', 'jam_in', 'jam_out', 'foto_in', 'foto_out',
        'lokasi_in', 'lokasi_out', 'kode_jam_kerja', 'jarak_in', 'jarak_out',
        'akurasi_in', 'perlu_review', 'catatan', 'ip_address',
    ];

    protected function casts(): array
    {
        // Catatan: kolom tanggal SENGAJA tidak di-cast 'date' (Laravel menyimpannya sebagai
        // "Y-m-d 00:00:00" sehingga perbandingan string di SQLite meleset). Dibaca lewat accessor.
        return ['perlu_review' => 'boolean'];
    }

    protected function tanggalPresensi(): Attribute
    {
        return Attribute::get(fn ($nilai) => $nilai ? Carbon::parse($nilai) : null);
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }

    public function jamKerja(): BelongsTo
    {
        return $this->belongsTo(JamKerja::class, 'kode_jam_kerja', 'kode_jam_kerja');
    }

    public function menitTerlambat(): int
    {
        return $this->jamKerja?->menitTerlambat($this->jam_in) ?? 0;
    }

    public function pulangCepat(): bool
    {
        return $this->jamKerja?->pulangCepat($this->jam_out) ?? false;
    }
}
