<?php

namespace App\Models;

use PDO;

class User extends BaseModel
{
    protected string $table = 'users';
    
    // Colunas seguras para SELECT (NUNCA incluir password_hash aqui!)
    protected array $selectColumns = [
        'id', 'name', 'email', 'phone', 'gender',
        'origin_university', 'university', 'nuit',
        'degree', 'major_area', 'bank_name', 'nib',
        'role', 'email_verified_at', 'verification_token', 'avatar_url',
        'supervisor_eligible', 'available_for_vigilance',
        'profile_completed', 'profile_completed_at',
        'created_by', 'created_at', 'updated_at'
    ];
    
    protected array $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'origin_university',
        'university',
        'nuit',
        'degree',
        'major_area',
        'bank_name',
        'nib',
        'role',
        'password_hash',
        'email_verified_at',
        'verification_token',
        'avatar_url',
        'supervisor_eligible',
        'available_for_vigilance',
        'profile_completed',
        'profile_completed_at',
        'created_by',
        'updated_at',
        'created_at',
    ];

    public function findByEmail(string $email): ?array
    {
        // Incluir password_hash apenas para autenticação
        $stmt = $this->db->prepare(
            "SELECT id, name, email, phone, gender, origin_university, university, nuit, 
                    degree, major_area, bank_name, nib, role, password_hash, email_verified_at, 
                    verification_token, avatar_url, supervisor_eligible, available_for_vigilance, 
                    profile_completed, profile_completed_at, created_by, created_at, updated_at 
             FROM {$this->table} WHERE email = :email LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
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
        $sql = "SELECT {$columns} FROM {$this->table} WHERE supervisor_eligible = 1";
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
        $requiredFields = ['phone', 'nuit', 'nib', 'bank_name'];
        
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
        $stmt = $this->db->prepare("UPDATE {$this->table} SET profile_completed = 1, profile_completed_at = :completed, updated_at = :updated WHERE id = :id");
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
        
        if ($isComplete && !$user['profile_completed']) {
            return $this->markProfileComplete($id);
        }
        
        if (!$isComplete && $user['profile_completed']) {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET profile_completed = 0, profile_completed_at = NULL, updated_at = :updated WHERE id = :id");
            return $stmt->execute([
                'updated' => now(),
                'id' => $id,
            ]);
        }

        return true;
    }
}