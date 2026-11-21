<?php

use App\Http\Middleware\EnsureUserRole;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Hostinger/LiteSpeed/CDN meneruskan permintaan lewat proxy: tanpa ini Laravel mengira
        // request http dan membuat URL foto http:// (diblokir browser sebagai mixed content).
        $middleware->trustProxies(at: '*');

        $middleware->alias(['role' => EnsureUserRole::class]);
        $middleware->web(append: [SecurityHeaders::class]);

        // Tamu yang membuka halaman terproteksi diarahkan ke login portal yang sesuai.
        $middleware->redirectGuestsTo(fn(Request $request) => $request->is('panel', 'panel/*')
            ? route('panel.login')
            : route('login'));

        // Pengguna yang sudah login diarahkan ke beranda portalnya.
        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->is('panel', 'panel/*') && Auth::guard('user')->check()) {
                return route('panel.dashboard');
            }
            if (Auth::guard('karyawan')->check()) {
                return route('dashboard');
            }

            return Auth::guard('user')->check() ? route('panel.dashboard') : route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn(Request $request) => $request->expectsJson());
    })->create();
