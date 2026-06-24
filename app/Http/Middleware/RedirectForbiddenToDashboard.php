<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class RedirectForbiddenToDashboard
{
    /**
     * Handle stale intended URLs or forbidden pages after a different user logs in.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (Throwable $exception) {
            if (! $this->shouldRedirect($request, $exception)) {
                throw $exception;
            }

            $request->session()->forget('url.intended');

            return redirect()
                ->route('dashboard')
                ->with('warning', 'Akses halaman sebelumnya tidak tersedia untuk akun ini. Anda dialihkan ke dashboard.');
        }
    }

    private function shouldRedirect(Request $request, Throwable $exception): bool
    {
        if (! auth()->check() || $request->expectsJson()) {
            return false;
        }

        if ($exception instanceof AccessDeniedHttpException || $exception instanceof UnauthorizedException) {
            return true;
        }

        return $exception instanceof HttpExceptionInterface && $exception->getStatusCode() === 403;
    }
}
