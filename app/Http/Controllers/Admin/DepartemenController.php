<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DepartemenRequest;
use App\Models\Departemen;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartemenController extends Controller
{
    public function index(): View
    {
        return view('admin.departemen.index', ['daftar' => Departemen::query()->withCount('karyawan')->orderBy('kode_dept')->get()]);
    }

    public function create(): View
    {
        return view('admin.departemen.form', ['departemen' => new Departemen]);
    }

    public function store(DepartemenRequest $request): RedirectResponse
    {
        $d = Departemen::query()->create($request->validated());
        LogAktivitas::catat('departemen.tambah', "{$d->kode_dept} - {$d->nama_dept}");

        return redirect()->route('panel.departemen.index')->with('sukses', 'Departemen ditambahkan.');
    }

    public function edit(Departemen $departemen): View
    {
        return view('admin.departemen.form', ['departemen' => $departemen]);
    }

    public function update(DepartemenRequest $request, Departemen $departemen): RedirectResponse
    {
        $departemen->update($request->validated());
        LogAktivitas::catat('departemen.ubah', "{$departemen->kode_dept} - {$departemen->nama_dept}");

        return redirect()->route('panel.departemen.index')->with('sukses', 'Departemen diperbarui.');
    }

    public function destroy(Departemen $departemen): RedirectResponse
    {
        if ($departemen->karyawan()->exists()) {
            return back()->with('galat', 'Departemen masih dipakai karyawan; pindahkan karyawannya terlebih dahulu.');
        }
        $departemen->delete();
        LogAktivitas::catat('departemen.hapus', "{$departemen->kode_dept} - {$departemen->nama_dept}");

        return back()->with('sukses', 'Departemen dihapus.');
    }
}
