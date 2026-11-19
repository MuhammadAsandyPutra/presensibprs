<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Karyawan\ProfilRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function edit(): View
    {
        return view('karyawan.profil', ['karyawan' => Auth::guard('karyawan')->user()->load('departemen', 'cabang')]);
    }

    public function update(ProfilRequest $request): RedirectResponse
    {
        $karyawan = Auth::guard('karyawan')->user();
        $data = $request->safe()->only(['nama_lengkap', 'no_hp']);

        if ($request->filled('password')) {
            $data['password'] = $request->input('password'); // di-hash otomatis oleh cast model
        }

        if ($request->hasFile('foto')) {
            $disk = Storage::disk(config('presensi.foto.disk'));
            if ($karyawan->foto) {
                $disk->delete($karyawan->foto);
            }
            $data['foto'] = $request->file('foto')->store('profil', config('presensi.foto.disk'));
        }

        $karyawan->update($data);

        return back()->with('sukses', 'Profil berhasil diperbarui.');
    }
}
