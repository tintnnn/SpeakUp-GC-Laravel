<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Staff belong in the admin shell, matching the original protected layout. */
class RedirectStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isStaff()) {
            return redirect('/admin/dashboard');
        }

        return $next($request);
    }
}
