<?php

namespace App\Models;

use App\Support\Geo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cabang extends Model
{
    protected $table = 'cabang';

    protected $primaryKey = 'kode_cabang';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['kode_cabang', 'nama_cabang', 'lokasi_cabang', 'radius_cabang'];

    public function karyawan(): HasMany
    {
        return $this->hasMany(Karyawan::class, 'kode_cabang', 'kode_cabang');
    }

    /** @return array{0: float, 1: float}|null [lat, lng] */
    public function koordinat(): ?array
    {
        return Geo::parse($this->lokasi_cabang);
    }
}
