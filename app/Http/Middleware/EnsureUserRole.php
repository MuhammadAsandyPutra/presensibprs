<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Pemakaian di route: ->middleware('role:admin') atau ->middleware('role:admin,hr'). */
class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::guard('user')->user();

        abort_unless($user && in_array($user->role, $roles, true), 403, 'Anda tidak memiliki akses ke halaman ini.');

        return $next($request);
    }
}
