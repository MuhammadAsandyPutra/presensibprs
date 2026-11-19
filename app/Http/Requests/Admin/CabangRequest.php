<?php

namespace App\Http\Requests\Admin;

use App\Support\Geo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CabangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $edit = $this->route('cabang');

        return [
            'kode_cabang' => $edit ? ['prohibited'] : ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('cabang', 'kode_cabang')],
            'nama_cabang' => ['required', 'string', 'max:100'],
            'lokasi_cabang' => ['required', 'string', 'max:60', function ($atribut, $nilai, $gagal) {
                if (Geo::parse($nilai) === null) {
                    $gagal('Format lokasi harus "latitude,longitude", contoh: -3.388000,114.635000');
                }
            }],
            'radius_cabang' => ['required', 'integer', 'between:20,2000'],
        ];
    }

    public function attributes(): array
    {
        return ['kode_cabang' => 'kode cabang', 'nama_cabang' => 'nama cabang', 'lokasi_cabang' => 'koordinat', 'radius_cabang' => 'radius'];
    }
}
