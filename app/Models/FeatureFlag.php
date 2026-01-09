<?php

namespace App\Models;

use App\Database\Connection;
use PDO;

class FeatureFlag
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    /**
     * Verifica se uma funcionalidade está habilitada para um perfil
     */
    public function isEnabled(string $role, string $featureCode): bool
    {
        $stmt = $this->db->prepare("
            SELECT enabled FROM feature_flags 
            WHERE role = ? AND feature_code = ?
        ");
        $stmt->execute([$role, $featureCode]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se não existe, assume habilitado (fallback seguro)
        return $result ? (bool) $result['enabled'] : true;
    }

    /**
     * Retorna todas as flags de um perfil
     */
    public function getAllByRole(string $role): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM feature_flags 
            WHERE role = ?
            ORDER BY feature_group, feature_name
        ");
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna todas as flags agrupadas por perfil
     */
    public function getAllGroupedByRole(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM feature_flags 
            ORDER BY role, feature_group, feature_name
        ");
        $flags = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grouped = [];
        foreach ($flags as $flag) {
            $role = $flag['role'];
            $group = $flag['feature_group'];

            if (!isset($grouped[$role])) {
                $grouped[$role] = [];
            }
            if (!isset($grouped[$role][$group])) {
                $grouped[$role][$group] = [];
            }
            $grouped[$role][$group][] = $flag;
        }

        return $grouped;
    }

    /**
     * Ativa ou desativa uma funcionalidade
     */
    public function toggle(string $role, string $featureCode, bool $enabled, int $userId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE feature_flags 
            SET enabled = ?, updated_by = ?, updated_at = NOW()
            WHERE role = ? AND feature_code = ?
        ");
        return $stmt->execute([$enabled ? 1 : 0, $userId, $role, $featureCode]);
    }

    /**
     * Retorna o estado anterior de uma flag (para auditoria)
     */
    public function getFlag(string $role, string $featureCode): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM feature_flags 
            WHERE role = ? AND feature_code = ?
        ");
        $stmt->execute([$role, $featureCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Retorna flags desabilitadas para um perfil
     */
    public function getDisabledByRole(string $role): array
    {
        $stmt = $this->db->prepare("
            SELECT feature_code FROM feature_flags 
            WHERE role = ? AND enabled = 0
        ");
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Reseta todas as flags para valores padrão
     */
    public function resetToDefaults(): bool
    {
        // Membro: tudo ativo
        $this->db->exec("UPDATE feature_flags SET enabled = 1 WHERE role = 'membro'");

        // Vigilante: alguns desativados por padrão
        $this->db->exec("UPDATE feature_flags SET enabled = 1 WHERE role = 'vigilante'");
        $this->db->exec("UPDATE feature_flags SET enabled = 0 WHERE role = 'vigilante' AND feature_code IN ('guard.edit_post_exam', 'guard.export_payment_pdf')");

        return true;
    }
}
