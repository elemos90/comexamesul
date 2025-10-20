<?php

namespace App\Models;

class ExamRoom extends BaseModel
{
    protected string $table = 'exam_rooms';
    
    protected array $selectColumns = [
        'id', 'location_id', 'code', 'name', 'capacity', 'floor',
        'building', 'notes', 'active', 'created_by', 'created_at', 'updated_at'
    ];
    
    protected array $fillable = [
        'location_id',
        'code',
        'name',
        'capacity',
        'floor',
        'building',
        'notes',
        'active',
        'created_by',
        'created_at',
        'updated_at',
    ];

    /**
     * Buscar salas por local
     */
    public function getByLocation(int $locationId, bool $activeOnly = true): array
    {
        $columns = $this->getSelectColumns();
        $sql = "SELECT {$columns} FROM {$this->table} WHERE location_id = :location_id";
        
        if ($activeOnly) {
            $sql .= " AND active = 1";
        }
        
        $sql .= " ORDER BY code ASC";
        
        return $this->statement($sql, ['location_id' => $locationId]);
    }

    /**
     * Buscar sala com informações do local
     */
    public function findWithLocation(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT er.*, 
                    el.name as location_name,
                    el.code as location_code
             FROM {$this->table} er
             INNER JOIN exam_locations el ON el.id = er.location_id
             WHERE er.id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Verificar se código da sala já existe no local
     */
    public function codeExistsInLocation(int $locationId, string $code, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE location_id = :location_id AND code = :code";
        
        if ($excludeId) {
            $sql .= " AND id != :id";
        }
        
        $params = [
            'location_id' => $locationId,
            'code' => $code
        ];
        
        if ($excludeId) {
            $params['id'] = $excludeId;
        }
        
        $result = $this->statement($sql, $params);
        return ($result[0]['count'] ?? 0) > 0;
    }

    /**
     * Verificar se sala está disponível em determinado horário
     */
    public function isAvailable(int $roomId, string $date, string $startTime, string $endTime, ?int $excludeJuryId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM juries 
                WHERE room_id = :room_id 
                  AND exam_date = :date
                  AND (start_time < :end_time AND end_time > :start_time)";
        
        if ($excludeJuryId) {
            $sql .= " AND id != :exclude_id";
        }
        
        $params = [
            'room_id' => $roomId,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime
        ];
        
        if ($excludeJuryId) {
            $params['exclude_id'] = $excludeJuryId;
        }
        
        $result = $this->statement($sql, $params);
        return ($result[0]['count'] ?? 0) === 0;
    }

    /**
     * Buscar salas com contagem de júris
     */
    public function withJuryCount(int $locationId): array
    {
        return $this->statement(
            "SELECT er.*, 
                    COUNT(j.id) as jury_count,
                    u.name as created_by_name
             FROM {$this->table} er
             LEFT JOIN juries j ON j.room_id = er.id
             LEFT JOIN users u ON u.id = er.created_by
             WHERE er.location_id = :location_id
             GROUP BY er.id
             ORDER BY er.active DESC, er.code ASC",
            ['location_id' => $locationId]
        );
    }

    /**
     * Ativar/desativar sala
     */
    public function toggleActive(int $id): bool
    {
        $room = $this->find($id);
        if (!$room) {
            return false;
        }
        
        $newStatus = $room['active'] == 1 ? 0 : 1;
        return $this->update($id, ['active' => $newStatus, 'updated_at' => now()]);
    }

    /**
     * Buscar todas as salas com informações do local
     */
    public function getAllWithLocation(bool $activeOnly = true): array
    {
        $sql = "SELECT er.*, 
                       el.name as location_name,
                       el.code as location_code
                FROM {$this->table} er
                INNER JOIN exam_locations el ON el.id = er.location_id";
        
        if ($activeOnly) {
            $sql .= " WHERE er.active = 1 AND el.active = 1";
        }
        
        $sql .= " ORDER BY el.name ASC, er.code ASC";
        
        return $this->statement($sql);
    }
}
