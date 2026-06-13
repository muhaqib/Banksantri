<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSantriIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isAlumni()) {
            $message = 'Akun alumni hanya dapat melihat data dan tidak dapat melakukan perubahan.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return back()->withErrors(['alumni' => $message]);
        }

        return $next($request);
    }
}
