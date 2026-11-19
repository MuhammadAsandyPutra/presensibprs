<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PengaturanRequest;
use App\Models\LogAktivitas;
use App\Models\Pengaturan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Pengaturan yang boleh diubah HR tanpa menyentuh kode: bobot KPI, kuota izin, jatah cuti, GPS. */
class PengaturanController extends Controller
{
    /** nama field form => kunci pengaturan */
    private const FIELD = [
        'kpi_bobot_kehadiran', 'kpi_bobot_ketepatan', 'kpi_bobot_disiplin',
        'kpi_penalti_per_menit', 'kpi_penalti_alpa', 'kpi_penalti_pulang_cepat',
        'kpi_batas_sangat_baik', 'kpi_batas_baik', 'kpi_batas_cukup',
        'izin_kuota_bulanan', 'izin_sakit_mundur_hari', 'cuti_jatah_tahunan',
        'gps_akurasi_maks', 'gps_review_akurasi',
    ];

    public function edit(): View
    {
        $nilai = [];
        foreach (self::FIELD as $field) {
            $nilai[$field] = Pengaturan::ambil($this->kunci($field));
        }

        return view('admin.kpi.pengaturan', ['nilai' => $nilai]);
    }

    public function update(PengaturanRequest $request): RedirectResponse
    {
        foreach (self::FIELD as $field) {
            Pengaturan::simpan($this->kunci($field), $request->input($field));
        }
        LogAktivitas::catat('pengaturan.ubah', 'Pengaturan KPI/kebijakan diperbarui');

        return back()->with('sukses', 'Pengaturan disimpan. KPI dihitung ulang otomatis memakai nilai baru.');
    }

    /** 'kpi_bobot_kehadiran' => 'kpi.bobot_kehadiran' (underscore pertama menjadi titik) */
    private function kunci(string $field): string
    {
        return preg_replace('/_/', '.', $field, 1);
    }
}
