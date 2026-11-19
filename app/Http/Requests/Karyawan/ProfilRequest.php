<?php

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;

class ProfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed', 'required_with:password_lama'],
            'password_lama' => ['nullable', 'required_with:password', 'string', 'current_password:karyawan'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return ['password_lama' => 'kata sandi lama', 'password' => 'kata sandi baru'];
    }
}
