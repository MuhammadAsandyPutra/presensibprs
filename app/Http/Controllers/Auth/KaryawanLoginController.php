<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KaryawanLoginController extends Controller
{
    public function form(): View
    {
        return view('auth.login-karyawan');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nik' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string'],
        ]);

        // 'aktif' => true: karyawan nonaktif tidak bisa masuk.
        if (Auth::guard('karyawan')->attempt([...$data, 'aktif' => true], $request->boolean('ingat'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withInput($request->only('nik'))
            ->with('galat', 'NIK atau kata sandi salah, atau akun Anda tidak aktif.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('karyawan')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
