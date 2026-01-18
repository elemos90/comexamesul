<?php

namespace App\Services;

use App\Models\User;

/**
 * Service for managing user roles with hierarchical logic
 */
class UserRoleService
{
    /**
     * Role hierarchy: child roles automatically include parent roles
     */
    private const ROLE_HIERARCHY = [
        'supervisor' => ['vigilante'],
        'coordenador' => ['membro']
    ];

    /**
     * All valid roles in the system
     */
    private const VALID_ROLES = ['vigilante', 'supervisor', 'membro', 'coordenador'];

    /**
     * Get all roles for a user (including inherited ones)
     */
    public function getUserRoles(int $userId): array
    {
        $db = database();
        $stmt = $db->prepare("SELECT role FROM user_roles WHERE user_id = ? ORDER BY FIELD(role, 'coordenador', 'membro', 'supervisor', 'vigilante')");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(int $userId, string $role): bool
    {
        $roles = $this->getUserRoles($userId);
        return in_array($role, $roles);
    }

    /**
     * Add a role to user (with automatic parent role addition)
     */
    public function addRole(int $userId, string $role, ?int $assignedBy = null): array
    {
        if (!in_array($role, self::VALID_ROLES)) {
            return ['success' => false, 'message' => 'Papel inválido'];
        }

        $db = database();

        // Get roles to add (including inherited)
        $rolesToAdd = $this->getRolesWithHierarchy($role);

        $added = [];
        foreach ($rolesToAdd as $r) {
            // Check if already has role
            if ($this->hasRole($userId, $r)) {
                continue;
            }

            $stmt = $db->prepare("INSERT INTO user_roles (user_id, role, assigned_by) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $r, $assignedBy]);
            $added[] = $r;

            // Log the action
            $this->logAction($userId, 'role_added', "Papel '$r' adicionado", $assignedBy);
        }

        return [
            'success' => true,
            'message' => count($added) > 0 ? 'Papéis adicionados: ' . implode(', ', $added) : 'Utilizador já possui este papel',
            'added' => $added
        ];
    }

    /**
     * Remove a role from user (with validation)
     */
    public function removeRole(int $userId, string $role, ?int $removedBy = null): array
    {
        // Cannot remove inherited roles
        $userRoles = $this->getUserRoles($userId);

        // Check if this role is required by another role
        foreach ($userRoles as $userRole) {
            if (isset(self::ROLE_HIERARCHY[$userRole]) && in_array($role, self::ROLE_HIERARCHY[$userRole])) {
                return [
                    'success' => false,
                    'message' => "Não pode remover '$role' enquanto o utilizador for '$userRole'"
                ];
            }
        }

        $db = database();
        $stmt = $db->prepare("DELETE FROM user_roles WHERE user_id = ? AND role = ?");
        $stmt->execute([$userId, $role]);

        if ($stmt->rowCount() > 0) {
            $this->logAction($userId, 'role_removed', "Papel '$role' removido", $removedBy);
            return ['success' => true, 'message' => "Papel '$role' removido"];
        }

        return ['success' => false, 'message' => 'Papel não encontrado'];
    }

    /**
     * Get all roles that should be added including hierarchical dependencies
     */
    private function getRolesWithHierarchy(string $role): array
    {
        $roles = [$role];

        if (isset(self::ROLE_HIERARCHY[$role])) {
            $roles = array_merge($roles, self::ROLE_HIERARCHY[$role]);
        }

        return array_unique($roles);
    }

    /**
     * Update user's primary role in users table
     */
    public function updatePrimaryRole(int $userId, string $role): void
    {
        $db = database();
        $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $userId]);
    }

    /**
     * Log an action to audit trail
     */
    private function logAction(int $userId, string $action, string $details, ?int $performedBy): void
    {
        $db = database();
        $stmt = $db->prepare("INSERT INTO user_audit_log (user_id, action, details, performed_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $details, $performedBy]);
    }

    /**
     * Get valid roles list
     */
    public static function getValidRoles(): array
    {
        return self::VALID_ROLES;
    }

    /**
     * Get role display name in Portuguese
     */
    public static function getRoleDisplayName(string $role): string
    {
        $names = [
            'vigilante' => 'Vigilante',
            'supervisor' => 'Supervisor',
            'membro' => 'Membro da Comissão',
            'coordenador' => 'Coordenador'
        ];
        return $names[$role] ?? $role;
    }

    /**
     * Get role badge color
     */
    public static function getRoleBadgeColor(string $role): string
    {
        $colors = [
            'vigilante' => 'blue',
            'supervisor' => 'purple',
            'membro' => 'green',
            'coordenador' => 'red'
        ];
        return $colors[$role] ?? 'gray';
    }
}
