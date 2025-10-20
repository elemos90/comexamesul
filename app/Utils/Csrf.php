<?php

namespace App\Utils;

use App\Http\Request;

class Csrf
{
    public static function token(): string
    {
        $key = env('CSRF_TOKEN_KEY', 'csrf_token');
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = bin2hex(random_bytes(32));
        }
        return $_SESSION[$key];
    }

    public static function validate(Request $request): bool
    {
        $key = env('CSRF_TOKEN_KEY', 'csrf_token');
        
        // Tentar pegar o token de várias fontes
        $submitted = $request->input('csrf') 
                  ?? $request->input('_token')
                  ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)
                  ?? ($_SERVER['HTTP_X_XSRF_TOKEN'] ?? null);
        
        if (!$submitted) {
            return false;
        }
        
        return hash_equals($_SESSION[$key] ?? '', $submitted);
    }
}
