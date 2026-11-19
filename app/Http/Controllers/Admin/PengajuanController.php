<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JenisPengajuan;
use App\Enums\StatusPengajuan;
use App\Exceptions\AturanBisnisException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KeputusanRequest;
use App\Models\LogAktivitas;
use App\Models\PengajuanIzin;
use App\Services\PengajuanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PengajuanController extends Controller
{
    public function __construct(private PengajuanService $service) {}

    public function index(Request $request): View
    {
        $f = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'jenis' => ['nullable', 'integer', 'in:1,2,3'],
            'status' => ['nullable', 'integer', 'in:0,1,2,3'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        $daftar = PengajuanIzin::query()->with('karyawan.departemen')
            ->when($f['cari'] ?? null, fn ($q, $c) => $q->whereHas('karyawan', fn ($k) => $k->where('nama_lengkap', 'like', "%{$c}%")->orWhere('nik', 'like', "%{$c}%")))
            ->when(isset($f['jenis']), fn ($q) => $q->where('status', $f['jenis']))
            ->when(isset($f['status']), fn ($q) => $q->where('status_approved', $f['status']))
            ->when($f['dari'] ?? null, fn ($q, $d) => $q->where('tgl_izin', '>=', $d))
            ->when($f['sampai'] ?? null, fn ($q, $s) => $q->where('tgl_izin', '<=', $s))
            // Yang menunggu tampil paling atas.
            ->orderByRaw('CASE WHEN status_approved = 0 THEN 0 ELSE 1 END')
            ->latest('tgl_izin')->latest('id')
            ->paginate(15)->withQueryString();

        return view('admin.pengajuan.index', [
            'daftar' => $daftar,
            'filter' => $f,
            'jenisList' => JenisPengajuan::cases(),
            'statusList' => StatusPengajuan::cases(),
        ]);
    }

    public function putuskan(KeputusanRequest $request, PengajuanIzin $pengajuan): RedirectResponse
    {
        $setuju = $request->input('keputusan') === 'setuju';

        try {
            $this->service->putuskan(
                $pengajuan,
                $setuju ? StatusPengajuan::Disetujui : StatusPengajuan::Ditolak,
                Auth::guard('user')->user(),
                $request->input('catatan'),
            );
        } catch (AturanBisnisException $e) {
            return back()->with('galat', $e->getMessage());
        }

        LogAktivitas::catat($setuju ? 'pengajuan.setuju' : 'pengajuan.tolak', "{$pengajuan->jenis()->label()} {$pengajuan->nik} #{$pengajuan->id}");

        return back()->with('sukses', 'Pengajuan '.($setuju ? 'disetujui.' : 'ditolak.'));
    }

    public function kembalikan(PengajuanIzin $pengajuan): RedirectResponse
    {
        try {
            $this->service->kembalikanKeMenunggu($pengajuan);
        } catch (AturanBisnisException $e) {
            return back()->with('galat', $e->getMessage());
        }

        LogAktivitas::catat('pengajuan.kembalikan', "Keputusan #{$pengajuan->id} ({$pengajuan->nik}) dikembalikan ke Menunggu");

        return back()->with('sukses', 'Keputusan dibatalkan; pengajuan kembali menunggu.');
    }
}
