<?php

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;

class PresensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lokasi' => ['required', 'string', 'max:60'],   // "lat,lng"
            'akurasi' => ['nullable', 'numeric', 'min:0'],  // meter
            'foto' => ['required', 'string'],               // data URL base64
        ];
    }

    public function messages(): array
    {
        return ['lokasi.required' => 'Lokasi belum terbaca. Aktifkan GPS lalu coba lagi.', 'foto.required' => 'Foto selfie belum diambil.'];
    }
}
