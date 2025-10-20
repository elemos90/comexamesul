<?php

namespace App\Middlewares;

use App\Http\Request;
use App\Http\Response;
use App\Utils\Auth;
use App\Utils\Flash;

class AuthMiddleware
{
    public function handle(Request $request, callable $next)
    {
        if (!Auth::check()) {
            // Detectar requisições AJAX/JSON
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            $acceptsJson = !empty($_SERVER['HTTP_ACCEPT']) && 
                          strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
            
            if ($isAjax || $acceptsJson) {
                // Retornar JSON para requisições AJAX
                Response::json([
                    'success' => false,
                    'message' => 'Sessão expirada. Por favor, faça login novamente.',
                    'redirect' => '/login'
                ], 401);
                exit;
            }
            
            // Redirecionar para requisições normais
            Flash::add('error', 'Por favor, faça login para continuar.');
            header('Location: /login');
            exit;
        }
        return $next($request);
    }
}
