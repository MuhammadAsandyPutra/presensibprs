<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Dipakai untuk tambah (store) maupun ubah (update) karyawan. */
class KaryawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses dibatasi di route (auth:user + role)
    }

    public function rules(): array
    {
        $edit = $this->route('karyawan');

        return [
            'nik' => $edit ? ['prohibited'] : ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('karyawan', 'nik')],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:60'],
            'no_hp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'kode_dept' => ['nullable', Rule::exists('departemen', 'kode_dept')],
            'kode_cabang' => ['required', Rule::exists('cabang', 'kode_cabang')],
            'jatah_cuti' => ['nullable', 'integer', 'between:0,60'],
            'aktif' => ['nullable', 'boolean'],
            'password' => [$edit ? 'nullable' : 'required', 'string', 'min:8'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return ['nik' => 'NIK', 'kode_dept' => 'departemen', 'kode_cabang' => 'cabang', 'jatah_cuti' => 'jatah cuti', 'password' => 'kata sandi'];
    }
}
