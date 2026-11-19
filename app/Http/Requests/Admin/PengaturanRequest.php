<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PengaturanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kpi_bobot_kehadiran' => ['required', 'numeric', 'between:0,100'],
            'kpi_bobot_ketepatan' => ['required', 'numeric', 'between:0,100'],
            'kpi_bobot_disiplin' => ['required', 'numeric', 'between:0,100'],
            'kpi_penalti_per_menit' => ['required', 'numeric', 'between:0,10'],
            'kpi_penalti_alpa' => ['required', 'numeric', 'between:0,100'],
            'kpi_penalti_pulang_cepat' => ['required', 'numeric', 'between:0,100'],
            'kpi_batas_sangat_baik' => ['required', 'numeric', 'between:0,100', 'gt:kpi_batas_baik'],
            'kpi_batas_baik' => ['required', 'numeric', 'between:0,100', 'gt:kpi_batas_cukup'],
            'kpi_batas_cukup' => ['required', 'numeric', 'between:0,100'],
            'izin_kuota_bulanan' => ['required', 'integer', 'between:0,31'],
            'izin_sakit_mundur_hari' => ['required', 'integer', 'between:0,60'],
            'cuti_jatah_tahunan' => ['required', 'integer', 'between:0,60'],
            'gps_akurasi_maks' => ['required', 'integer', 'between:10,1000'],
            'gps_review_akurasi' => ['required', 'integer', 'between:10,1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $total = (float) $this->input('kpi_bobot_kehadiran') + (float) $this->input('kpi_bobot_ketepatan') + (float) $this->input('kpi_bobot_disiplin');
            if ($total <= 0) {
                $v->errors()->add('kpi_bobot_kehadiran', 'Total bobot KPI harus lebih dari 0.');
            }
        });
    }
}
