<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            // Scanner routes redirect to scanner login
            if ($request->is('scanner') || $request->is('scanner/*')) {
                return route('scanner.login');
            }
            return route('login');
        }
    }
}
