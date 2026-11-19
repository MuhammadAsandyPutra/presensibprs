<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HariLiburRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $edit = $this->route('hariLibur');

        return [
            'tanggal' => ['required', 'date', Rule::unique('hari_libur', 'tanggal')->ignore($edit?->getKey())],
            'keterangan' => ['required', 'string', 'max:100'],
        ];
    }
}
