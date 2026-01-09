<?php

namespace App\Services;

use App\Models\FeatureFlag;
use App\Services\ActivityLogger;
use App\Utils\Auth;

/**
 * Serviço centralizado para verificação de Feature Flags
 * 
 * Uso:
 *   FeatureFlagService::check('commission.create_jury')
 *   FeatureFlagService::isEnabled('membro', 'commission.create_jury')
 */
class FeatureFlagService
{
    private static ?array $cache = null;

    /**
     * Verifica se uma funcionalidade está habilitada para o utilizador atual
     * Considera o role atual do utilizador
     */
    public static function check(string $featureCode): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $role = $user['role'];

        // Coordenador tem acesso total (nunca restringido por flags)
        if ($role === 'coordenador') {
            return true;
        }

        // Mapear role para o código usado nas flags
        $flagRole = self::mapRoleToFlagRole($role);

        return self::isEnabled($flagRole, $featureCode);
    }

    /**
     * Verifica se uma funcionalidade está habilitada para um role específico
     */
    public static function isEnabled(string $role, string $featureCode): bool
    {
        // Carregar cache se necessário
        if (self::$cache === null) {
            self::loadCache();
        }

        $key = "{$role}:{$featureCode}";

        // Se não está no cache, verificar no banco
        if (!isset(self::$cache[$key])) {
            $model = new FeatureFlag();
            self::$cache[$key] = $model->isEnabled($role, $featureCode);
        }

        return self::$cache[$key];
    }

    /**
     * Carrega todas as flags para o cache
     */
    private static function loadCache(): void
    {
        self::$cache = [];

        $model = new FeatureFlag();

        foreach (['membro', 'supervisor', 'vigilante'] as $role) {
            $flags = $model->getAllByRole($role);
            foreach ($flags as $flag) {
                $key = "{$role}:{$flag['feature_code']}";
                self::$cache[$key] = (bool) $flag['enabled'];
            }
        }
    }

    /**
     * Invalida o cache (chamar após toggle)
     */
    public static function invalidateCache(): void
    {
        self::$cache = null;
    }

    /**
     * Toggle uma funcionalidade com auditoria
     */
    public static function toggle(string $role, string $featureCode, bool $enabled): array
    {
        $userId = Auth::id();
        if (!$userId) {
            return ['success' => false, 'message' => 'Utilizador não autenticado'];
        }

        $model = new FeatureFlag();

        // Obter estado anterior
        $flag = $model->getFlag($role, $featureCode);
        if (!$flag) {
            return ['success' => false, 'message' => 'Flag não encontrada'];
        }

        $previousState = (bool) $flag['enabled'];

        // Se não mudou, retornar sem fazer nada
        if ($previousState === $enabled) {
            return ['success' => true, 'message' => 'Estado não alterado', 'changed' => false];
        }

        // Atualizar
        $result = $model->toggle($role, $featureCode, $enabled, $userId);

        if (!$result) {
            return ['success' => false, 'message' => 'Erro ao atualizar flag'];
        }

        // Registar auditoria
        ActivityLogger::log('feature_flags', $flag['id'], $enabled ? 'enabled' : 'disabled', [
            'role' => $role,
            'feature_code' => $featureCode,
            'feature_name' => $flag['feature_name'],
            'previous_state' => $previousState ? 'enabled' : 'disabled',
            'new_state' => $enabled ? 'enabled' : 'disabled'
        ]);

        // Invalidar cache
        self::invalidateCache();

        return [
            'success' => true,
            'message' => $enabled ? 'Funcionalidade ativada' : 'Funcionalidade desativada',
            'changed' => true,
            'previous_state' => $previousState,
            'new_state' => $enabled
        ];
    }

    /**
     * Retorna todas as flags agrupadas por role
     */
    public static function getAll(): array
    {
        $model = new FeatureFlag();
        return $model->getAllGroupedByRole();
    }

    /**
     * Retorna flags desabilitadas para o utilizador atual
     */
    public static function getDisabledFeatures(): array
    {
        $user = Auth::user();
        if (!$user || $user['role'] === 'coordenador') {
            return [];
        }

        $flagRole = self::mapRoleToFlagRole($user['role']);

        $model = new FeatureFlag();
        return $model->getDisabledByRole($flagRole);
    }

    /**
     * Mapeia o role do utilizador para o código usado nas flags
     */
    private static function mapRoleToFlagRole(string $role): string
    {
        // Supervisor and vigilante are now separate roles
        return match ($role) {
            'membro', 'comissao' => 'membro',
            'supervisor' => 'supervisor',
            'vigilante' => 'vigilante',
            default => $role
        };
    }

    /**
     * Retorna a mensagem padrão para funcionalidade desabilitada
     */
    public static function getDisabledMessage(): string
    {
        return 'Esta funcionalidade foi temporariamente desativada pelo Coordenador.';
    }
}
