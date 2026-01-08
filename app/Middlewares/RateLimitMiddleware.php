<?php

namespace App\Middlewares;

use App\Http\Request;
use App\Utils\Flash;
use App\Utils\RateLimiter;

class RateLimitMiddleware
{
    private int $maxAttempts = 5;
    private int $decaySeconds = 900;

    public function setParameters(array $parameters): void
    {
        if (isset($parameters[0]) && is_numeric($parameters[0])) {
            $this->maxAttempts = (int) $parameters[0];
        }
        if (isset($parameters[1]) && is_numeric($parameters[1])) {
            $this->decaySeconds = (int) $parameters[1];
        }
    }

    public function handle(Request $request, callable $next)
    {
        $key = 'rate:' . $request->ip() . ':' . $request->path();
        if (!RateLimiter::hit($key, $this->decaySeconds, $this->maxAttempts)) {
            Flash::add('error', 'Muitas tentativas. Aguarde e tente novamente.');
            redirect('/login');
            exit;
        }
        return $next($request);
    }
}
