<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: hanya developer (email terdaftar di DEVELOPER_EMAIL) yang boleh akses.
 * Route impersonate tidak ditampilkan di UI manapun — diakses langsung via URL.
 */
class IsDeveloper
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            abort(404); // Pura-pura halaman tidak ada jika belum login
        }

        $developerEmails = array_map(
            'trim',
            explode(',', env('DEVELOPER_EMAIL', ''))
        );

        $authorized = in_array(
            Auth::user()->email,
            array_filter($developerEmails)
        );

        if (! $authorized) {
            abort(404); // Tampilkan 404, bukan 403 — supaya keberadaan fitur tidak bocor
        }

        return $next($request);
    }
}
