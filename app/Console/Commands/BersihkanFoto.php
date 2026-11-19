<?php

namespace App\Console\Commands;

use App\Models\Presensi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/** Kebijakan retensi data pribadi (foto wajah): hapus foto lebih tua dari N bulan. */
class BersihkanFoto extends Command
{
    protected $signature = 'presensi:bersihkan-foto {--bulan= : Override masa retensi (bulan)}';

    protected $description = 'Hapus foto presensi yang melewati masa retensi';

    public function handle(): int
    {
        $bulan = (int) ($this->option('bulan') ?: config('presensi.foto.retensi_bulan'));
        $batas = now()->subMonths($bulan)->toDateString();
        $disk = Storage::disk(config('presensi.foto.disk'));
        $jumlah = 0;

        Presensi::query()->where('tanggal_presensi', '<', $batas)
            ->where(fn ($q) => $q->whereNotNull('foto_in')->orWhereNotNull('foto_out'))
            ->chunkById(200, function ($daftar) use ($disk, &$jumlah) {
                foreach ($daftar as $p) {
                    foreach (['foto_in', 'foto_out'] as $kolom) {
                        if ($p->{$kolom}) {
                            $disk->delete($p->{$kolom});
                            $jumlah++;
                        }
                    }
                    $p->update(['foto_in' => null, 'foto_out' => null]);
                }
            });

        $this->info("{$jumlah} foto sebelum {$batas} dihapus.");

        return self::SUCCESS;
    }
}
