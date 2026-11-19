<?php

namespace App\Http\Requests\Karyawan;

use App\Enums\JenisPengajuan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis' => ['required', Rule::in(array_map(fn ($j) => $j->value, JenisPengajuan::cases()))],
            'tgl_izin' => ['required', 'date'],
            'tgl_selesai' => ['nullable', 'date', 'after_or_equal:tgl_izin'],
            'keterangan' => ['required', 'string', 'min:5', 'max:500'],
            'lampiran' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:'.config('presensi.izin.lampiran_maks_kb')],
        ];
    }

    public function attributes(): array
    {
        return ['tgl_izin' => 'tanggal mulai', 'tgl_selesai' => 'tanggal selesai'];
    }
}
