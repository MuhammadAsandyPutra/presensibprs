<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\KaryawanLoginController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\Karyawan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Peta URL
|--------------------------------------------------------------------------
|  /          -> portal KARYAWAN (guard `karyawan`, login NIK)
|  /panel     -> portal HR/ADMIN (guard `user`, login email)
| Setiap fitur memiliki controller sendiri di namespace portalnya:
|  App\Http\Controllers\Karyawan\*  dan  App\Http\Controllers\Admin\*
*/

// ---------------------------------------------------------------- Portal karyawan
Route::middleware('guest:karyawan')->group(function () {
    Route::get('/', [KaryawanLoginController::class, 'form'])->name('login');
    Route::post('/login', [KaryawanLoginController::class, 'login'])->middleware('throttle:login')->name('login.proses');
});

Route::middleware('auth:karyawan')->group(function () {
    Route::post('/logout', [KaryawanLoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [Karyawan\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/presensi/create', [Karyawan\PresensiController::class, 'create'])->name('presensi.create');
    Route::post('/presensi/store', [Karyawan\PresensiController::class, 'store'])->middleware('throttle:30,1')->name('presensi.store');
    Route::get('/presensi/histori', [Karyawan\HistoriController::class, 'index'])->name('histori');

    Route::get('/presensi/izin', [Karyawan\PengajuanController::class, 'index'])->name('izin.index');
    Route::get('/presensi/izin/buat', [Karyawan\PengajuanController::class, 'create'])->name('izin.create');
    Route::post('/presensi/izin', [Karyawan\PengajuanController::class, 'store'])->name('izin.store');
    Route::post('/presensi/izin/{pengajuan}/batal', [Karyawan\PengajuanController::class, 'batal'])->name('izin.batal');

    Route::get('/editprofile', [Karyawan\ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/editprofile', [Karyawan\ProfilController::class, 'update'])->name('profil.update');
});

// Berkas privat (foto presensi, foto profil, lampiran): hanya pemilik atau HR/Admin.
Route::middleware('auth:karyawan,user')->prefix('berkas')->name('berkas.')->group(function () {
    Route::get('/presensi/{presensi}/{jenis}', [BerkasController::class, 'presensi'])->whereIn('jenis', ['in', 'out'])->name('presensi');
    Route::get('/profil/{karyawan}', [BerkasController::class, 'profil'])->name('profil');
    Route::get('/lampiran/{pengajuan}', [BerkasController::class, 'lampiran'])->name('lampiran');
});

// ---------------------------------------------------------------- Portal HR / Admin
Route::prefix('panel')->name('panel.')->group(function () {
    Route::middleware('guest:user')->group(function () {
        Route::get('/', [AdminLoginController::class, 'form'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'login'])->middleware('throttle:login')->name('login.proses');
    });

    Route::middleware(['auth:user', 'role:admin,hr'])->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Monitoring & pengajuan
        Route::get('/monitoring', [Admin\MonitoringController::class, 'index'])->name('monitoring');
        Route::get('/pengajuan', [Admin\PengajuanController::class, 'index'])->name('pengajuan.index');
        Route::post('/pengajuan/{pengajuan}/putuskan', [Admin\PengajuanController::class, 'putuskan'])->name('pengajuan.putuskan');
        Route::post('/pengajuan/{pengajuan}/kembalikan', [Admin\PengajuanController::class, 'kembalikan'])->name('pengajuan.kembalikan');

        // Laporan
        Route::get('/laporan', [Admin\LaporanController::class, 'index'])->name('laporan');
        Route::get('/laporan/presensi', [Admin\LaporanController::class, 'presensi'])->name('laporan.presensi');
        Route::get('/laporan/presensi/csv', [Admin\LaporanController::class, 'presensiCsv'])->name('laporan.presensi.csv');
        Route::get('/laporan/rekap', [Admin\LaporanController::class, 'rekap'])->name('laporan.rekap');
        Route::get('/laporan/rekap/csv', [Admin\LaporanController::class, 'rekapCsv'])->name('laporan.rekap.csv');

        // KPI & pengaturannya
        Route::get('/kpi', [Admin\KpiController::class, 'index'])->name('kpi');
        Route::get('/kpi/csv', [Admin\KpiController::class, 'csv'])->name('kpi.csv');
        Route::get('/kpi/{karyawan}', [Admin\KpiController::class, 'show'])->name('kpi.show');
        Route::get('/pengaturan', [Admin\PengaturanController::class, 'edit'])->name('pengaturan');
        Route::put('/pengaturan', [Admin\PengaturanController::class, 'update'])->name('pengaturan.update');

        // Data master yang dikelola HR
        Route::resource('karyawan', Admin\KaryawanController::class)->except('show')->parameters(['karyawan' => 'karyawan']);
        Route::post('/karyawan/{karyawan}/reset-password', [Admin\KaryawanController::class, 'resetPassword'])->name('karyawan.reset-password');
        Route::get('/karyawan/{karyawan}/jadwal', [Admin\JadwalController::class, 'edit'])->name('karyawan.jadwal');
        Route::put('/karyawan/{karyawan}/jadwal', [Admin\JadwalController::class, 'update'])->name('karyawan.jadwal.update');
        Route::resource('departemen', Admin\DepartemenController::class)->except('show')->parameters(['departemen' => 'departemen']);
        Route::resource('hari-libur', Admin\HariLiburController::class)->except('show')->parameters(['hari-libur' => 'hariLibur']);
    });

    // Konfigurasi sistem: khusus Admin
    Route::middleware(['auth:user', 'role:admin'])->group(function () {
        Route::resource('cabang', Admin\CabangController::class)->except('show')->parameters(['cabang' => 'cabang']);
        Route::resource('jam-kerja', Admin\JamKerjaController::class)->except('show')->parameters(['jam-kerja' => 'jamKerja']);
        Route::get('/log', [Admin\LogController::class, 'index'])->name('log');
    });
});
