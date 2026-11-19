<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\PengajuanIzin;
use App\Models\Presensi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Menyajikan berkas PRIVAT (foto wajah, lampiran). Berkas tidak pernah bisa dibuka lewat URL
 * langsung; hanya pemiliknya atau HR/Admin yang boleh melihatnya.
 */
class BerkasController extends Controller
{
    public function presensi(Presensi $presensi, string $jenis): StreamedResponse
    {
        $this->izinkan($presensi->nik);

        return $this->kirim($jenis === 'in' ? $presensi->foto_in : $presensi->foto_out);
    }

    public function profil(Karyawan $karyawan): StreamedResponse
    {
        $this->izinkan($karyawan->nik);

        return $this->kirim($karyawan->foto);
    }

    public function lampiran(PengajuanIzin $pengajuan): StreamedResponse
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

    private function kirim(?string $path): StreamedResponse
    {
        $disk = Storage::disk(config('presensi.foto.disk'));
        abort_if(! $path || ! $disk->exists($path), 404);

        return $disk->response($path, null, ['Cache-Control' => 'private, max-age=300']);
    }
}
