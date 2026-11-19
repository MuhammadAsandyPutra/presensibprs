<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HariLiburRequest;
use App\Models\HariLibur;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HariLiburController extends Controller
{
    public function index(Request $request): View
    {
        $tahun = (int) $request->input('tahun', now()->year);

        return view('admin.hari-libur.index', [
            'tahun' => $tahun,
            'daftar' => HariLibur::query()->whereYear('tanggal', $tahun)->orderBy('tanggal')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.hari-libur.form', ['hariLibur' => new HariLibur]);
    }

    public function store(HariLiburRequest $request): RedirectResponse
    {
        $h = HariLibur::query()->create($request->validated());
        LogAktivitas::catat('libur.tambah', "{$h->tanggal->toDateString()} - {$h->keterangan}");

        return redirect()->route('panel.hari-libur.index')->with('sukses', 'Hari libur ditambahkan.');
    }

    public function edit(HariLibur $hariLibur): View
    {
        return view('admin.hari-libur.form', ['hariLibur' => $hariLibur]);
    }

    public function update(HariLiburRequest $request, HariLibur $hariLibur): RedirectResponse
    {
        $hariLibur->update($request->validated());
        LogAktivitas::catat('libur.ubah', "{$hariLibur->tanggal->toDateString()} - {$hariLibur->keterangan}");

        return redirect()->route('panel.hari-libur.index')->with('sukses', 'Hari libur diperbarui.');
    }

    public function destroy(HariLibur $hariLibur): RedirectResponse
    {
        $hariLibur->delete();
        LogAktivitas::catat('libur.hapus', "{$hariLibur->tanggal->toDateString()} - {$hariLibur->keterangan}");

        return back()->with('sukses', 'Hari libur dihapus.');
    }
}
