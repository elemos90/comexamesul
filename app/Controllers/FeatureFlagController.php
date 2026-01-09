<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\FeatureFlagService;
use App\Utils\Auth;
use App\Utils\Csrf;

class FeatureFlagController extends Controller
{
    /**
     * Página de configurações de funcionalidades
     */
    public function index(): string
    {
        $user = Auth::user();

        if (!$user || $user['role'] !== 'coordenador') {
            redirect('/dashboard');
            exit;
        }

        // Obter todas as flags agrupadas
        $flags = FeatureFlagService::getAll();

        // Mapeamento de roles para exibição (3 tabs separados)
        $roleLabels = [
            'membro' => 'Membro da Comissão',
            'supervisor' => 'Supervisor',
            'vigilante' => 'Vigilante'
        ];

        // Mapeamento de grupos para exibição
        $groupLabels = [
            'juris' => 'Gestão de Júris',
            'alocacao' => 'Alocação de Pessoal',
            'relatorios' => 'Relatórios',
            'pagamentos' => 'Pagamentos',
            'exportacao' => 'Exportação',
            'visualizacao' => 'Visualização',
            'geral' => 'Geral'
        ];

        return $this->view('features/index', [
            'title' => 'Configurações de Funcionalidades',
            'flags' => $flags,
            'roleLabels' => $roleLabels,
            'groupLabels' => $groupLabels,
            'csrfToken' => csrf_token()
        ]);
    }

    /**
     * Toggle de funcionalidade (AJAX)
     */
    public function toggle(Request $request): void
    {
        $user = Auth::user();

        if (!$user || $user['role'] !== 'coordenador') {
            Response::json([
                'success' => false,
                'message' => 'Acesso não autorizado'
            ], 403);
            return;
        }

        $role = $request->input('role');
        $featureCode = $request->input('feature_code');
        $enabled = filter_var($request->input('enabled'), FILTER_VALIDATE_BOOLEAN);

        // Validações
        if (!$role || !$featureCode) {
            Response::json([
                'success' => false,
                'message' => 'Parâmetros inválidos'
            ], 400);
            return;
        }

        // Não permitir desativar funcionalidades críticas
        $criticalFeatures = ['guard.view_juries'];
        if (in_array($featureCode, $criticalFeatures) && !$enabled) {
            Response::json([
                'success' => false,
                'message' => 'Esta funcionalidade é crítica e não pode ser desativada'
            ], 400);
            return;
        }

        // Executar toggle
        $result = FeatureFlagService::toggle($role, $featureCode, $enabled);

        Response::json($result);
    }

    /**
     * Retorna o estado de todas as flags (API)
     */
    public function getAll(): void
    {
        $user = Auth::user();

        if (!$user || $user['role'] !== 'coordenador') {
            Response::json([
                'success' => false,
                'message' => 'Acesso não autorizado'
            ], 403);
            return;
        }

        $flags = FeatureFlagService::getAll();

        Response::json([
            'success' => true,
            'flags' => $flags
        ]);
    }

    /**
     * Reseta todas as flags para valores padrão
     */
    public function reset(Request $request): void
    {
        $user = Auth::user();

        if (!$user || $user['role'] !== 'coordenador') {
            Response::json([
                'success' => false,
                'message' => 'Acesso não autorizado'
            ], 403);
            return;
        }

        $model = new \App\Models\FeatureFlag();
        $model->resetToDefaults();

        // Invalidar cache
        FeatureFlagService::invalidateCache();

        Response::json([
            'success' => true,
            'message' => 'Configurações restauradas para valores padrão'
        ]);
    }
}
