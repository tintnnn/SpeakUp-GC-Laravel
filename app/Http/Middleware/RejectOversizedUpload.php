<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectOversizedUpload
{
    public function handle(Request $request, Closure $next): Response
    {
        $length = (int) $request->server('CONTENT_LENGTH', 0);
        $discarded = $request->isMethod('POST')
            && $length > 0
            && $request->request->count() === 0
            && $request->files->count() === 0;

        if ($discarded) {
            return redirect()->back()->withErrors([
                'evidence' => 'The upload is too large. Each file must be 50 MB or smaller. Use an external link for anything bigger.',
            ]);
        }

        return $next($request);
    }
}
