<?php

namespace App\Models;

class LocationStats extends BaseModel
{
    protected string $table = 'location_stats';
    
    protected array $selectColumns = [
        'id', 'location', 'exam_date', 'total_juries', 'total_disciplines',
        'total_candidates', 'total_vigilantes', 'total_supervisors', 'updated_at'
    ];
    
    protected array $fillable = [
        'location',
        'exam_date',
        'total_juries',
        'total_disciplines',
        'total_candidates',
        'total_vigilantes',
        'total_supervisors',
        'updated_at',
    ];

    public function updateStatsForLocation(string $location, string $examDate): void
    {
        $sql = "SELECT 
                    COUNT(DISTINCT j.id) as total_juries,
                    COUNT(DISTINCT j.subject) as total_disciplines,
                    SUM(j.candidates_quota) as total_candidates,
                    COUNT(DISTINCT jv.vigilante_id) as total_vigilantes,
                    COUNT(DISTINCT j.supervisor_id) as total_supervisors
                FROM juries j
                LEFT JOIN jury_vigilantes jv ON jv.jury_id = j.id
                WHERE j.location = :location AND j.exam_date = :date";
        
        $stats = $this->statement($sql, ['location' => $location, 'date' => $examDate])[0] ?? null;
        
        if (!$stats) {
            return;
        }

        // Verificar se já existe registro
        $existing = $this->statement(
            "SELECT id FROM {$this->table} WHERE location = :location AND exam_date = :date",
            ['location' => $location, 'date' => $examDate]
        );

        if ($existing) {
            $this->update($existing[0]['id'], [
                'total_juries' => (int) ($stats['total_juries'] ?? 0),
                'total_disciplines' => (int) ($stats['total_disciplines'] ?? 0),
                'total_candidates' => (int) ($stats['total_candidates'] ?? 0),
                'total_vigilantes' => (int) ($stats['total_vigilantes'] ?? 0),
                'total_supervisors' => (int) ($stats['total_supervisors'] ?? 0),
                'updated_at' => now(),
            ]);
        } else {
            $this->create([
                'location' => $location,
                'exam_date' => $examDate,
                'total_juries' => (int) ($stats['total_juries'] ?? 0),
                'total_disciplines' => (int) ($stats['total_disciplines'] ?? 0),
                'total_candidates' => (int) ($stats['total_candidates'] ?? 0),
                'total_vigilantes' => (int) ($stats['total_vigilantes'] ?? 0),
                'total_supervisors' => (int) ($stats['total_supervisors'] ?? 0),
                'updated_at' => now(),
            ]);
        }
    }

    public function getAllStats(): array
    {
        $columns = $this->getSelectColumns();
        return $this->statement(
            "SELECT {$columns} FROM {$this->table} ORDER BY exam_date DESC, location"
        );
    }

    public function getStatsByDateRange(string $startDate, string $endDate): array
    {
        $columns = $this->getSelectColumns();
        return $this->statement(
            "SELECT {$columns} FROM {$this->table} 
             WHERE exam_date BETWEEN :start AND :end 
             ORDER BY exam_date DESC, location",
            ['start' => $startDate, 'end' => $endDate]
        );
    }

    public function getTopLocations(int $limit = 10): array
    {
        return $this->statement(
            "SELECT 
                location,
                SUM(total_juries) as total_juries,
                SUM(total_candidates) as total_candidates,
                SUM(total_vigilantes) as total_vigilantes,
                COUNT(DISTINCT exam_date) as exam_days
             FROM {$this->table}
             GROUP BY location
             ORDER BY total_candidates DESC
             LIMIT :limit",
            ['limit' => $limit]
        );
    }
}
