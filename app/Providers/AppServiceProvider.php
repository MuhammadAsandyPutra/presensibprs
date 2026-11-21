<?php

namespace App\Providers;

use App\Models\PengajuanIzin;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Di produksi semua URL (route, asset, foto) dibuat https agar tidak diblokir sebagai mixed content.
        if (config('app.env') === 'production' || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Batasi percobaan login: 5x per menit per (IP + NIK/email).
        RateLimiter::for('login', fn(Request $request) => Limit::perMinute(5)
            ->by($request->ip() . '|' . strtolower((string) ($request->input('nik') ?? $request->input('email')))));

        // Lencana jumlah pengajuan menunggu pada menu sidebar panel.
        View::composer('layouts.admin', function ($view) {
            $view->with('jumlahMenunggu', Schema::hasTable('pengajuan_izin')
                ? PengajuanIzin::query()->where('status_approved', 0)->count()
                : 0);
        });
    }
}
