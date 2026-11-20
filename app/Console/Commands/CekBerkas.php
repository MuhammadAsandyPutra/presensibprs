<?php

namespace App\Console\Commands;

use App\Models\Karyawan;
use App\Models\PengajuanIzin;
use App\Models\Presensi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Diagnosa foto/lampiran yang tidak tampil. Jalankan di server:
 *   php artisan presensi:cek-berkas
 *   php artisan presensi:cek-berkas --nik=1001
 * Salin seluruh keluarannya bila perlu bantuan.
 */
class CekBerkas extends Command
{
    protected $signature = 'presensi:cek-berkas {--nik= : Periksa satu karyawan saja}';

    protected $description = 'Diagnosa foto profil, foto presensi, dan lampiran yang tidak tampil';

    public function handle(): int
    {
        $namaDisk = config('presensi.foto.disk');
        $root = (string) config("filesystems.disks.{$namaDisk}.root");

        $this->info('== LINGKUNGAN');
        $this->line('APP_ENV      : '.config('app.env'));
        $this->line('APP_URL      : '.config('app.url').(str_starts_with((string) config('app.url'), 'https://') ? '' : '   <-- sebaiknya https://'));
        $this->line('Contoh URL   : '.route('berkas.profil', ['karyawan' => '1001']));
        $this->line('Disk foto    : '.$namaDisk.' -> '.$root);
        $this->line('Folder ada   : '.(is_dir($root) ? 'ya' : 'TIDAK ADA'));
        $this->line('Folder bisa ditulis : '.(is_writable($root) ? 'ya' : 'TIDAK'));
        $this->line('PHP          : '.PHP_VERSION.' | fileinfo='.(extension_loaded('fileinfo') ? 'ya' : 'TIDAK').' | gd='.(extension_loaded('gd') ? 'ya' : 'TIDAK'));

        $this->newLine();
        $this->info('== PANJANG KOLOM DATABASE (harus varchar(255))');
        foreach ([['karyawan', 'foto'], ['presensi', 'foto_in'], ['presensi', 'foto_out'], ['pengajuan_izin', 'lampiran']] as [$tabel, $kolom]) {
            try {
                $info = collect(Schema::getColumns($tabel))->firstWhere('name', $kolom);
                $tipe = $info['type'] ?? 'KOLOM TIDAK ADA';
                $this->line(sprintf('%-26s %s%s', "{$tabel}.{$kolom}", $tipe, preg_match('/\((\d+)\)/', $tipe, $m) && (int) $m[1] < 100 ? '   <-- TERLALU PENDEK' : ''));
            } catch (Throwable $e) {
                $this->warn("{$tabel}.{$kolom}: ".$e->getMessage());
            }
        }

        $this->newLine();
        $this->info('== ISI FOLDER (maks 5 terbaru per jenis)');
        foreach (['profil', 'presensi', 'lampiran'] as $folder) {
            try {
                $berkas = Storage::disk($namaDisk)->allFiles($folder);
                $this->line(sprintf('%-9s: %d berkas', $folder, count($berkas)));
                foreach (array_slice(array_reverse($berkas), 0, 5) as $b) {
                    $this->line('           '.$b);
                }
            } catch (Throwable $e) {
                $this->warn("{$folder}: ".$e->getMessage());
            }
        }

        $nik = $this->option('nik');

        $this->newLine();
        $this->info('== FOTO PROFIL (database vs disk)');
        $daftar = Karyawan::query()->whereNotNull('foto')->when($nik, fn ($q) => $q->where('nik', $nik))->limit(5)->get();
        foreach ($daftar as $k) {
            $this->periksa("karyawan {$k->nik}", $k->foto, 'uploads/karyawan');
        }
        if ($daftar->isEmpty()) {
            $this->line('Tidak ada karyawan dengan foto di database.');
        }

        $this->newLine();
        $this->info('== FOTO PRESENSI (5 terbaru)');
        $daftar = Presensi::query()->whereNotNull('foto_in')->when($nik, fn ($q) => $q->where('nik', $nik))->orderByDesc('id')->limit(5)->get();
        foreach ($daftar as $p) {
            $this->periksa("presensi #{$p->id} masuk", $p->foto_in, 'uploads/absensi');
        }
        if ($daftar->isEmpty()) {
            $this->line('Tidak ada foto presensi di database.');
        }

        $this->newLine();
        $this->info('== LAMPIRAN (5 terbaru)');
        $daftar = PengajuanIzin::query()->whereNotNull('lampiran')->when($nik, fn ($q) => $q->where('nik', $nik))->orderByDesc('id')->limit(5)->get();
        foreach ($daftar as $l) {
            $this->periksa("pengajuan #{$l->id}", $l->lampiran, null);
        }
        if ($daftar->isEmpty()) {
            $this->line('Tidak ada lampiran di database.');
        }

        $this->newLine();
        $this->line('Selesai. Bila ada baris "TIDAK DITEMUKAN", bandingkan nilai database dengan isi folder di atas.');

        return self::SUCCESS;
    }

    private function periksa(string $label, ?string $nilai, ?string $folderLama): void
    {
        $nilai = ltrim((string) $nilai, '/');
        $privat = config('presensi.foto.disk');
        $kandidat = [[$privat, $nilai], ['public', $nilai]];
        if ($folderLama) {
            $kandidat[] = ['public', $folderLama.'/'.$nilai];
            $kandidat[] = [$privat, $folderLama.'/'.$nilai];
        }

        foreach ($kandidat as [$disk, $path]) {
            if (Storage::disk($disk)->exists($path)) {
                $penuh = Storage::disk($disk)->path($path);
                $mime = function_exists('mime_content_type') ? @mime_content_type($penuh) : '?';
                $izin = substr(sprintf('%o', @fileperms($penuh)), -4);
                $this->line(sprintf('[OK]  %s -> %s:%s (%d byte, %s, izin %s)', $label, $disk, $path, @filesize($penuh), $mime, $izin));

                return;
            }
        }

        $this->error(sprintf('[TIDAK DITEMUKAN] %s | nilai di database: "%s" (%d karakter)', $label, $nilai, strlen($nilai)));
        foreach ($kandidat as [$disk, $path]) {
            $this->line("      dicoba: {$disk}:{$path}");
        }
    }
}
