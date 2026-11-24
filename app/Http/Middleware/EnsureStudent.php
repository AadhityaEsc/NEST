<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless(
            $user && $user->role === 'student' && $user->university_id,
            403,
            'NEST is available only to registered university students.'
        );

        return $next($request);
    }
}
