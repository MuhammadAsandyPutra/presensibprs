<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JamKerjaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $edit = $this->route('jamKerja');
        $waktu = ['required', 'date_format:H:i'];

        return [
            'kode_jam_kerja' => $edit ? ['prohibited'] : ['required', 'string', 'size:4', 'regex:/^[A-Za-z0-9]+$/', Rule::unique('jam_kerja', 'kode_jam_kerja')],
            'nama_jam_kerja' => ['required', 'string', 'max:50'],
            'awal_jam_masuk' => $waktu,
            'jam_masuk' => [...$waktu, 'after_or_equal:awal_jam_masuk'],
            'akhir_jam_masuk' => [...$waktu, 'after_or_equal:jam_masuk'],
            'jam_pulang' => [...$waktu, 'after:jam_masuk'],
            'toleransi_menit' => ['required', 'integer', 'between:0,120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kode_jam_kerja' => 'kode jam kerja', 'nama_jam_kerja' => 'nama jam kerja', 'awal_jam_masuk' => 'awal jam masuk',
            'jam_masuk' => 'jam masuk', 'akhir_jam_masuk' => 'akhir jam masuk', 'jam_pulang' => 'jam pulang', 'toleransi_menit' => 'toleransi',
        ];
    }
}
