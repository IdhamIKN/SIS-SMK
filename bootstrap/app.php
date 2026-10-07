<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException as SpatieUnauthorizedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register Spatie Laravel Permission middleware
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

            // Developer-only: akses impersonate tersembunyi
            'is_developer'       => \App\Http\Middleware\IsDeveloper::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // ── Helper: render view error dan log ke file Laravel ──────────────────────
        $renderError = function (int $code, \Throwable $e) {
            // Log error ke laravel.log untuk debugging (tanpa expose ke user)
            \Illuminate\Support\Facades\Log::channel('stack')->error(
                "[HTTP {$code}] " . get_class($e) . ': ' . $e->getMessage(),
                [
                    'url'        => request()->fullUrl(),
                    'method'     => request()->method(),
                    'ip'         => request()->ip(),
                    'user_id'    => \Illuminate\Support\Facades\Auth::id(),
                    'user_agent' => request()->userAgent(),
                    'file'       => $e->getFile(),
                    'line'       => $e->getLine(),
                ]
            );

            if (view()->exists("errors.{$code}")) {
                return response()->view("errors.{$code}", [], $code);
            }

            // Fallback view generic jika view khusus tidak ada
            return response()->view('errors.error', [
                'statusCode'   => $code,
                'icon'         => 'exclamation-circle',
                'iconClass'    => 'misc',
                'errorTitle'   => 'Terjadi Kesalahan',
                'errorMessage' => 'Permintaan tidak dapat diproses. Silakan coba lagi atau hubungi administrator.',
            ], $code);
        };

        // ── 404 Not Found ──────────────────────────────────────────────────────────
        $exceptions->render(function (NotFoundHttpException $e, $request) use ($renderError) {
            if (! $request->expectsJson()) {
                // Abaikan log untuk request browser otomatis yang tidak relevan
                $noisyPaths = ['/_service-worker.js', '/service-worker.js', '/sw.js', '/favicon.ico', '/robots.txt'];
                if (in_array($request->getPathInfo(), $noisyPaths)) {
                    return response('', 404);
                }
                return $renderError(404, $e);
            }
        });

        // ── 403 Forbidden (AccessDeniedHttpException) ─────────────────────────────
        $exceptions->render(function (AccessDeniedHttpException $e, $request) use ($renderError) {
            if (! $request->expectsJson()) {
                return $renderError(403, $e);
            }
        });

        // ── 403 Forbidden (Spatie Permission UnauthorizedException) ───────────────
        $exceptions->render(function (SpatieUnauthorizedException $e, $request) use ($renderError) {
            if (! $request->expectsJson()) {
                return $renderError(403, $e);
            }
        });

        // ── 401 Unauthenticated ────────────────────────────────────────────────────
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            // Redirect ke login dan simpan intended URL agar bisa balik setelah login
            return redirect()->guest(route('login'));
        });

        // ── 419 CSRF / Token Mismatch ──────────────────────────────────────────────
        // Redirect back dengan pesan error — user masih login, hanya token kadaluarsa.
        // Jika halaman referrer tidak tersedia (misal akses langsung), fallback ke login.
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesi halaman telah kedaluwarsa. Silakan muat ulang halaman dan coba lagi.'], 419);
            }

            return redirect()->back()
                ->withInput($request->except(['_token', 'password', 'password_confirmation']))
                ->with('error', 'Sesi halaman telah kedaluwarsa. Silakan coba lagi.');
        });

        // ── 429 Too Many Requests ─────────────────────────────────────────────────
        $exceptions->render(function (ThrottleRequestsException $e, $request) use ($renderError) {
            if (! $request->expectsJson()) {
                return $renderError(429, $e);
            }
        });

        // ── HTTP Exception umum (503, dan kode lain yang belum ditangani) ─────────
        $exceptions->render(function (HttpException $e, $request) use ($renderError) {
            if (! $request->expectsJson()) {
                return $renderError($e->getStatusCode(), $e);
            }
        });

        // ── Unhandled Exception / 500 Internal Server Error ───────────────────────
        // Hanya aktif saat APP_DEBUG=false agar developer tetap melihat stack trace.
        // ValidationException dikecualikan — biarkan Laravel menangani redirect-back
        // dengan flash errors secara bawaan (form biasa) atau 422 JSON (AJAX).
        $exceptions->render(function (\Throwable $e, $request) use ($renderError) {
            if ($e instanceof ValidationException) {
                return null; // serahkan ke handler bawaan Laravel
            }
            if (! $request->expectsJson() && ! app()->hasDebugModeEnabled()) {
                return $renderError(500, $e);
            }
        });
    })->create();
