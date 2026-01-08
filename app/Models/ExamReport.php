<?php

namespace App\Models;

class ExamReport extends BaseModel
{
    protected string $table = 'exam_reports';

    protected array $selectColumns = [
        'id',
        'jury_id',
        'supervisor_id',
        'role',
        'present_m',
        'present_f',
        'absent_m',
        'absent_f',
        'fraudes_m',
        'fraudes_f',
        'total',
        'occurrences',
        'submitted_at',
        'created_at',
        'updated_at'
    ];

    protected array $fillable = [
        'jury_id',
        'supervisor_id',
        'role',
        'present_m',
        'present_f',
        'absent_m',
        'absent_f',
        'fraudes_m',
        'fraudes_f',
        'total',
        'occurrences',
        'submitted_at',
        'created_at',
        'updated_at',
    ];

    public function findByJury(int $juryId): ?array
    {
        $columns = $this->getSelectColumns();
        $stmt = $this->db->prepare("SELECT {$columns} FROM {$this->table} WHERE jury_id = :jury LIMIT 1");
        $stmt->execute(['jury' => $juryId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
