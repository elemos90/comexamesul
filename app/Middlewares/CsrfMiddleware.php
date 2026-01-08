<?php

namespace App\Middlewares;

use App\Http\Request;
use App\Http\Response;
use App\Utils\Csrf;
use App\Utils\Flash;

class CsrfMiddleware
{
    public function handle(Request $request, callable $next)
    {
        if (in_array($request->method(), ['POST', 'PUT', 'DELETE'])) {
            error_log("DEBUG CsrfMiddleware: Validando CSRF para " . $request->path());

            if (!Csrf::validate($request)) {
                error_log("ERRO CsrfMiddleware: Token CSRF inválido!");
                error_log("DEBUG: Token enviado: " . ($request->input('csrf') ?? 'NULL'));
                error_log("DEBUG: Token sessão: " . ($_SESSION[env('CSRF_TOKEN_KEY', 'csrf_token')] ?? 'NULL'));

                // Detectar requisições AJAX/JSON
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

                $acceptsJson = !empty($_SERVER['HTTP_ACCEPT']) &&
                    strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

                if ($isAjax || $acceptsJson) {
                    // Retornar JSON para requisições AJAX
                    Response::json([
                        'success' => false,
                        'message' => 'Token CSRF inválido. Recarregue a página e tente novamente.'
                    ], 419);
                    exit;
                }

                // Resposta padrão para requisições normais
                Flash::add('error', 'Token CSRF inválido.');
                http_response_code(419);
                exit('Sessão expirada. Recarregue a página.');
            }

            error_log("DEBUG CsrfMiddleware: CSRF válido, continuando...");
        }
        return $next($request);
    }
}
