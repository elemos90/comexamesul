<?php

namespace App\Models;

class ExamVacancy extends BaseModel
{
    protected string $table = 'exam_vacancies';
    
    protected array $selectColumns = [
        'id', 'title', 'description', 'deadline_at', 'status',
        'created_by', 'created_at', 'updated_at'
    ];
    
    protected array $fillable = [
        'title',
        'description',
        'deadline_at',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];

    public function openVacancies(): array
    {
        $columns = $this->getSelectColumns();
        $sql = "SELECT {$columns} FROM {$this->table} WHERE status = 'aberta' ORDER BY deadline_at ASC";
        return $this->statement($sql);
    }

    public function closeExpired(): int
    {
        $sql = "UPDATE {$this->table} SET status = 'fechada', updated_at = :updated WHERE status = 'aberta' AND deadline_at < :now";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'updated' => now(),
            'now' => now(),
        ]);
        return $stmt->rowCount();
    }
}
