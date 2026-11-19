<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class HariLibur extends Model
{
    protected $table = 'hari_libur';

    public $timestamps = false;

    protected $fillable = ['tanggal', 'keterangan'];

    protected function tanggal(): Attribute
    {
        return Attribute::get(fn ($nilai) => $nilai ? Carbon::parse($nilai) : null);
    }
}
