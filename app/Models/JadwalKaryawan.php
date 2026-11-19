<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Jam kerja yang berlaku untuk satu karyawan pada satu hari dalam seminggu. */
class JadwalKaryawan extends Model
{
    protected $table = 'konfigurasi_jamkerja';

    public $timestamps = false;

    protected $fillable = ['nik', 'hari', 'kode_jam_kerja'];

    public function jamKerja(): BelongsTo
    {
        return $this->belongsTo(JamKerja::class, 'kode_jam_kerja', 'kode_jam_kerja');
    }
}
