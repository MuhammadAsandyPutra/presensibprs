<?php

namespace App\Models;

use App\Support\Waktu;
use Illuminate\Database\Eloquent\Model;

class JamKerja extends Model
{
    protected $table = 'jam_kerja';

    protected $primaryKey = 'kode_jam_kerja';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'kode_jam_kerja', 'nama_jam_kerja', 'awal_jam_masuk', 'jam_masuk',
        'akhir_jam_masuk', 'jam_pulang', 'toleransi_menit',
    ];

    /**
     * Menit keterlambatan, dihitung dari jam_masuk.
     * Bila tiba dalam batas toleransi => 0. Contoh: masuk 09:00, toleransi 10, tiba 09:08 => 0,
     * tiba 09:12 => 12 menit.
     */
    public function menitTerlambat(?string $jamIn): int
    {
        if (! $jamIn) {
            return 0;
        }
        $masuk = Waktu::detik($this->jam_masuk);
        $batas = $masuk + ((int) $this->toleransi_menit) * 60;
        $tiba = Waktu::detik($jamIn);

        return $tiba <= $batas ? 0 : intdiv($tiba - $masuk, 60);
    }

    public function pulangCepat(?string $jamOut): bool
    {
        return $jamOut !== null && Waktu::detik($jamOut) < Waktu::detik($this->jam_pulang);
    }
}
