<?php

namespace App\Models;

class Discipline extends BaseModel
{
    protected string $table = 'disciplines';
    
    protected array $selectColumns = [
        'id', 'code', 'name', 'description', 'active',
        'created_by', 'created_at', 'updated_at'
    ];
    
    protected array $fillable = [
        'code',
        'name',
        'description',
        'active',
        'created_by',
        'created_at',
        'updated_at',
    ];

    /**
     * Buscar todas as disciplinas ativas
     */
    public function getActive(): array
    {
        $columns = $this->getSelectColumns();
        return $this->statement(
            "SELECT {$columns} FROM {$this->table} WHERE active = 1 ORDER BY name ASC"
        );
    }

    /**
     * Buscar disciplina por código
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
     * Ativar/desativar disciplina
     */
    public function toggleActive(int $id): bool
    {
        $discipline = $this->find($id);
        if (!$discipline) {
            return false;
        }
        
        $newStatus = $discipline['active'] == 1 ? 0 : 1;
        return $this->update($id, ['active' => $newStatus, 'updated_at' => now()]);
    }

    /**
     * Buscar disciplinas com contagem de júris
     */
    public function withJuryCount(): array
    {
        return $this->statement(
            "SELECT d.*, 
                    COUNT(j.id) as jury_count,
                    u.name as created_by_name
             FROM {$this->table} d
             LEFT JOIN juries j ON j.discipline_id = d.id
             LEFT JOIN users u ON u.id = d.created_by
             GROUP BY d.id
             ORDER BY d.active DESC, d.name ASC"
        );
    }
}
