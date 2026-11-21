<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\Karyawan;
use App\Models\Pengaturan;
use App\Models\PengajuanIzin;
use App\Models\Presensi;
use App\Support\Geo;
use App\Support\Waktu;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Seluruh aturan presensi masuk/pulang ada di sini (bukan di controller).
 * SEMUA validasi dilakukan di server: lokasi, radius, jam, dan waktu memakai jam server.
 */
class PresensiService
{
    public function __construct(private JadwalService $jadwal) {}

    /**
     * Catat presensi. Otomatis menentukan masuk atau pulang.
     *
     * @return array{jenis: string, pesan: string, terlambat_menit: int}
     *
     * @throws AturanBisnisException bila ada aturan yang dilanggar
     */
    public function catat(Karyawan $karyawan, string $lokasi, ?float $akurasi, string $fotoDataUrl, ?string $ip = null): array
    {
        $sekarang = now();
        $tanggal = $sekarang->toDateString();
        $jam = $sekarang->format('H:i:s');

        $posisi = Geo::parse($lokasi)
            ?? throw new AturanBisnisException('Lokasi tidak terbaca. Aktifkan GPS lalu coba lagi.', 'lokasi');

        $cabang = $karyawan->cabang;
        $kantor = $cabang?->koordinat();
        if (! $cabang || ! $kantor) {
            throw new AturanBisnisException('Lokasi kantor Anda belum diatur. Hubungi HR.', 'kantor');
        }

        $maksAkurasi = (float) Pengaturan::ambil('gps.akurasi_maks');
        if ($akurasi !== null && $akurasi > $maksAkurasi) {
            throw new AturanBisnisException(
                'Akurasi GPS rendah (' . round($akurasi) . ' m). Pindah ke area terbuka lalu coba lagi.',
                'akurasi'
            );
        }

        $jarak = Geo::jarak($posisi[0], $posisi[1], $kantor[0], $kantor[1]);
        if ($jarak > (int) $cabang->radius_cabang) {
            throw new AturanBisnisException(
                "Anda berada {$jarak} m dari kantor, radius maksimal {$cabang->radius_cabang} m.",
                'radius'
            );
        }

        $this->pastikanTidakSedangIzin($karyawan, $tanggal);

        $jamKerja = $this->jadwal->untukTanggal($karyawan, $sekarang)
            ?? throw new AturanBisnisException('Hari ini bukan hari kerja Anda.', 'jadwal');

        $presensi = Presensi::query()->where('nik', $karyawan->nik)->where('tanggal_presensi', $tanggal)->first();

        if ($presensi === null) {
            if (Waktu::detik($jam) < Waktu::detik($jamKerja->awal_jam_masuk)) {
                throw new AturanBisnisException('Belum waktunya presensi masuk (mulai ' . Waktu::hm($jamKerja->awal_jam_masuk) . ').', 'waktu');
            }
            if (Waktu::detik($jam) > Waktu::detik($jamKerja->akhir_jam_masuk)) {
                throw new AturanBisnisException('Waktu presensi masuk sudah ditutup (batas ' . Waktu::hm($jamKerja->akhir_jam_masuk) . '). Hubungi HR.', 'waktu');
            }

            $foto = $this->simpanFoto($karyawan, $tanggal, 'in', $fotoDataUrl);

            try {
                Presensi::query()->create([
                    'nik' => $karyawan->nik,
                    'tanggal_presensi' => $tanggal,
                    'jam_in' => $jam,
                    'foto_in' => $foto,
                    'lokasi_in' => $lokasi,
                    'kode_jam_kerja' => $jamKerja->kode_jam_kerja,
                    'jarak_in' => $jarak,
                    'akurasi_in' => $akurasi !== null ? (int) round($akurasi) : null,
                    'perlu_review' => $this->mencurigakan($karyawan, $lokasi, $akurasi),
                    'ip_address' => $ip,
                ]);
            } catch (UniqueConstraintViolationException) {
                Storage::disk(config('presensi.foto.disk'))->delete($foto);
                throw new AturanBisnisException('Presensi masuk hari ini sudah tercatat.', 'selesai');
            } catch (Throwable $e) {
                Storage::disk(config('presensi.foto.disk'))->delete($foto);
                throw $e;
            }

            $menit = $jamKerja->menitTerlambat($jam);

            return [
                'jenis' => 'masuk',
                'pesan' => $menit > 0 ? "Presensi masuk tercatat. Anda terlambat {$menit} menit." : 'Terima kasih, selamat bekerja!',
                'terlambat_menit' => $menit,
            ];
        }

        if ($presensi->jam_out === null) {
            if (Waktu::detik($jam) < Waktu::detik($jamKerja->jam_pulang)) {
                throw new AturanBisnisException('Belum waktunya pulang (jam pulang ' . Waktu::hm($jamKerja->jam_pulang) . ').', 'waktu');
            }

            $foto = $this->simpanFoto($karyawan, $tanggal, 'out', $fotoDataUrl);
            $presensi->update([
                'jam_out' => $jam,
                'foto_out' => $foto,
                'lokasi_out' => $lokasi,
                'jarak_out' => $jarak,
            ]);

            return ['jenis' => 'pulang', 'pesan' => 'Presensi pulang tercatat. Hati-hati di jalan!', 'terlambat_menit' => 0];
        }

        throw new AturanBisnisException('Anda sudah menyelesaikan presensi masuk dan pulang hari ini.', 'selesai');
    }

    private function pastikanTidakSedangIzin(Karyawan $karyawan, string $tanggal): void
    {
        $pengajuan = PengajuanIzin::query()
            ->where('nik', $karyawan->nik)
            ->where('status_approved', \App\Enums\StatusPengajuan::Disetujui->value)
            ->berimpitDengan($tanggal, $tanggal)
            ->first();

        if ($pengajuan) {
            throw new AturanBisnisException(
                'Anda memiliki ' . strtolower($pengajuan->jenis()->label()) . ' yang disetujui pada hari ini, presensi tidak diperlukan.',
                'izin'
            );
        }
    }

    /**
     * Tandai untuk direview HR (BUKAN ditolak): akurasi GPS rendah, atau koordinat
     * persis sama dengan 3 presensi terakhir (indikasi lokasi palsu/tetap).
     */
    private function mencurigakan(Karyawan $karyawan, string $lokasi, ?float $akurasi): bool
    {
        if ($akurasi !== null && $akurasi > (float) Pengaturan::ambil('gps.review_akurasi')) {
            return true;
        }

        $terakhir = Presensi::query()->where('nik', $karyawan->nik)
            ->orderByDesc('tanggal_presensi')->limit(3)->pluck('lokasi_in');

        return $terakhir->count() === 3 && $terakhir->every(fn($l) => $l === $lokasi);
    }

    /** Validasi & simpan foto (data URL base64) ke disk PRIVAT; mengembalikan path relatif. */
    private function simpanFoto(Karyawan $karyawan, string $tanggal, string $jenis, string $dataUrl): string
    {
        if (! preg_match('#^data:image/(png|jpeg|jpg);base64,#', $dataUrl, $cocok)) {
            throw new AturanBisnisException('Foto tidak valid. Ambil ulang foto selfie.', 'foto');
        }

        $biner = base64_decode(substr($dataUrl, strlen($cocok[0])), true);
        if ($biner === false || strlen($biner) < 1000) {
            throw new AturanBisnisException('Foto tidak valid. Ambil ulang foto selfie.', 'foto');
        }
        if (strlen($biner) > config('presensi.foto.maks_kb') * 1024) {
            throw new AturanBisnisException('Ukuran foto terlalu besar. Ambil ulang foto.', 'foto');
        }
        if (@getimagesizefromstring($biner) === false) {
            throw new AturanBisnisException('Berkas foto rusak. Ambil ulang foto selfie.', 'foto');
        }

        $ekstensi = $cocok[1] === 'png' ? 'png' : 'jpg';
        $nik = preg_replace('/[^A-Za-z0-9_-]/', '_', $karyawan->nik);
        $path = 'presensi/' . substr($tanggal, 0, 4) . '/' . substr($tanggal, 5, 2)
            . '/' . $nik . '_' . str_replace('-', '', $tanggal) . '_' . $jenis . '.' . $ekstensi;

        if (! Storage::disk(config('presensi.foto.disk'))->put($path, $biner)) {
            Log::error('Gagal menyimpan foto presensi', ['path' => $path]);
            throw new AturanBisnisException('Foto gagal disimpan di server. Hubungi admin.', 'foto');
        }

        return $path;
    }
}
