<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JamKerja;
use App\Models\Karyawan;
use App\Models\LogAktivitas;
use App\Services\JadwalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Jam kerja per hari untuk satu karyawan (7 pilihan, kosong = libur). */
class JadwalController extends Controller
{
    public function edit(Karyawan $karyawan, JadwalService $jadwal): View
    {
        return view('admin.karyawan.jadwal', [
            'karyawan' => $karyawan,
            'peta' => $jadwal->petaUntuk($karyawan),
            'jamKerjaList' => JamKerja::query()->orderBy('kode_jam_kerja')->get(),
            'adaJadwal' => $karyawan->jadwal()->exists(),
        ]);
    }

    public function update(Request $request, Karyawan $karyawan, JadwalService $jadwal): RedirectResponse
    {
        $data = $request->validate([
            'hari' => ['required', 'array'],
            'hari.*' => ['nullable', Rule::exists('jam_kerja', 'kode_jam_kerja')],
        ]);

        // Hanya nama hari yang dikenal yang diterima.
        $perHari = array_intersect_key($data['hari'], array_flip(config('presensi.hari')));
        if (count(array_filter($perHari)) === 0) {
            return back()->with('galat', 'Pilih minimal satu hari kerja.');
        }

        $jadwal->simpan($karyawan, $perHari);
        LogAktivitas::catat('jadwal.ubah', "Jadwal {$karyawan->nik} - {$karyawan->nama_lengkap}");

        return redirect()->route('panel.karyawan.index')->with('sukses', 'Jadwal kerja disimpan.');
    }
}
