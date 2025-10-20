<?php

namespace App\Services;

use App\Models\VacancyApplication;
use App\Models\ApplicationStatusHistory;

class ApplicationStatsService
{
    private $db;

    public function __construct()
    {
        $model = new VacancyApplication();
        $this->db = $model->getDb();
    }

    /**
     * Obter estatísticas gerais de candidaturas
     */
    public function getGeneralStats(): array
    {
        // SELECT * seguro: v_application_stats é uma VIEW com campos específicos
        $sql = "SELECT * FROM v_application_stats LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $stats ?: [
            'total_applications' => 0,
            'pending_count' => 0,
            'approved_count' => 0,
            'rejected_count' => 0,
            'cancelled_count' => 0,
            'approval_rate' => 0,
            'avg_review_hours' => 0,
            'total_reapplies' => 0,
        ];
    }

    /**
     * Obter candidaturas por dia (últimos 30 dias)
     */
    public function getApplicationsByDay(int $days = 30): array
    {
        // SELECT * seguro: v_applications_by_day é uma VIEW com campos específicos
        $sql = "SELECT * FROM v_applications_by_day 
                WHERE date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                ORDER BY date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obter top vigilantes mais ativos
     */
    public function getTopVigilantes(int $limit = 10): array
    {
        // SELECT * seguro: v_top_vigilantes é uma VIEW com campos específicos
        $sql = "SELECT * FROM v_top_vigilantes LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obter distribuição de status
     */
    public function getStatusDistribution(): array
    {
        $stats = $this->getGeneralStats();
        $total = $stats['total_applications'];

        if ($total == 0) {
            return [];
        }

        return [
            [
                'status' => 'Aprovadas',
                'count' => $stats['approved_count'],
                'percentage' => round(($stats['approved_count'] / $total) * 100, 1),
                'color' => '#10b981',
            ],
            [
                'status' => 'Pendentes',
                'count' => $stats['pending_count'],
                'percentage' => round(($stats['pending_count'] / $total) * 100, 1),
                'color' => '#f59e0b',
            ],
            [
                'status' => 'Rejeitadas',
                'count' => $stats['rejected_count'],
                'percentage' => round(($stats['rejected_count'] / $total) * 100, 1),
                'color' => '#ef4444',
            ],
            [
                'status' => 'Canceladas',
                'count' => $stats['cancelled_count'],
                'percentage' => round(($stats['cancelled_count'] / $total) * 100, 1),
                'color' => '#6b7280',
            ],
        ];
    }

    /**
     * Obter estatísticas por vaga
     */
    public function getStatsByVacancy(int $vacancyId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'aprovada' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejeitada' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN status = 'cancelada' THEN 1 ELSE 0 END) as cancelled,
                    SUM(reapply_count) as total_reapplies
                FROM vacancy_applications
                WHERE vacancy_id = :vacancy_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['vacancy_id' => $vacancyId]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Obter tempo médio de revisão por coordenador
     */
    public function getAvgReviewTimeByCoordinator(): array
    {
        $sql = "SELECT 
                    u.id,
                    u.name,
                    COUNT(va.id) as reviews_count,
                    ROUND(AVG(TIMESTAMPDIFF(HOUR, va.applied_at, va.reviewed_at)), 1) as avg_hours
                FROM users u
                INNER JOIN vacancy_applications va ON u.id = va.reviewed_by
                WHERE va.reviewed_at IS NOT NULL
                GROUP BY u.id, u.name
                ORDER BY reviews_count DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obter taxa de conversão (candidaturas → aprovações)
     */
    public function getConversionRate(): float
    {
        $stats = $this->getGeneralStats();
        $total = $stats['approved_count'] + $stats['rejected_count'];
        
        if ($total == 0) {
            return 0;
        }

        return round(($stats['approved_count'] / $total) * 100, 2);
    }

    /**
     * Obter candidaturas que precisam de revisão urgente (>48h pendentes)
     */
    public function getUrgentPendingApplications(): array
    {
        $sql = "SELECT 
                    va.*,
                    v.title as vacancy_title,
                    u.name as vigilante_name,
                    TIMESTAMPDIFF(HOUR, va.applied_at, NOW()) as hours_pending
                FROM vacancy_applications va
                INNER JOIN exam_vacancies v ON va.vacancy_id = v.id
                INNER JOIN users u ON va.vigilante_id = u.id
                WHERE va.status = 'pendente'
                  AND va.applied_at < DATE_SUB(NOW(), INTERVAL 48 HOUR)
                ORDER BY va.applied_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obter motivos de rejeição mais comuns
     */
    public function getTopRejectionReasons(int $limit = 5): array
    {
        $sql = "SELECT 
                    rejection_reason,
                    COUNT(*) as count
                FROM vacancy_applications
                WHERE status = 'rejeitada'
                  AND rejection_reason IS NOT NULL
                  AND rejection_reason != ''
                GROUP BY rejection_reason
                ORDER BY count DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Exportar dados para relatório
     */
    public function exportData(array $filters = []): array
    {
        $sql = "SELECT 
                    va.id,
                    va.status,
                    va.applied_at,
                    va.reviewed_at,
                    va.reapply_count,
                    v.title as vacancy_title,
                    vig.name as vigilante_name,
                    vig.email as vigilante_email,
                    coord.name as reviewed_by_name,
                    TIMESTAMPDIFF(HOUR, va.applied_at, va.reviewed_at) as review_hours
                FROM vacancy_applications va
                INNER JOIN exam_vacancies v ON va.vacancy_id = v.id
                INNER JOIN users vig ON va.vigilante_id = vig.id
                LEFT JOIN users coord ON va.reviewed_by = coord.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND va.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['vacancy_id'])) {
            $sql .= " AND va.vacancy_id = :vacancy_id";
            $params['vacancy_id'] = $filters['vacancy_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND va.applied_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND va.applied_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY va.applied_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
