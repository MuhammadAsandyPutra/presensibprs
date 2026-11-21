<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\PengajuanIzin;
use App\Models\Presensi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Menyajikan berkas PRIVAT (foto wajah, lampiran). Berkas tidak pernah bisa dibuka lewat URL
 * langsung; hanya pemiliknya atau HR/Admin yang boleh melihatnya.
 */
class BerkasController extends Controller
{
    private const TIPE = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'pdf' => 'application/pdf',
    ];

    public function presensi(Presensi $presensi, string $jenis): BinaryFileResponse
    {
        $this->izinkan($presensi->nik);

        return $this->kirim($jenis === 'in' ? $presensi->foto_in : $presensi->foto_out, 'uploads/absensi');
    }

    public function profil(Karyawan $karyawan): BinaryFileResponse
    {
        $this->izinkan($karyawan->nik);

        return $this->kirim($karyawan->foto, 'uploads/karyawan');
    }

    public function lampiran(PengajuanIzin $pengajuan): BinaryFileResponse
    {
        $this->izinkan($pengajuan->nik);

        return $this->kirim($pengajuan->lampiran);
    }

    /** HR/Admin boleh semua; karyawan hanya miliknya sendiri. */
    private function izinkan(string $nikPemilik): void
    {
        $pemilikSendiri = Auth::guard('karyawan')->check() && (string) Auth::guard('karyawan')->id() === (string) $nikPemilik;

        abort_unless(Auth::guard('user')->check() || $pemilikSendiri, 403);
    }

    /**
     * Cari berkas di beberapa lokasi agar data LAMA tetap tampil:
     *  1. disk privat : storage/app/private/<nilai kolom>
     *  2. disk public : storage/app/public/<nilai kolom>
     *  3. folder lama : storage/app/public/uploads/karyawan|absensi/<nama file>
     *
     * Berkas dikirim lewat BinaryFileResponse (bukan stream Flysystem): lebih andal di shared
     * hosting (LiteSpeed/Apache), mendukung ETag/304 & Range, dan tidak bergantung ekstensi fileinfo.
     * Kegagalan dicatat di storage/logs/laravel.log.
     */
    private function kirim(?string $nilai, ?string $folderLama = null): BinaryFileResponse
    {
        $nilai = ltrim(str_replace('\\', '/', (string) $nilai), '/');
        abort_if($nilai === '' || str_contains($nilai, '..'), 404);

        $privat = config('presensi.foto.disk');
        $kandidat = [[$privat, $nilai], ['public', $nilai]];
        if ($folderLama) {
            $kandidat[] = ['public', $folderLama . '/' . $nilai];
            $kandidat[] = [$privat, $folderLama . '/' . $nilai];
        }

        foreach ($kandidat as [$namaDisk, $path]) {
            $disk = Storage::disk($namaDisk);
            if (! $disk->exists($path)) {
                continue;
            }

            $penuh = $disk->path($path);
            if (! is_file($penuh) || ! is_readable($penuh)) {
                Log::error('Berkas ada tetapi tidak bisa dibaca (periksa izin folder)', ['path' => $penuh]);
                continue;
            }

            $tipe = self::TIPE[strtolower(pathinfo($path, PATHINFO_EXTENSION))] ?? 'application/octet-stream';

            // Bersihkan buffer agar tidak ada spasi/warning PHP yang ikut merusak biner gambar.
            while (! app()->runningUnitTests() && ob_get_level() > 0) {
                @ob_end_clean();
            }

            $respons = response()->file($penuh, [
                'Content-Type' => $tipe,
                'Cache-Control' => 'private, max-age=300',
                'X-Content-Type-Options' => 'nosniff',
            ]);
            $respons->setAutoEtag();

            return $respons;
        }

        Log::warning('Berkas tidak ditemukan', ['nilai_database' => $nilai, 'dicoba' => $kandidat]);
        abort(404);
    }
}
