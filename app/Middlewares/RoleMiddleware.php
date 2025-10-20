<?php

namespace App\Middlewares;

use App\Http\Request;
use App\Http\Response;
use App\Utils\Auth;
use App\Utils\Flash;

class RoleMiddleware
{
    private array $roles = [];

    public function setParameters(array $parameters): void
    {
        $this->roles = $parameters;
    }

    public function handle(Request $request, callable $next)
    {
        if (!$this->roles) {
            return $next($request);
        }
        
        if (!Auth::check() || !Auth::hasAnyRole($this->roles)) {
            // Detectar requisições AJAX/JSON
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            $acceptsJson = !empty($_SERVER['HTTP_ACCEPT']) && 
                          strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
            
            if ($isAjax || $acceptsJson) {
                // Retornar JSON para requisições AJAX
                Response::json([
                    'success' => false,
                    'message' => 'Não autorizado. Permissões insuficientes.',
                    'redirect' => '/dashboard'
                ], 403);
                exit;
            }
            
            // Redirecionar para requisições normais
            Flash::add('error', 'Não autorizado.');
            header('Location: /dashboard');
            exit;
        }
        
        return $next($request);
    }
}
