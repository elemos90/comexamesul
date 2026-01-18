<?php

namespace App\Services;

use App\Models\User;

/**
 * Service for user promotions
 */
class UserPromotionService
{
    private UserRoleService $roleService;

    public function __construct()
    {
        $this->roleService = new UserRoleService();
    }

    /**
     * Promote user to Supervisor (includes Vigilante)
     */
    public function promoteToSupervisor(int $userId, ?int $promotedBy = null): array
    {
        // Add supervisor role (automatically adds vigilante)
        $result = $this->roleService->addRole($userId, 'supervisor', $promotedBy);

        if ($result['success']) {
            // Update primary role
            $this->roleService->updatePrimaryRole($userId, 'supervisor');

            // Log promotion
            $this->logPromotion($userId, 'Promovido a Supervisor', $promotedBy);

            return [
                'success' => true,
                'message' => 'Utilizador promovido a Supervisor com sucesso!'
            ];
        }

        return $result;
    }

    /**
     * Promote user to Committee Member
     */
    public function promoteToMember(int $userId, ?int $promotedBy = null): array
    {
        $result = $this->roleService->addRole($userId, 'membro', $promotedBy);

        if ($result['success']) {
            // Update primary role if not already coordinator
            if (!$this->roleService->hasRole($userId, 'coordenador')) {
                $this->roleService->updatePrimaryRole($userId, 'membro');
            }

            $this->logPromotion($userId, 'Promovido a Membro da Comissão', $promotedBy);

            return [
                'success' => true,
                'message' => 'Utilizador promovido a Membro da Comissão com sucesso!'
            ];
        }

        return $result;
    }

    /**
     * Promote user to Coordinator (includes Member)
     */
    public function promoteToCoordinator(int $userId, ?int $promotedBy = null): array
    {
        // Add coordinator role (automatically adds member)
        $result = $this->roleService->addRole($userId, 'coordenador', $promotedBy);

        if ($result['success']) {
            $this->roleService->updatePrimaryRole($userId, 'coordenador');
            $this->logPromotion($userId, 'Promovido a Coordenador', $promotedBy);

            return [
                'success' => true,
                'message' => 'Utilizador promovido a Coordenador com sucesso!'
            ];
        }

        return $result;
    }

    /**
     * Log promotion to audit trail
     */
    private function logPromotion(int $userId, string $details, ?int $promotedBy): void
    {
        $db = database();
        $stmt = $db->prepare("INSERT INTO user_audit_log (user_id, action, details, performed_by) VALUES (?, 'promotion', ?, ?)");
        $stmt->execute([$userId, $details, $promotedBy]);
    }

    /**
     * Get promotion history for a user
     */
    public function getPromotionHistory(int $userId): array
    {
        $db = database();
        $stmt = $db->prepare("
            SELECT ual.*, u.name as performed_by_name
            FROM user_audit_log ual
            LEFT JOIN users u ON u.id = ual.performed_by
            WHERE ual.user_id = ? AND ual.action IN ('promotion', 'role_added', 'role_removed')
            ORDER BY ual.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
