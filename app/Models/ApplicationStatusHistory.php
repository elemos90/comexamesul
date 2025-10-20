<?php

namespace App\Models;

class ApplicationStatusHistory extends BaseModel
{
    protected string $table = 'application_status_history';
    
    protected array $fillable = [
        'application_id',
        'old_status',
        'new_status',
        'changed_by',
        'changed_at',
        'reason',
        'metadata',
    ];

    /**
     * Buscar histórico de uma candidatura
     */
    public function getByApplication(int $applicationId): array
    {
        $sql = "SELECT h.*, 
                       u.name as changed_by_name,
                       u.email as changed_by_email
                FROM {$this->table} h
                LEFT JOIN users u ON h.changed_by = u.id
                WHERE h.application_id = :application_id
                ORDER BY h.changed_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['application_id' => $applicationId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Registrar mudança de status manual
     */
    public function recordChange(int $applicationId, ?string $oldStatus, string $newStatus, ?int $changedBy = null, ?string $reason = null, ?array $metadata = null): int
    {
        $data = [
            'application_id' => $applicationId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $changedBy,
            'changed_at' => now(),
            'reason' => $reason,
            'metadata' => $metadata ? json_encode($metadata) : null,
        ];
        
        return $this->create($data);
    }

    /**
     * Contar mudanças de status de uma candidatura
     */
    public function countChanges(int $applicationId): int
    {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE application_id = :application_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['application_id' => $applicationId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Buscar última mudança de uma candidatura
     */
    public function getLastChange(int $applicationId): ?array
    {
        $sql = "SELECT h.*, 
                       u.name as changed_by_name
                FROM {$this->table} h
                LEFT JOIN users u ON h.changed_by = u.id
                WHERE h.application_id = :application_id
                ORDER BY h.changed_at DESC
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['application_id' => $applicationId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    /**
     * Buscar histórico de um vigilante
     */
    public function getByVigilante(int $vigilanteId): array
    {
        $sql = "SELECT h.*, 
                       va.vacancy_id,
                       v.title as vacancy_title,
                       u.name as changed_by_name
                FROM {$this->table} h
                INNER JOIN vacancy_applications va ON h.application_id = va.id
                INNER JOIN exam_vacancies v ON va.vacancy_id = v.id
                LEFT JOIN users u ON h.changed_by = u.id
                WHERE va.vigilante_id = :vigilante_id
                ORDER BY h.changed_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['vigilante_id' => $vigilanteId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
