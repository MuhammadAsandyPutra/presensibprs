<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KaryawanRequest;
use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\Karyawan;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KaryawanController extends Controller
{
    public function index(Request $request): View
    {
        $f = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'kode_dept' => ['nullable', 'string', 'max:10'],
            'kode_cabang' => ['nullable', 'string', 'max:10'],
            'status' => ['nullable', 'in:aktif,nonaktif'],
        ]);

        $daftar = Karyawan::query()->with('departemen', 'cabang')
            ->when($f['cari'] ?? null, fn ($q, $c) => $q->where(fn ($w) => $w->where('nama_lengkap', 'like', "%{$c}%")->orWhere('nik', 'like', "%{$c}%")))
            ->when($f['kode_dept'] ?? null, fn ($q, $d) => $q->where('kode_dept', $d))
            ->when($f['kode_cabang'] ?? null, fn ($q, $c) => $q->where('kode_cabang', $c))
            ->when($f['status'] ?? null, fn ($q, $s) => $q->where('aktif', $s === 'aktif'))
            ->orderBy('nama_lengkap')->paginate(15)->withQueryString();

        return view('admin.karyawan.index', [
            'daftar' => $daftar,
            'filter' => $f,
            'departemenList' => Departemen::query()->orderBy('nama_dept')->get(),
            'cabangList' => Cabang::query()->orderBy('nama_cabang')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.karyawan.form', $this->opsiForm(new Karyawan(['aktif' => true])));
    }

    public function store(KaryawanRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('foto');
        $data['aktif'] = $request->boolean('aktif', true);
        $data['jatah_cuti'] = $request->input('jatah_cuti') ?: config('presensi.cuti.jatah_tahunan');

        $karyawan = Karyawan::query()->create($data);
        $this->simpanFoto($request, $karyawan);
        LogAktivitas::catat('karyawan.tambah', "{$karyawan->nik} - {$karyawan->nama_lengkap}");

        return redirect()->route('panel.karyawan.index')->with('sukses', 'Karyawan ditambahkan. Atur jadwal kerjanya bila berbeda dari Senin-Jumat.');
    }

    public function edit(Karyawan $karyawan): View
    {
        return view('admin.karyawan.form', $this->opsiForm($karyawan));
    }

    public function update(KaryawanRequest $request, Karyawan $karyawan): RedirectResponse
    {
        $data = $request->safe()->except(['foto', 'password']);
        $data['aktif'] = $request->boolean('aktif');
        $data['jatah_cuti'] = $request->input('jatah_cuti') ?: config('presensi.cuti.jatah_tahunan');
        if ($request->filled('password')) {
            $data['password'] = $request->input('password');
        }

        $karyawan->update($data);
        $this->simpanFoto($request, $karyawan);
        LogAktivitas::catat('karyawan.ubah', "{$karyawan->nik} - {$karyawan->nama_lengkap}");

        return redirect()->route('panel.karyawan.index')->with('sukses', 'Data karyawan diperbarui.');
    }

    /** Data karyawan tidak dihapus (riwayat presensi harus utuh); cukup dinonaktifkan. */
    public function destroy(Karyawan $karyawan): RedirectResponse
    {
        $karyawan->update(['aktif' => false]);
        LogAktivitas::catat('karyawan.nonaktif', "{$karyawan->nik} - {$karyawan->nama_lengkap}");

        return back()->with('sukses', "{$karyawan->nama_lengkap} dinonaktifkan. Riwayat presensinya tetap tersimpan.");
    }

    public function resetPassword(Karyawan $karyawan): RedirectResponse
    {
        $baru = Str::password(10, symbols: false);
        $karyawan->update(['password' => $baru]);
        LogAktivitas::catat('karyawan.reset-password', "{$karyawan->nik} - {$karyawan->nama_lengkap}");

        return back()->with('sukses', "Kata sandi {$karyawan->nama_lengkap} direset menjadi: {$baru} (catat dan sampaikan ke karyawan).");
    }

    private function opsiForm(Karyawan $karyawan): array
    {
        return [
            'karyawan' => $karyawan,
            'departemenList' => Departemen::query()->orderBy('nama_dept')->get(),
            'cabangList' => Cabang::query()->orderBy('nama_cabang')->get(),
        ];
    }

    private function simpanFoto(KaryawanRequest $request, Karyawan $karyawan): void
    {
        if (! $request->hasFile('foto')) {
            return;
        }
        $disk = config('presensi.foto.disk');
        if ($karyawan->foto) {
            Storage::disk($disk)->delete($karyawan->foto);
        }
        $karyawan->update(['foto' => $request->file('foto')->store('profil', $disk)]);
    }
}
