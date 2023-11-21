<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyCspNonce
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $nonce = base64_encode(random_bytes(16)); // Genera un nonce aleatorio

        // Establece la política de seguridad CSP en la respuesta
        $response->header('Content-Security-Policy', "script-src 'self' https://http2.mlstatic.com/analytics/ga/mla-mp-analytics.min.js 'nonce-$nonce' 'strict-dynamic' 'unsafe-eval' 'report-sample'");

        return $response;
    }
}
