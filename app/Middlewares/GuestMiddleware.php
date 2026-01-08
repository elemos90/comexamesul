<?php

namespace App\Middlewares;

use App\Http\Request;
use App\Utils\Auth;

class GuestMiddleware
{
    public function handle(Request $request, callable $next)
    {
        if (Auth::check()) {
            redirect('/dashboard');
            exit;
        }
        return $next($request);
    }
}
