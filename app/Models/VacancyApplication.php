<?php

namespace App\Models;

class VacancyApplication extends BaseModel
{
    protected string $table = 'vacancy_applications';
    
    protected array $fillable = [
        'vacancy_id',
        'vigilante_id',
        'status',
        'notes',
        'applied_at',
        'reviewed_at',
        'reviewed_by',
        'rejection_reason',
        'reapply_count',
        'created_at',
        'updated_at',
    ];

    /**
     * Buscar candidaturas de um vigilante
     */
    public function getByVigilante(int $vigilanteId): array
    {
        $sql = "SELECT va.*, 
                       v.title as vacancy_title,
                       v.deadline_at,
                       v.status as vacancy_status
                FROM {$this->table} va
                INNER JOIN exam_vacancies v ON va.vacancy_id = v.id
                WHERE va.vigilante_id = :vigilante_id
                ORDER BY va.applied_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['vigilante_id' => $vigilanteId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Buscar candidaturas de uma vaga
     * Nota: supervisor_eligible vem da tabela 'users', não de 'vacancy_applications'
     */
    public function getByVacancy(int $vacancyId, ?string $status = null): array
    {
        $sql = "SELECT va.*, 
                       u.name as vigilante_name,
                       u.email as vigilante_email,
                       u.phone as vigilante_phone,
                       u.supervisor_eligible,
                       reviewer.name as reviewed_by_name,
                       va.rejection_reason,
                       va.reapply_count
                FROM {$this->table} va
                INNER JOIN users u ON va.vigilante_id = u.id
                LEFT JOIN users reviewer ON va.reviewed_by = reviewer.id
                WHERE va.vacancy_id = :vacancy_id";
        
        $params = ['vacancy_id' => $vacancyId];
        
        if ($status) {
            $sql .= " AND va.status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY va.applied_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Verificar se vigilante já se candidatou a uma vaga
     */
    public function hasApplied(int $vacancyId, int $vigilanteId): bool
    {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE vacancy_id = :vacancy_id 
                  AND vigilante_id = :vigilante_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'vacancy_id' => $vacancyId,
            'vigilante_id' => $vigilanteId
        ]);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Candidatar-se a uma vaga
     */
    public function apply(int $vacancyId, int $vigilanteId, ?string $notes = null): int
    {
        $data = [
            'vacancy_id' => $vacancyId,
            'vigilante_id' => $vigilanteId,
            'status' => 'pendente',
            'notes' => $notes,
            'applied_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        return $this->create($data);
    }

    /**
     * Cancelar candidatura
     */
    public function cancelApplication(int $applicationId): bool
    {
        return $this->update($applicationId, [
            'status' => 'cancelada',
            'updated_at' => now(),
        ]);
    }

    /**
     * Aprovar candidatura
     */
    public function approve(int $applicationId, int $reviewerId): bool
    {
        return $this->update($applicationId, [
            'status' => 'aprovada',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
            'updated_at' => now(),
        ]);
    }

    /**
     * Rejeitar candidatura
     */
    public function reject(int $applicationId, int $reviewerId, ?string $rejectionReason = null): bool
    {
        return $this->update($applicationId, [
            'status' => 'rejeitada',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
            'rejection_reason' => $rejectionReason,
            'updated_at' => now(),
        ]);
    }

    /**
     * Buscar vigilantes disponíveis para uma vaga
     */
    public function getAvailableVigilantes(int $vacancyId): array
    {
        $sql = "SELECT u.id, u.name, u.email, u.phone, u.supervisor_eligible,
                       va.applied_at, va.status
                FROM users u
                INNER JOIN {$this->table} va ON u.id = va.vigilante_id
                WHERE va.vacancy_id = :vacancy_id
                  AND va.status IN ('pendente', 'aprovada')
                  AND u.role = 'vigilante'
                  AND u.profile_completed = 1
                ORDER BY va.applied_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['vacancy_id' => $vacancyId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Contar candidaturas por status
     */
    public function countByStatus(int $vacancyId): array
    {
        $sql = "SELECT status, COUNT(*) as count
                FROM {$this->table}
                WHERE vacancy_id = :vacancy_id
                GROUP BY status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['vacancy_id' => $vacancyId]);
        
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $counts = [
            'pendente' => 0,
            'aprovada' => 0,
            'rejeitada' => 0,
            'cancelada' => 0,
        ];
        
        foreach ($results as $row) {
            $counts[$row['status']] = (int) $row['count'];
        }
        
        return $counts;
    }
}
