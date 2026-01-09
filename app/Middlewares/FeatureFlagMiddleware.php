<?php

namespace App\Middlewares;

use App\Http\Request;
use App\Http\Response;
use App\Services\FeatureFlagService;
use App\Utils\Auth;
use App\Utils\Flash;

/**
 * Middleware para verificar Feature Flags
 * 
 * Uso nas rotas:
 *   $router->get('/juries/create', 'JuryController@create', 
 *       ['AuthMiddleware', 'RoleMiddleware:membro', 'FeatureFlagMiddleware:commission.create_jury']);
 */
class FeatureFlagMiddleware
{
    private string $featureCode = '';

    public function setParameters(array $parameters): void
    {
        $this->featureCode = $parameters[0] ?? '';
    }

    public function handle(Request $request, callable $next)
    {
        // Se não há código de feature, prosseguir
        if (empty($this->featureCode)) {
            return $next($request);
        }

        // Verificar se a funcionalidade está habilitada
        if (!FeatureFlagService::check($this->featureCode)) {
            return $this->handleDenied($request);
        }

        return $next($request);
    }

    /**
     * Trata acesso negado por feature flag
     */
    private function handleDenied(Request $request)
    {
        $message = FeatureFlagService::getDisabledMessage();

        // Detectar requisições AJAX/JSON
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $acceptsJson = !empty($_SERVER['HTTP_ACCEPT']) &&
            strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

        if ($isAjax || $acceptsJson) {
            Response::json([
                'success' => false,
                'message' => $message,
                'error_type' => 'feature_disabled',
                'feature_code' => $this->featureCode
            ], 403);
            exit;
        }

        // Redirecionar para requisições normais
        Flash::add('warning', $message);
        redirect('/dashboard');
        exit;
    }
}
