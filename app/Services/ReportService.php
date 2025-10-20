<?php

namespace App\Services;

use App\Database\Connection;
use PDO;

class ReportService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function availableVigilantes(array $filters = []): array
    {
        $sql = "SELECT u.* FROM users u WHERE u.role = 'vigilante' AND u.available_for_vigilance = 1";
        $params = [];
        if (!empty($filters['name'])) {
            $sql .= " AND u.name LIKE :name";
            $params['name'] = '%' . $filters['name'] . '%';
        }
        if (!empty($filters['degree'])) {
            $sql .= " AND u.degree = :degree";
            $params['degree'] = $filters['degree'];
        }
        $sql .= ' ORDER BY u.name';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function supervisorsByJury(): array
    {
        $sql = "SELECT j.subject, j.exam_date, j.start_time, j.end_time, j.location, j.room, u.name AS supervisor_name
                FROM juries j
                LEFT JOIN users u ON u.id = j.supervisor_id
                ORDER BY j.exam_date, j.start_time";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function consolidatedVigias(): array
    {
        $sql = "SELECT j.id, j.subject, j.exam_date, j.start_time, j.end_time,
                       r.present_m, r.present_f, r.absent_m, r.absent_f, r.total
                FROM juries j
                LEFT JOIN exam_reports r ON r.jury_id = j.id
                ORDER BY j.exam_date, j.start_time";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
