<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CabangRequest;
use App\Models\Cabang;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CabangController extends Controller
{
    public function index(): View
    {
        return view('admin.cabang.index', ['daftar' => Cabang::query()->withCount('karyawan')->orderBy('kode_cabang')->get()]);
    }

    public function create(): View
    {
        return view('admin.cabang.form', ['cabang' => new Cabang(['radius_cabang' => 100])]);
    }

    public function store(CabangRequest $request): RedirectResponse
    {
        $c = Cabang::query()->create($request->validated());
        LogAktivitas::catat('cabang.tambah', "{$c->kode_cabang} - {$c->nama_cabang}");

        return redirect()->route('panel.cabang.index')->with('sukses', 'Cabang ditambahkan.');
    }

    public function edit(Cabang $cabang): View
    {
        return view('admin.cabang.form', ['cabang' => $cabang]);
    }

    public function update(CabangRequest $request, Cabang $cabang): RedirectResponse
    {
        $cabang->update($request->validated());
        LogAktivitas::catat('cabang.ubah', "{$cabang->kode_cabang} - lokasi/radius diperbarui");

        return redirect()->route('panel.cabang.index')->with('sukses', 'Cabang diperbarui.');
    }

    public function destroy(Cabang $cabang): RedirectResponse
    {
        if ($cabang->karyawan()->exists()) {
            return back()->with('galat', 'Cabang masih dipakai karyawan; pindahkan karyawannya terlebih dahulu.');
        }
        $cabang->delete();
        LogAktivitas::catat('cabang.hapus', "{$cabang->kode_cabang} - {$cabang->nama_cabang}");

        return back()->with('sukses', 'Cabang dihapus.');
    }
}
