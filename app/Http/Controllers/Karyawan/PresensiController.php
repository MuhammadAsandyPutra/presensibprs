<?php

namespace App\Http\Controllers\Karyawan;

use App\Exceptions\AturanBisnisException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Karyawan\PresensiRequest;
use App\Models\Pengaturan;
use App\Models\Presensi;
use App\Services\JadwalService;
use App\Services\PresensiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PresensiController extends Controller
{
    /** Halaman kamera + peta. Semua keputusan tetap divalidasi ulang di server saat store(). */
    public function create(JadwalService $jadwal): View
    {
        $karyawan = Auth::guard('karyawan')->user()->load('cabang');

        return view('karyawan.presensi.create', [
            'karyawan' => $karyawan,
            'cabang' => $karyawan->cabang,
            'jamKerja' => $jadwal->untukTanggal($karyawan, now()),
            'presensi' => Presensi::query()->where('nik', $karyawan->nik)->where('tanggal_presensi', now()->toDateString())->first(),
            'akurasiMaks' => (int) Pengaturan::ambil('gps.akurasi_maks'),
        ]);
    }

    public function store(PresensiRequest $request, PresensiService $service): JsonResponse
    {
        try {
            $hasil = $service->catat(
                Auth::guard('karyawan')->user(),
                $request->string('lokasi')->toString(),
                $request->filled('akurasi') ? (float) $request->input('akurasi') : null,
                $request->string('foto')->toString(),
                $request->ip(),
            );
        } catch (AturanBisnisException $e) {
            return response()->json(['ok' => false, 'pesan' => $e->getMessage(), 'kode' => $e->kode], 422);
        }

        return response()->json(['ok' => true, ...$hasil]);
    }
}
