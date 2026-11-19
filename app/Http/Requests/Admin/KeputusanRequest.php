<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class KeputusanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keputusan' => ['required', 'in:setuju,tolak'],
            'catatan' => ['nullable', 'string', 'max:255', 'required_if:keputusan,tolak'],
        ];
    }

    public function messages(): array
    {
        return ['catatan.required_if' => 'Alasan penolakan wajib diisi.'];
    }
}
