<?php

namespace App\Services;

use App\Enums\JenisPengajuan;
use App\Enums\StatusPengajuan;
use App\Exceptions\AturanBisnisException;
use App\Models\HariLibur;
use App\Models\Karyawan;
use App\Models\PengajuanIzin;
use App\Models\Pengaturan;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

/** Aturan pengajuan izin, sakit, dan cuti beserta alur persetujuannya. */
class PengajuanService
{
    public function __construct(private JadwalService $jadwal) {}

    /**
     * @param  array{jenis: int|string, tgl_izin: string, tgl_selesai?: string|null, keterangan: string}  $data
     *
     * @throws AturanBisnisException
     */
    public function ajukan(Karyawan $karyawan, array $data, ?UploadedFile $lampiran = null): PengajuanIzin
    {
        $jenis = JenisPengajuan::from((int) $data['jenis']);
        $mulai = Carbon::parse($data['tgl_izin'])->startOfDay();
        $selesai = ! empty($data['tgl_selesai']) ? Carbon::parse($data['tgl_selesai'])->startOfDay() : $mulai->copy();
        $hariIni = now()->startOfDay();

        if ($selesai->lt($mulai)) {
            throw new AturanBisnisException('Tanggal selesai tidak boleh sebelum tanggal mulai.');
        }

        if ($jenis === JenisPengajuan::Sakit) {
            $mundur = (int) Pengaturan::ambil('izin.sakit_mundur_hari');
            if ($mulai->lt($hariIni->copy()->subDays($mundur))) {
                throw new AturanBisnisException("Pengajuan sakit hanya dapat dibuat mundur maksimal {$mundur} hari.");
            }
        } elseif ($mulai->lt($hariIni)) {
            throw new AturanBisnisException('Tanggal pengajuan tidak boleh sebelum hari ini.');
        }

        $hari = $this->hitungHariKerja($karyawan, $mulai, $selesai);
        if ($hari < 1) {
            throw new AturanBisnisException('Rentang tanggal tersebut tidak mengandung hari kerja Anda.');
        }

        $bentrok = PengajuanIzin::query()->where('nik', $karyawan->nik)->berlaku()
            ->berimpitDengan($mulai->toDateString(), $selesai->toDateString())->exists();
        if ($bentrok) {
            throw new AturanBisnisException('Sudah ada pengajuan lain pada tanggal tersebut.');
        }

        if ($jenis->memakaiKuotaBulanan()) {
            $kuota = (int) Pengaturan::ambil('izin.kuota_bulanan');
            if ($this->jumlahPengajuanBulan($karyawan, $mulai) >= $kuota) {
                throw new AturanBisnisException("Kuota izin/sakit bulan tersebut sudah habis (maksimal {$kuota} kali).");
            }
        } else {
            if ($mulai->year !== $selesai->year) {
                throw new AturanBisnisException('Cuti lintas tahun harap diajukan terpisah per tahun.');
            }
            $sisa = $this->sisaCuti($karyawan, $mulai->year);
            if ($hari > $sisa) {
                throw new AturanBisnisException("Sisa cuti Anda {$sisa} hari, pengajuan ini {$hari} hari kerja.");
            }
        }

        return PengajuanIzin::query()->create([
            'nik' => $karyawan->nik,
            'tgl_izin' => $mulai->toDateString(),
            'tgl_selesai' => $selesai->toDateString(),
            'status' => $jenis->value,
            'keterangan' => $data['keterangan'],
            'status_approved' => StatusPengajuan::Menunggu->value,
            'jumlah_hari' => $hari,
            'lampiran' => $lampiran?->store('lampiran/'.$mulai->year, config('presensi.foto.disk')),
        ]);
    }

    /** Jumlah hari kerja KARYAWAN tsb dalam rentang (sesuai jadwal, tanpa hari libur). */
    public function hitungHariKerja(Karyawan $karyawan, CarbonInterface $mulai, CarbonInterface $selesai): int
    {
        $peta = $this->jadwal->petaUntuk($karyawan);
        $libur = HariLibur::query()->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->get()->map(fn ($h) => $h->tanggal->toDateString())->all();

        $jumlah = 0;
        for ($tgl = $mulai->copy(); $tgl->lte($selesai); $tgl->addDay()) {
            $namaHari = config('presensi.hari')[$tgl->dayOfWeekIso];
            if (isset($peta[$namaHari]) && ! in_array($tgl->toDateString(), $libur, true)) {
                $jumlah++;
            }
        }

        return $jumlah;
    }

    public function jumlahPengajuanBulan(Karyawan $karyawan, CarbonInterface $acuan): int
    {
        return PengajuanIzin::query()->where('nik', $karyawan->nik)->berlaku()
            ->whereIn('status', [JenisPengajuan::Izin->value, JenisPengajuan::Sakit->value])
            ->whereYear('tgl_izin', $acuan->year)->whereMonth('tgl_izin', $acuan->month)->count();
    }

    public function sisaKuotaBulan(Karyawan $karyawan, CarbonInterface $acuan): int
    {
        return max(0, (int) Pengaturan::ambil('izin.kuota_bulanan') - $this->jumlahPengajuanBulan($karyawan, $acuan));
    }

    /** Saldo cuti tahunan = jatah - cuti yang menunggu/disetujui pada tahun tsb. */
    public function sisaCuti(Karyawan $karyawan, int $tahun): int
    {
        $terpakai = (int) PengajuanIzin::query()->where('nik', $karyawan->nik)->berlaku()
            ->where('status', JenisPengajuan::Cuti->value)
            ->whereYear('tgl_izin', $tahun)->sum('jumlah_hari');

        return max(0, $karyawan->jatahCutiTahunan() - $terpakai);
    }

    /** Karyawan membatalkan pengajuannya sendiri (hanya selagi menunggu). */
    public function batal(Karyawan $karyawan, PengajuanIzin $pengajuan): void
    {
        if ($pengajuan->nik !== $karyawan->nik) {
            throw new AturanBisnisException('Pengajuan tidak ditemukan.');
        }
        if ($pengajuan->status_approved !== StatusPengajuan::Menunggu) {
            throw new AturanBisnisException('Hanya pengajuan yang masih menunggu yang dapat dibatalkan.');
        }
        $pengajuan->update(['status_approved' => StatusPengajuan::Dibatalkan->value]);
    }

    /** HR menyetujui/menolak pengajuan yang menunggu. */
    public function putuskan(PengajuanIzin $pengajuan, StatusPengajuan $keputusan, User $user, ?string $catatan = null): void
    {
        if (! in_array($keputusan, [StatusPengajuan::Disetujui, StatusPengajuan::Ditolak], true)) {
            throw new AturanBisnisException('Keputusan tidak valid.');
        }
        if ($pengajuan->status_approved !== StatusPengajuan::Menunggu) {
            throw new AturanBisnisException('Pengajuan ini sudah diputuskan. Kembalikan ke "Menunggu" bila ingin mengubahnya.');
        }

        $pengajuan->update([
            'status_approved' => $keputusan->value,
            'catatan_hr' => $catatan,
            'diputuskan_oleh' => $user->getKey(),
            'diputuskan_pada' => now(),
        ]);
    }

    /** HR membatalkan keputusan sebelumnya (kembali ke Menunggu). */
    public function kembalikanKeMenunggu(PengajuanIzin $pengajuan): void
    {
        if (! in_array($pengajuan->status_approved, [StatusPengajuan::Disetujui, StatusPengajuan::Ditolak], true)) {
            throw new AturanBisnisException('Hanya pengajuan yang sudah diputuskan yang dapat dikembalikan.');
        }

        $pengajuan->update([
            'status_approved' => StatusPengajuan::Menunggu->value,
            'catatan_hr' => null,
            'diputuskan_oleh' => null,
            'diputuskan_pada' => null,
        ]);
    }
}
