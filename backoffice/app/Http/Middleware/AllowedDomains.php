<?php

namespace App\Http\Middleware;

use Closure;

class AllowedDomains
{
    protected $allowedDomains = [
        'anjapanshipping.com',
        'skjjapanshipping.com',
    ];

    public function handle($request, Closure $next)
    {
        $origin = $request->headers->get('origin');
        $referer = $request->headers->get('referer');

        $allowed = false;

        foreach ($this->allowedDomains as $domain) {
            if ($origin && str_contains($origin, $domain)) {
                $allowed = true;
                break;
            }
            if ($referer && str_contains($referer, $domain)) {
                $allowed = true;
                break;
            }
        }

        // Allow same-server requests (no origin/referer = direct server call)
        if (!$origin && !$referer) {
            $allowed = true;
        }

        if (!$allowed) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized domain'
            ], 403);
        }

        $response = $next($request);

        // Set CORS headers for allowed domains
        if ($origin) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'POST, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With');
        }

        return $response;
    }
}
