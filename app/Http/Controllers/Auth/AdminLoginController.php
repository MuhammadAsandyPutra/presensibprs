<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    public function form(): View
    {
        return view('auth.login-admin');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('user')->attempt($data, $request->boolean('ingat'))) {
            $request->session()->regenerate();
            LogAktivitas::catat('login', 'Masuk ke panel');

            return redirect()->intended(route('panel.dashboard'));
        }

        return back()->withInput($request->only('email'))->with('galat', 'Email atau kata sandi salah.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('user')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('panel.login');
    }
}
