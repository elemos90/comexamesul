<?php

namespace App\Models;

class Jury extends BaseModel
{
    protected string $table = 'juries';
    protected array $fillable = [
        'vacancy_id',
        'subject',
        'exam_date',
        'start_time',
        'end_time',
        'location',
        'room',
        'candidates_quota',
        'notes',
        'supervisor_id',
        'approved_by',
        'created_by',
        'created_at',
        'updated_at',
    ];

    public function withAllocations(): array
    {
        $sql = "SELECT j.*, 
                       s.name AS supervisor_name,
                       s.phone AS supervisor_phone,
                       er.name as room_name,
                       er.code as room_code,
                       er.capacity as room_capacity,
                       er.floor as room_floor,
                       er.building as room_building,
                       COALESCE(el.name, j.location) as location
                FROM juries j
                LEFT JOIN users s ON s.id = j.supervisor_id
                LEFT JOIN exam_rooms er ON er.id = j.room_id
                LEFT JOIN exam_locations el ON el.id = er.location_id
                WHERE j.vacancy_id IS NOT NULL
                ORDER BY j.subject, j.exam_date, j.start_time, j.room";
        return $this->statement($sql);
    }
    
    public function getGroupedBySubjectAndTime(): array
    {
        $juries = $this->withAllocations();
        $grouped = [];
        
        foreach ($juries as $jury) {
            // Agrupar por disciplina, data, horário E local
            // Isso permite que o mesmo local tenha várias disciplinas no mesmo dia
            $key = $jury['subject'] . '|' . $jury['exam_date'] . '|' . $jury['start_time'] . '|' . $jury['end_time'] . '|' . $jury['location'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'subject' => $jury['subject'],
                    'exam_date' => $jury['exam_date'],
                    'start_time' => $jury['start_time'],
                    'end_time' => $jury['end_time'],
                    'location' => $jury['location'],
                    'juries' => []
                ];
            }
            $grouped[$key]['juries'][] = $jury;
        }
        
        return array_values($grouped);
    }
    
    public function getGroupedByLocationAndDate(): array
    {
        $juries = $this->withAllocations();
        $locationGroups = [];
        
        // Primeiro nível: Agrupar por local e data
        foreach ($juries as $jury) {
            $locationKey = $jury['location'] . '|' . $jury['exam_date'];
            if (!isset($locationGroups[$locationKey])) {
                $locationGroups[$locationKey] = [
                    'location' => $jury['location'],
                    'exam_date' => $jury['exam_date'],
                    'disciplines' => []
                ];
            }
            
            // Segundo nível: Agrupar por disciplina e horário dentro do local
            $disciplineKey = $jury['subject'] . '|' . $jury['start_time'] . '|' . $jury['end_time'];
            if (!isset($locationGroups[$locationKey]['disciplines'][$disciplineKey])) {
                $locationGroups[$locationKey]['disciplines'][$disciplineKey] = [
                    'subject' => $jury['subject'],
                    'start_time' => $jury['start_time'],
                    'end_time' => $jury['end_time'],
                    'juries' => []
                ];
            }
            
            $locationGroups[$locationKey]['disciplines'][$disciplineKey]['juries'][] = $jury;
        }
        
        // Converter arrays associativos em numéricos
        $result = [];
        foreach ($locationGroups as $locationGroup) {
            $locationGroup['disciplines'] = array_values($locationGroup['disciplines']);
            $result[] = $locationGroup;
        }
        
        return $result;
    }

    public function vigilantesForJury(int $juryId): array
    {
        $sql = "SELECT u.* FROM jury_vigilantes jv
                INNER JOIN users u ON u.id = jv.vigilante_id
                WHERE jv.jury_id = :jury
                ORDER BY u.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['jury' => $juryId]);
        return $stmt->fetchAll();
    }

    public function hasSupervisorReport(int $juryId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM exam_reports WHERE jury_id = :jury");
        $stmt->execute(['jury' => $juryId]);
        return (int) $stmt->fetchColumn() > 0;
    }
    
    /**
     * Buscar júris de uma vaga específica
     */
    public function getByVacancy(int $vacancyId): array
    {
        $sql = "SELECT j.*, 
                       s.name AS supervisor_name,
                       s.phone AS supervisor_phone,
                       er.name as room_name,
                       er.code as room_code,
                       er.capacity as room_capacity,
                       er.floor as room_floor,
                       er.building as room_building,
                       COALESCE(el.name, j.location) as location,
                       COALESCE(el.name, j.location) as location_name
                FROM juries j
                LEFT JOIN users s ON s.id = j.supervisor_id
                LEFT JOIN exam_rooms er ON er.id = j.room_id
                LEFT JOIN exam_locations el ON el.id = er.location_id
                WHERE j.vacancy_id = :vacancy
                ORDER BY j.exam_date, j.start_time, j.subject, j.room";
        return $this->statement($sql, ['vacancy' => $vacancyId]);
    }
    
    /**
     * CORREÇÃO #6: Buscar júris com estatísticas pré-carregadas (evita N+1)
     */
    public function getByVacancyWithStats(int $vacancyId): array
    {
        $sql = "SELECT 
                    j.*,
                    s.name AS supervisor_name,
                    s.phone AS supervisor_phone,
                    COUNT(DISTINCT jv.id) as vigilantes_allocated,
                    CEIL(j.candidates_quota / 30) as required_vigilantes
                FROM juries j
                LEFT JOIN users s ON s.id = j.supervisor_id
                LEFT JOIN jury_vigilantes jv ON jv.jury_id = j.id
                WHERE j.vacancy_id = :vacancy
                GROUP BY j.id
                ORDER BY j.exam_date, j.start_time, j.subject, j.room";
        
        $juries = $this->statement($sql, ['vacancy' => $vacancyId]);
        
        // Adicionar flag de completude
        foreach ($juries as &$jury) {
            $jury['is_complete'] = (int)$jury['vigilantes_allocated'] >= (int)$jury['required_vigilantes'];
        }
        
        return $juries;
    }
    
    /**
     * Calcular número de vigilantes necessários
     * Regra: 1 vigilante por 30 candidatos
     */
    public function calculateRequiredVigilantes(int $candidatesQuota): int
    {
        return (int) ceil($candidatesQuota / 30);
    }
    
    /**
     * Buscar júris agrupados por vaga, local e data
     */
    public function getGroupedByVacancy(int $vacancyId): array
    {
        $juries = $this->getByVacancy($vacancyId);
        $grouped = [];
        
        foreach ($juries as $jury) {
            $key = $jury['location'] . '|' . $jury['exam_date'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'location' => $jury['location'],
                    'exam_date' => $jury['exam_date'],
                    'disciplines' => []
                ];
            }
            
            $disciplineKey = $jury['subject'] . '|' . $jury['start_time'] . '|' . $jury['end_time'];
            if (!isset($grouped[$key]['disciplines'][$disciplineKey])) {
                $grouped[$key]['disciplines'][$disciplineKey] = [
                    'subject' => $jury['subject'],
                    'start_time' => $jury['start_time'],
                    'end_time' => $jury['end_time'],
                    'juries' => []
                ];
            }
            
            $grouped[$key]['disciplines'][$disciplineKey]['juries'][] = $jury;
        }
        
        // Converter para arrays numéricos
        $result = [];
        foreach ($grouped as $locationGroup) {
            $locationGroup['disciplines'] = array_values($locationGroup['disciplines']);
            $result[] = $locationGroup;
        }
        
        return $result;
    }
}
