<?php

namespace App\Models;

use App\Enums\JenisPengajuan;
use App\Enums\StatusPengajuan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PengajuanIzin extends Model
{
    protected $table = 'pengajuan_izin';

    protected $fillable = [
        'nik', 'tgl_izin', 'tgl_selesai', 'status', 'keterangan', 'status_approved',
        'jumlah_hari', 'lampiran', 'catatan_hr', 'diputuskan_oleh', 'diputuskan_pada',
    ];

    protected function casts(): array
    {
        return [
            'diputuskan_pada' => 'datetime',
            'status' => JenisPengajuan::class,          // jenis pengajuan
            'status_approved' => StatusPengajuan::class, // keputusan HR
        ];
    }

    // Kolom tanggal dibaca lewat accessor (bukan cast 'date') agar tersimpan sebagai "Y-m-d" polos.
    protected function tglIzin(): Attribute
    {
        return Attribute::get(fn ($nilai) => $nilai ? Carbon::parse($nilai) : null);
    }

    protected function tglSelesai(): Attribute
    {
        return Attribute::get(fn ($nilai) => $nilai ? Carbon::parse($nilai) : null);
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }

    public function jenis(): JenisPengajuan
    {
        return $this->status;
    }

    public function tanggalAkhir(): Carbon
    {
        return ($this->tgl_selesai ?? $this->tgl_izin)->copy();
    }

    /** Pengajuan yang masih berlaku (menunggu / disetujui). */
    public function scopeBerlaku(Builder $query): Builder
    {
        return $query->whereIn('status_approved', StatusPengajuan::aktif());
    }

    /** Pengajuan yang rentang tanggalnya beririsan dengan [mulai, selesai]. */
    public function scopeBerimpitDengan(Builder $query, string $mulai, string $selesai): Builder
    {
        return $query->where('tgl_izin', '<=', $selesai)
            ->whereRaw('COALESCE(tgl_selesai, tgl_izin) >= ?', [$mulai]);
    }
}
