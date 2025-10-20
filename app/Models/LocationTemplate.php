<?php

namespace App\Models;

class LocationTemplate extends BaseModel
{
    protected string $table = 'location_templates';
    
    protected array $selectColumns = [
        'id', 'name', 'location', 'description', 'is_active',
        'created_by', 'created_at', 'updated_at'
    ];
    
    protected array $fillable = [
        'name',
        'location',
        'description',
        'is_active',
        'created_by',
        'created_at',
        'updated_at',
    ];

    public function getActive(): array
    {
        $columns = $this->getSelectColumns();
        return $this->statement(
            "SELECT {$columns} FROM {$this->table} WHERE is_active = 1 ORDER BY name"
        );
    }

    public function withDetails(int $templateId): ?array
    {
        $template = $this->find($templateId);
        if (!$template) {
            return null;
        }

        // Buscar disciplinas
        $disciplines = $this->statement(
            "SELECT id, template_id, subject, start_time, end_time, display_order, created_at FROM location_template_disciplines WHERE template_id = :id ORDER BY display_order, subject",
            ['id' => $templateId]
        );

        // Buscar salas para cada disciplina
        foreach ($disciplines as &$discipline) {
            $discipline['rooms'] = $this->statement(
                "SELECT id, discipline_id, room, candidates_quota, created_at FROM location_template_rooms WHERE discipline_id = :id ORDER BY room",
                ['id' => $discipline['id']]
            );
        }
        unset($discipline);

        $template['disciplines'] = $disciplines;
        return $template;
    }

    public function getAllWithCounts(): array
    {
        $sql = "SELECT 
                    lt.*,
                    COUNT(DISTINCT ltd.id) as disciplines_count,
                    COUNT(ltr.id) as rooms_count,
                    SUM(ltr.candidates_quota) as total_capacity,
                    u.name as creator_name
                FROM location_templates lt
                LEFT JOIN location_template_disciplines ltd ON ltd.template_id = lt.id
                LEFT JOIN location_template_rooms ltr ON ltr.discipline_id = ltd.id
                LEFT JOIN users u ON u.id = lt.created_by
                GROUP BY lt.id
                ORDER BY lt.is_active DESC, lt.name";
        
        return $this->statement($sql);
    }

    public function createWithStructure(array $data, array $disciplines): int
    {
        $templateId = $this->create([
            'name' => $data['name'],
            'location' => $data['location'],
            'description' => $data['description'] ?? null,
            'is_active' => 1,
            'created_by' => $data['created_by'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $displayOrder = 0;
        foreach ($disciplines as $discipline) {
            $disciplineId = $this->execute(
                "INSERT INTO location_template_disciplines (template_id, subject, start_time, end_time, display_order, created_at) 
                 VALUES (:template, :subject, :start, :end, :order, :created)",
                [
                    'template' => $templateId,
                    'subject' => $discipline['subject'],
                    'start' => $discipline['start_time'],
                    'end' => $discipline['end_time'],
                    'order' => $displayOrder++,
                    'created' => now(),
                ]
            );
            $disciplineId = (int) $this->db->lastInsertId();

            if (!empty($discipline['rooms']) && is_array($discipline['rooms'])) {
                foreach ($discipline['rooms'] as $room) {
                    $this->execute(
                        "INSERT INTO location_template_rooms (discipline_id, room, candidates_quota, created_at) 
                         VALUES (:discipline, :room, :quota, :created)",
                        [
                            'discipline' => $disciplineId,
                            'room' => $room['room'],
                            'quota' => (int) $room['candidates_quota'],
                            'created' => now(),
                        ]
                    );
                }
            }
        }

        return $templateId;
    }

    public function toggleActive(int $id): bool
    {
        $template = $this->find($id);
        if (!$template) {
            return false;
        }
        
        $newStatus = (int) $template['is_active'] === 1 ? 0 : 1;
        $this->update($id, ['is_active' => $newStatus, 'updated_at' => now()]);
        return true;
    }
}
