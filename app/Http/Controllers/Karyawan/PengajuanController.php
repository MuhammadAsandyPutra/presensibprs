<?php

namespace App\Http\Controllers\Karyawan;

use App\Enums\JenisPengajuan;
use App\Exceptions\AturanBisnisException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Karyawan\PengajuanRequest;
use App\Models\PengajuanIzin;
use App\Services\PengajuanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PengajuanController extends Controller
{
    public function __construct(private PengajuanService $service) {}

    public function index(): View
    {
        $karyawan = Auth::guard('karyawan')->user();

        return view('karyawan.pengajuan.index', [
            'daftar' => PengajuanIzin::query()->where('nik', $karyawan->nik)->latest('tgl_izin')->latest('id')->paginate(10),
        ]);
    }

    public function create(): View
    {
        $karyawan = Auth::guard('karyawan')->user();

        return view('karyawan.pengajuan.create', [
            'jenis' => JenisPengajuan::cases(),
            'sisaCuti' => $this->service->sisaCuti($karyawan, now()->year),
            'sisaKuota' => $this->service->sisaKuotaBulan($karyawan, now()),
        ]);
    }

    public function store(PengajuanRequest $request): RedirectResponse
    {
        try {
            $this->service->ajukan(Auth::guard('karyawan')->user(), $request->validated(), $request->file('lampiran'));
        } catch (AturanBisnisException $e) {
            return back()->withInput()->with('galat', $e->getMessage());
        }

        return redirect()->route('izin.index')->with('sukses', 'Pengajuan terkirim dan menunggu persetujuan HR.');
    }

    public function batal(PengajuanIzin $pengajuan): RedirectResponse
    {
        try {
            $this->service->batal(Auth::guard('karyawan')->user(), $pengajuan);
        } catch (AturanBisnisException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return back()->with('sukses', 'Pengajuan dibatalkan.');
    }
}
