<?php

namespace App\Models;

use PDO;

class User extends BaseModel
{
    protected string $table = 'users';

    // Colunas seguras para SELECT (NUNCA incluir password_hash aqui!)
    protected array $selectColumns = [
        'id',
        'name',
        'username',
        'email',
        'phone',
        'gender',
        'birth_date',
        'document_type',
        'document_number',
        'origin_university',
        'university',
        'nuit',
        'degree',
        'major_area',
        'bank_name',
        'nib',
        'bank_account_holder',
        'role',
        'email_verified_at',
        'verification_token',
        'avatar_url',
        'supervisor_eligible',
        'available_for_vigilance',
        'must_change_password',
        'profile_complete',
        'profile_completed',
        'profile_completed_at',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected array $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'gender',
        'birth_date',
        'document_type',
        'document_number',
        'origin_university',
        'university',
        'nuit',
        'degree',
        'major_area',
        'bank_name',
        'nib',
        'bank_account_holder',
        'role',
        'password_hash',
        'email_verified_at',
        'verification_token',
        'avatar_url',
        'supervisor_eligible',
        'available_for_vigilance',
        'must_change_password',
        'temp_password_at',
        'profile_complete',
        'profile_completed',
        'profile_completed_at',
        'is_active',
        'deactivated_at',
        'deactivation_reason',
        'created_by',
        'updated_at',
        'created_at',
        'recovery_keyword_hash',
        'recovery_pin_hash',
    ];

    public function findByEmail(string $email): ?array
    {
        // Incluir password_hash apenas para autenticação
        $stmt = $this->db->prepare(
            "SELECT *, password_hash FROM {$this->table} WHERE email = :email LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Buscar utilizador por username (para autenticação)
     */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *, password_hash FROM {$this->table} WHERE username = :username LIMIT 1"
        );
        $stmt->execute(['username' => strtolower(trim($username))]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Verificar se username está disponível
     */
    public function isUsernameAvailable(string $username, ?int $excludeUserId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE username = :username";
        $params = ['username' => strtolower(trim($username))];

        if ($excludeUserId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeUserId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() === 0;
    }

    /**
     * Criar pedido de reset de senha (interno, sem email)
     */
    public function createPasswordResetRequest(int $userId): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO password_reset_requests (user_id, requested_at, status) 
             VALUES (:user_id, :requested_at, 'pending')"
        );
        $stmt->execute([
            'user_id' => $userId,
            'requested_at' => now(),
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Obter pedidos de reset pendentes (para Coordenador)
     */
    public function getPendingPasswordResets(): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, u.name, u.username, u.email 
             FROM password_reset_requests r
             JOIN users u ON u.id = r.user_id
             WHERE r.status = 'pending'
             ORDER BY r.requested_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Buscar pedido de reset por ID
     */
    public function findPasswordResetRequest(int $requestId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, u.name, u.username, u.email 
             FROM password_reset_requests r
             JOIN users u ON u.id = r.user_id
             WHERE r.id = :id"
        );
        $stmt->execute(['id' => $requestId]);
        $request = $stmt->fetch();
        return $request ?: null;
    }

    /**
     * Resolver pedido de reset de senha
     */
    public function resolvePasswordReset(int $requestId, int $resolvedBy, string $tempPassword): bool
    {
        $this->db->beginTransaction();
        try {
            // Obter dados do pedido
            $stmt = $this->db->prepare("SELECT user_id FROM password_reset_requests WHERE id = :id");
            $stmt->execute(['id' => $requestId]);
            $request = $stmt->fetch();

            if (!$request) {
                $this->db->rollBack();
                return false;
            }

            // Atualizar senha do utilizador
            $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare(
                "UPDATE {$this->table} SET 
                 password_hash = :hash, 
                 must_change_password = 1, 
                 temp_password_at = :temp_at,
                 updated_at = :updated
                 WHERE id = :id"
            );
            $stmt->execute([
                'hash' => $hashedPassword,
                'temp_at' => now(),
                'updated' => now(),
                'id' => $request['user_id'],
            ]);

            // Marcar pedido como resolvido
            $stmt = $this->db->prepare(
                "UPDATE password_reset_requests SET 
                 status = 'resolved', 
                 resolved_at = :resolved_at, 
                 resolved_by = :resolved_by,
                 new_temp_password = :temp_pass
                 WHERE id = :id"
            );
            $stmt->execute([
                'resolved_at' => now(),
                'resolved_by' => $resolvedBy,
                'temp_pass' => $tempPassword, // Guardar temporariamente para notificar
                'id' => $requestId,
            ]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Limpar flag de alteração obrigatória de senha
     */
    public function clearMustChangePassword(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET must_change_password = 0, temp_password_at = NULL, updated_at = :updated WHERE id = :id"
        );
        return $stmt->execute(['updated' => now(), 'id' => $id]);
    }

    /**
     * Marcar perfil como completo (nova coluna profile_complete)
     */
    public function setProfileComplete(int $id, bool $complete = true): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET profile_complete = :complete, updated_at = :updated WHERE id = :id"
        );
        return $stmt->execute(['complete' => $complete ? 1 : 0, 'updated' => now(), 'id' => $id]);
    }

    public function findByVerificationToken(string $token): ?array
    {
        $columns = $this->getSelectColumns();
        $stmt = $this->db->prepare("SELECT {$columns}, password_hash FROM {$this->table} WHERE verification_token = :token LIMIT 1");
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function createUser(array $data): int
    {
        $data['created_at'] = $data['created_at'] ?? now();
        $data['updated_at'] = $data['updated_at'] ?? now();
        return $this->create($data);
    }

    public function updateUser(int $id, array $data): bool
    {
        $data['updated_at'] = now();
        return $this->update($id, $data);
    }

    public function markEmailVerified(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET email_verified_at = :verified, verification_token = NULL, updated_at = :updated WHERE id = :id");
        return $stmt->execute([
            'verified' => now(),
            'updated' => now(),
            'id' => $id,
        ]);
    }

    public function setVerificationToken(int $id, string $token): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET verification_token = :token, updated_at = :updated WHERE id = :id");
        return $stmt->execute([
            'token' => $token,
            'updated' => now(),
            'id' => $id,
        ]);
    }

    public function availableVigilantes(): array
    {
        $columns = $this->getSelectColumns();
        $sql = "SELECT {$columns} FROM {$this->table} WHERE role = 'vigilante' AND available_for_vigilance = 1 ORDER BY name";
        return $this->statement($sql);
    }

    public function supervisors(): array
    {
        $columns = $this->getSelectColumns();
        $sql = "SELECT {$columns} FROM {$this->table} WHERE role = 'vigilante' ORDER BY supervisor_eligible DESC, name";
        return $this->statement($sql);
    }

    public function allByRole(string $role): array
    {
        $columns = $this->getSelectColumns();
        $stmt = $this->db->prepare("SELECT {$columns} FROM {$this->table} WHERE role = :role ORDER BY name");
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll();
    }

    /**
     * Buscar usuários por múltiplos papéis
     */
    public function findByRole(array $roles): array
    {
        $columns = $this->getSelectColumns();
        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $sql = "SELECT {$columns} FROM {$this->table} WHERE role IN ($placeholders) ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($roles);
        return $stmt->fetchAll();
    }

    public function updatePassword(int $id, string $hash): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET password_hash = :hash, updated_at = :updated WHERE id = :id");
        return $stmt->execute([
            'hash' => $hash,
            'updated' => now(),
            'id' => $id,
        ]);
    }

    public function setSupervisorEligibility(int $id, bool $eligible): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET supervisor_eligible = :eligible, updated_at = :updated WHERE id = :id");
        return $stmt->execute([
            'eligible' => $eligible ? 1 : 0,
            'updated' => now(),
            'id' => $id,
        ]);
    }

    public function getVigilantesWithWorkload(): array
    {
        $columns = implode(', ', array_map(fn($col) => "u.{$col}", $this->selectColumns));
        $sql = "SELECT {$columns}, 
                COUNT(jv.id) as jury_count,
                GROUP_CONCAT(DISTINCT j.subject ORDER BY j.exam_date SEPARATOR ', ') as subjects
                FROM {$this->table} u
                LEFT JOIN jury_vigilantes jv ON jv.vigilante_id = u.id
                LEFT JOIN juries j ON j.id = jv.jury_id
                WHERE u.role = 'vigilante' AND u.available_for_vigilance = 1
                GROUP BY u.id
                ORDER BY jury_count ASC, u.name";
        return $this->statement($sql);
    }

    /**
     * Verificar se perfil do vigilante está completo
     */
    public function isProfileComplete(array $user): bool
    {
        // Campos obrigatórios conforme definido no Wizard
        $requiredFields = ['phone', 'nuit', 'nib', 'bank_name', 'bank_account_holder'];

        foreach ($requiredFields as $field) {
            if (empty($user[$field]) || trim($user[$field]) === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Obter campos faltantes do perfil
     */
    public function getMissingProfileFields(array $user): array
    {
        $requiredFields = [
            'phone' => 'Telefone',
            'nuit' => 'NUIT',
            'nib' => 'NIB',
            'bank_name' => 'Banco',
            'bank_account_holder' => 'Titular da Conta',
        ];

        $missing = [];
        foreach ($requiredFields as $field => $label) {
            if (empty($user[$field]) || trim($user[$field]) === '') {
                $missing[$field] = $label;
            }
        }

        return $missing;
    }

    /**
     * Marcar perfil como completo
     */
    public function markProfileComplete(int $id): bool
    {
        // Atualiza ambas as tabelas (legado e novo)
        $stmt = $this->db->prepare("UPDATE {$this->table} SET profile_completed = 1, profile_complete = 1, profile_completed_at = :completed, updated_at = :updated WHERE id = :id");
        return $stmt->execute([
            'completed' => now(),
            'updated' => now(),
            'id' => $id,
        ]);
    }

    /**
     * Verificar e atualizar status do perfil
     */
    public function checkAndUpdateProfileStatus(int $id): bool
    {
        $user = $this->find($id);
        if (!$user) {
            return false;
        }

        $isComplete = $this->isProfileComplete($user);

        // Se está completo mas marcado como incompleto na BD (em qualquer coluna)
        if ($isComplete && (!$user['profile_completed'] || empty($user['profile_complete']))) {
            return $this->markProfileComplete($id);
        }

        // Se está incompleto mas marcado como completo
        if (!$isComplete && ($user['profile_completed'] || !empty($user['profile_complete']))) {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET profile_completed = 0, profile_complete = 0, profile_completed_at = NULL, updated_at = :updated WHERE id = :id");
            return $stmt->execute([
                'updated' => now(),
                'id' => $id,
            ]);
        }

        return true;
    }

    /**
     * Definir palavra-chave de recuperação
     */
    public function setRecoveryKeyword(int $userId, string $keyword): bool
    {
        // Normalizar: lowercase, trim
        $normalized = mb_strtolower(trim($keyword));
        $hash = password_hash($normalized, PASSWORD_DEFAULT);

        return $this->update($userId, [
            'recovery_keyword_hash' => $hash,
            'updated_at' => now()
        ]);
    }

    /**
     * Definir PIN de recuperação
     */
    public function setRecoveryPin(int $userId, string $pin): bool
    {
        $hash = password_hash(trim($pin), PASSWORD_DEFAULT);

        return $this->update($userId, [
            'recovery_pin_hash' => $hash,
            'updated_at' => now()
        ]);
    }

    /**
     * Verificar quais métodos o utilizador tem configurado
     */
    public function getRecoveryMethods(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT recovery_keyword_hash, recovery_pin_hash FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        if (!$user)
            return [];

        $methods = [];
        if (!empty($user['recovery_keyword_hash']))
            $methods['keyword'] = true;
        if (!empty($user['recovery_pin_hash']))
            $methods['pin'] = true;

        // Verificar perguntas
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM user_security_answers WHERE user_id = :id");
        $stmt->execute(['id' => $userId]);
        if ($stmt->fetchColumn() > 0) {
            $methods['questions'] = true;
        }

        return $methods;
    }

    /**
     * Verificar se tem pelo menos um método configurado
     */
    public function hasConfiguredRecovery(int $userId): bool
    {
        $methods = $this->getRecoveryMethods($userId);
        return !empty($methods);
    }

    /**
     * Validar Palavra-chave
     */
    public function verifyRecoveryKeyword(int $userId, string $input): bool
    {
        $stmt = $this->db->prepare("SELECT recovery_keyword_hash FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $hash = $stmt->fetchColumn();

        if (!$hash)
            return false;

        $normalized = mb_strtolower(trim($input));
        return password_verify($normalized, $hash);
    }

    /**
     * Validar PIN
     */
    public function verifyRecoveryPin(int $userId, string $input): bool
    {
        $stmt = $this->db->prepare("SELECT recovery_pin_hash FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $hash = $stmt->fetchColumn();

        if (!$hash)
            return false;

        return password_verify(trim($input), $hash);
    }
}