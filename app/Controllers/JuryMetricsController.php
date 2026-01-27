<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;

/**
 * Controller for Jury Metrics and Statistics
 */
class JuryMetricsController extends Controller
{
    /**
     * API: Obter estatísticas de alocação
     */
    public function getAllocationStats(Request $request)
    {
        $allocationService = new \App\Services\AllocationService();
        $stats = $allocationService->getAllocationStats();

        Response::json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * API: Obter métricas detalhadas (KPIs)
     */
    public function getMetrics(Request $request)
    {
        $allocationService = new \App\Services\AllocationService();
        $stats = $allocationService->getAllocationStats();

        // Calcular métricas adicionais
        $db = \App\Database\Connection::getInstance();

        // Conflitos detectados
        $conflictsStmt = $db->query("
            SELECT COUNT(DISTINCT jv1.vigilante_id) as conflicts_count
            FROM jury_vigilantes jv1
            INNER JOIN juries j1 ON j1.id = jv1.jury_id
            INNER JOIN jury_vigilantes jv2 ON jv2.vigilante_id = jv1.vigilante_id AND jv2.id != jv1.id
            INNER JOIN juries j2 ON j2.id = jv2.jury_id
            WHERE j1.exam_date = j2.exam_date
              AND (j1.start_time < j2.end_time AND j2.start_time < j1.end_time)
        ");
        $conflictsCount = (int) $conflictsStmt->fetchColumn();

        // Júris sem alocação completa
        $incompleteStmt = $db->query("
            SELECT COUNT(*) FROM vw_jury_slots 
            WHERE occupancy_status = 'incomplete'
        ");
        $incompleteJuries = (int) $incompleteStmt->fetchColumn();

        // Taxa de ocupação média
        $occupancyStmt = $db->query("
            SELECT AVG((vigilantes_allocated * 100.0) / vigilantes_capacity) as avg_occupancy
            FROM vw_jury_slots
            WHERE vigilantes_capacity > 0
        ");
        $avgOccupancy = round((float) $occupancyStmt->fetchColumn(), 2);

        Response::json([
            'success' => true,
            'metrics' => array_merge($stats, [
                'conflicts_count' => $conflictsCount,
                'incomplete_juries' => $incompleteJuries,
                'avg_occupancy_percent' => $avgOccupancy,
                'balance_quality' => $stats['workload_std_deviation'] <= 1.0 ? 'excellent' : ($stats['workload_std_deviation'] <= 2.0 ? 'good' : 'needs_improvement')
            ])
        ]);
    }
}
