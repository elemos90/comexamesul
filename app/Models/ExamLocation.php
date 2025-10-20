<?php

namespace App\Models;

class ExamLocation extends BaseModel
{
    protected string $table = 'exam_locations';
    
    protected array $selectColumns = [
        'id', 'code', 'name', 'address', 'city', 'capacity',
        'description', 'active', 'created_by', 'created_at', 'updated_at'
    ];
    
    protected array $fillable = [
        'code',
        'name',
        'address',
        'city',
        'capacity',
        'description',
        'active',
        'created_by',
        'created_at',
        'updated_at',
    ];

    /**
     * Buscar todos os locais ativos
     */
    public function getActive(): array
    {
        $columns = $this->getSelectColumns();
        return $this->statement(
            "SELECT {$columns} FROM {$this->table} WHERE active = 1 ORDER BY name ASC"
        );
    }

    /**
     * Buscar local por código
     */
    public function findByCode(string $code): ?array
    {
        $columns = $this->getSelectColumns();
        $stmt = $this->db->prepare("SELECT {$columns} FROM {$this->table} WHERE code = :code LIMIT 1");
        $stmt->execute(['code' => $code]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Verificar se código já existe
     */
    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE code = :code";
        
        if ($excludeId) {
            $sql .= " AND id != :id";
        }
        
        $params = ['code' => $code];
        if ($excludeId) {
            $params['id'] = $excludeId;
        }
        
        $result = $this->statement($sql, $params);
        return ($result[0]['count'] ?? 0) > 0;
    }

    /**
     * Buscar locais com salas e júris
     */
    public function withDetails(): array
    {
        return $this->statement(
            "SELECT el.*, 
                    COUNT(DISTINCT er.id) as room_count,
                    COUNT(DISTINCT j.id) as jury_count,
                    SUM(CASE WHEN er.active = 1 THEN 1 ELSE 0 END) as active_rooms,
                    u.name as created_by_name
             FROM {$this->table} el
             LEFT JOIN exam_rooms er ON er.location_id = el.id
             LEFT JOIN juries j ON j.location_id = el.id
             LEFT JOIN users u ON u.id = el.created_by
             GROUP BY el.id
             ORDER BY el.active DESC, el.name ASC"
        );
    }

    /**
     * Ativar/desativar local
     */
    public function toggleActive(int $id): bool
    {
        $location = $this->find($id);
        if (!$location) {
            return false;
        }
        
        $newStatus = $location['active'] == 1 ? 0 : 1;
        return $this->update($id, ['active' => $newStatus, 'updated_at' => now()]);
    }
}
