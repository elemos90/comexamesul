<?php

namespace App\Models;

class AvailabilityChangeRequest extends BaseModel
{
    protected string $table = 'availability_change_requests';
    
    protected array $fillable = [
        'vigilante_id',
        'application_id',
        'request_type',
        'reason',
        'attachment_path',
        'attachment_original_name',
        'has_allocation',
        'jury_details',
        'status',
        'reviewed_at',
        'reviewed_by',
        'reviewer_notes',
        'created_at',
        'updated_at',
    ];

    /**
     * Criar solicitação de mudança
     */
    public function createRequest(array $data): int
    {
        $data['status'] = 'pendente';
        $data['created_at'] = now();
        $data['updated_at'] = now();
        
        return $this->create($data);
    }

    /**
     * Buscar solicitações de um vigilante
     */
    public function getByVigilante(int $vigilanteId): array
    {
        $sql = "SELECT acr.*, 
                       va.vacancy_id,
                       v.title as vacancy_title,
                       reviewer.name as reviewed_by_name
                FROM {$this->table} acr
                INNER JOIN vacancy_applications va ON acr.application_id = va.id
                INNER JOIN exam_vacancies v ON va.vacancy_id = v.id
                LEFT JOIN users reviewer ON acr.reviewed_by = reviewer.id
                WHERE acr.vigilante_id = :vigilante_id
                ORDER BY acr.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['vigilante_id' => $vigilanteId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Buscar solicitações pendentes para coordenador
     */
    public function getPending(): array
    {
        $sql = "SELECT acr.*, 
                       u.name as vigilante_name,
                       u.email as vigilante_email,
                       u.phone as vigilante_phone,
                       va.vacancy_id,
                       v.title as vacancy_title
                FROM {$this->table} acr
                INNER JOIN users u ON acr.vigilante_id = u.id
                INNER JOIN vacancy_applications va ON acr.application_id = va.id
                INNER JOIN exam_vacancies v ON va.vacancy_id = v.id
                WHERE acr.status = 'pendente'
                ORDER BY acr.has_allocation DESC, acr.created_at ASC";
        
        return $this->statement($sql);
    }

    /**
     * Aprovar solicitação
     */
    public function approve(int $requestId, int $reviewerId, ?string $notes = null): bool
    {
        return $this->update($requestId, [
            'status' => 'aprovada',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
            'reviewer_notes' => $notes,
            'updated_at' => now(),
        ]);
    }

    /**
     * Rejeitar solicitação
     */
    public function reject(int $requestId, int $reviewerId, string $notes): bool
    {
        return $this->update($requestId, [
            'status' => 'rejeitada',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
            'reviewer_notes' => $notes,
            'updated_at' => now(),
        ]);
    }

    /**
     * Verificar se vigilante tem solicitação pendente
     */
    public function hasPendingRequest(int $vigilanteId, int $applicationId): bool
    {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE vigilante_id = :vigilante_id 
                  AND application_id = :application_id 
                  AND status = 'pendente'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'vigilante_id' => $vigilanteId,
            'application_id' => $applicationId
        ]);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Contar solicitações por status
     */
    public function countByStatus(): array
    {
        $sql = "SELECT status, COUNT(*) as count
                FROM {$this->table}
                GROUP BY status";
        
        $results = $this->statement($sql);
        $counts = [
            'pendente' => 0,
            'aprovada' => 0,
            'rejeitada' => 0,
        ];
        
        foreach ($results as $row) {
            $counts[$row['status']] = (int) $row['count'];
        }
        
        return $counts;
    }
}
