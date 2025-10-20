<?php

namespace App\Models;

class JuryVigilante extends BaseModel
{
    protected string $table = 'jury_vigilantes';
    protected array $fillable = [
        'jury_id',
        'vigilante_id',
        'assigned_by',
        'created_at',
    ];

    public function vigilantesForJury(int $juryId): array
    {
        $sql = "SELECT jv.*, u.* FROM jury_vigilantes jv
                INNER JOIN users u ON u.id = jv.vigilante_id
                WHERE jv.jury_id = :jury ORDER BY u.name";
        return $this->statement($sql, ['jury' => $juryId]);
    }
    
    /**
     * EAGER LOADING: Carregar vigilantes para múltiplos júris de uma vez
     * Resolve problema N+1 queries
     * 
     * @param array $juryIds Array de IDs dos júris
     * @return array Vigilantes agrupados por jury_id
     */
    public function getVigilantesForMultipleJuries(array $juryIds): array
    {
        if (empty($juryIds)) {
            return [];
        }
        
        // Criar placeholders para IN clause
        $placeholders = implode(',', array_fill(0, count($juryIds), '?'));
        
        $sql = "
            SELECT 
                jv.jury_id,
                jv.id as allocation_id,
                jv.papel as allocation_role,
                jv.created_at as allocated_at,
                u.id,
                u.name,
                u.email,
                u.phone,
                u.role as user_role
            FROM jury_vigilantes jv
            INNER JOIN users u ON u.id = jv.vigilante_id
            WHERE jv.jury_id IN ($placeholders)
            ORDER BY jv.jury_id, u.name
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($juryIds);
        
        return $stmt->fetchAll();
    }

    public function getByVigilante(int $vigilanteId): array
    {
        $sql = "SELECT jv.*, j.subject, j.exam_date, j.start_time, j.end_time, j.location, j.room
                FROM jury_vigilantes jv
                INNER JOIN juries j ON j.id = jv.jury_id
                WHERE jv.vigilante_id = :vigilante
                ORDER BY j.exam_date, j.start_time";
        return $this->statement($sql, ['vigilante' => $vigilanteId]);
    }

    public function vigilanteHasConflict(int $vigilanteId, string $date, string $start, string $end, ?int $ignoreJuryId = null): bool
    {
        $sql = "SELECT j.* FROM jury_vigilantes jv
                INNER JOIN juries j ON j.id = jv.jury_id
                WHERE jv.vigilante_id = :vigilante
                  AND j.exam_date = :date";
        $params = [
            'vigilante' => $vigilanteId,
            'date' => $date,
        ];

        if ($ignoreJuryId) {
            $sql .= " AND jv.jury_id <> :ignore";
            $params['ignore'] = $ignoreJuryId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        foreach ($items as $item) {
            if ($this->overlaps($start, $end, $item['start_time'], $item['end_time'])) {
                return true;
            }
        }

        return false;
    }

    private function overlaps(string $startA, string $endA, string $startB, string $endB): bool
    {
        return ($startA < $endB) && ($startB < $endA);
    }
}