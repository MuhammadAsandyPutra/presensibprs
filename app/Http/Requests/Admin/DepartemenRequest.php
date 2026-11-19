<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartemenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $edit = $this->route('departemen');

        return [
            'kode_dept' => $edit ? ['prohibited'] : ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('departemen', 'kode_dept')],
            'nama_dept' => ['required', 'string', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return ['kode_dept' => 'kode departemen', 'nama_dept' => 'nama departemen'];
    }
}
