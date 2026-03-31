<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            if ($request->is('scanner') || $request->is('scanner/*') || $request->is('qr-scan/*') || $request->is('*/qr-scan/*')) {
                return route('scanner.login');
            }
            return route('login');
        }
    }
}
