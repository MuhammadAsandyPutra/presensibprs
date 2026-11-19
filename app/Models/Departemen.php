<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departemen extends Model
{
    protected $table = 'departemen';

    protected $primaryKey = 'kode_dept';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['kode_dept', 'nama_dept'];

    public function karyawan(): HasMany
    {
        return $this->hasMany(Karyawan::class, 'kode_dept', 'kode_dept');
    }
}
