<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\JamKerjaRequest;
use App\Models\JadwalKaryawan;
use App\Models\JamKerja;
use App\Models\LogAktivitas;
use App\Models\Presensi;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JamKerjaController extends Controller
{
    public function index(): View
    {
        return view('admin.jam-kerja.index', ['daftar' => JamKerja::query()->orderBy('kode_jam_kerja')->get()]);
    }

    public function create(): View
    {
        return view('admin.jam-kerja.form', ['jamKerja' => new JamKerja(['toleransi_menit' => 0])]);
    }

    public function store(JamKerjaRequest $request): RedirectResponse
    {
        $j = JamKerja::query()->create($request->validated());
        LogAktivitas::catat('jamkerja.tambah', "{$j->kode_jam_kerja} - {$j->nama_jam_kerja}");

        return redirect()->route('panel.jam-kerja.index')->with('sukses', 'Jam kerja ditambahkan.');
    }

    public function edit(JamKerja $jamKerja): View
    {
        return view('admin.jam-kerja.form', ['jamKerja' => $jamKerja]);
    }

    public function update(JamKerjaRequest $request, JamKerja $jamKerja): RedirectResponse
    {
        $jamKerja->update($request->validated());
        LogAktivitas::catat('jamkerja.ubah', "{$jamKerja->kode_jam_kerja} - {$jamKerja->nama_jam_kerja}");

        return redirect()->route('panel.jam-kerja.index')->with('sukses', 'Jam kerja diperbarui. Perubahan berlaku untuk perhitungan keterlambatan selanjutnya dan laporan.');
    }

    public function destroy(JamKerja $jamKerja): RedirectResponse
    {
        $dipakai = JadwalKaryawan::query()->where('kode_jam_kerja', $jamKerja->kode_jam_kerja)->exists()
            || Presensi::query()->where('kode_jam_kerja', $jamKerja->kode_jam_kerja)->exists();
        if ($dipakai) {
            return back()->with('galat', 'Jam kerja ini sudah dipakai pada jadwal/riwayat presensi sehingga tidak dapat dihapus.');
        }
        $jamKerja->delete();
        LogAktivitas::catat('jamkerja.hapus', "{$jamKerja->kode_jam_kerja} - {$jamKerja->nama_jam_kerja}");

        return back()->with('sukses', 'Jam kerja dihapus.');
    }
}
