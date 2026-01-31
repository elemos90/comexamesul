<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Models\Jury;
use App\Models\JuryVigilante;
use App\Models\User;
use App\Utils\Auth;

/**
 * Controller for Jury Resources (Vigilantes, Supervisors, Rooms)
 * Handles availability and eligibility logic
 */
class JuryResourceController extends Controller
{
    /**
     * API: Obter slots e ocupação de júri(s)
     */
    public function getJurySlots(Request $request)
    {
        $juryId = (int) $request->param('id');

        $allocationService = new \App\Services\AllocationService();
        $slots = $allocationService->getJurySlots($juryId);

        Response::json([
            'success' => true,
            'slots' => $slots
        ]);
    }

    /**
     * API: Obter vigilantes elegíveis para um júri
     */
    public function getEligibleVigilantes(Request $request)
    {
        try {
            $juryId = (int) $request->param('id');

            $allocationService = new \App\Services\AllocationService();
            $vigilantes = $allocationService->getEligibleVigilantes($juryId);

            Response::json([
                'success' => true,
                'vigilantes' => $vigilantes
            ]);
        } catch (\Exception $e) {
            error_log("Erro ao buscar vigilantes elegíveis: " . $e->getMessage());
            Response::json([
                'success' => false,
                'message' => 'Erro ao carregar candidatos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obter supervisores elegíveis (para um júri ou todos)
     */
    public function getEligibleSupervisors(Request $request)
    {
        $juryId = (int) $request->param('id');

        // Se não há juryId, retornar todos os supervisores elegíveis (COM FILTRO DE CONFLITO)
        if (!$juryId) {
            $examDate = $request->input('exam_date');
            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');

            $userModel = new User();

            // Se há contexto de exame, filtrar vigilantes já alocados E supervisores já ocupados
            if ($examDate && $startTime && $endTime) {
                // Implementação simplificada via Query Builder seria ideal aqui, mas mantendo raw SQL por compatibilidade imediata
                $db = \App\Database\Connection::getInstance();

                // 1. Buscar vigilantes já alocados como vigilantes neste horário
                $stmt = $db->prepare("
                    SELECT DISTINCT jv.vigilante_id
                    FROM jury_vigilantes jv
                    INNER JOIN juries j ON j.id = jv.jury_id
                    WHERE j.exam_date = :exam_date
                      AND j.start_time = :start_time
                      AND j.end_time = :end_time
                ");
                $stmt->execute([
                    'exam_date' => $examDate,
                    'start_time' => $startTime,
                    'end_time' => $endTime
                ]);
                $excludeIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

                // 2. Buscar supervisores JÁ supervisionando outros exames neste horário
                $supervisorStmt = $db->prepare("
                    SELECT DISTINCT j.supervisor_id
                    FROM juries j
                    WHERE j.supervisor_id IS NOT NULL
                      AND j.exam_date = :exam_date
                      AND j.start_time < :end_time
                      AND j.end_time > :start_time
                ");
                $supervisorStmt->execute([
                    'exam_date' => $examDate,
                    'start_time' => $startTime,
                    'end_time' => $endTime
                ]);
                $busySupervisors = $supervisorStmt->fetchAll(\PDO::FETCH_COLUMN);

                // Combinar ambos os IDs a excluir
                $excludeIds = array_unique(array_merge($excludeIds, $busySupervisors));

                // Construir cláusula de exclusão
                $excludeClause = !empty($excludeIds) ? "AND u.id NOT IN (" . implode(',', array_map('intval', $excludeIds)) . ")" : "";

                $supervisors = $userModel->statement(
                    "SELECT u.id, u.name, u.email, u.supervisor_eligible,
                            IFNULL(vw.supervision_count, 0) as supervision_count,
                            IFNULL(vw.workload_score, 0) as workload_score
                     FROM users u
                     LEFT JOIN vw_vigilante_workload vw ON vw.user_id = u.id
                     WHERE u.role IN ('vigilante', 'coordenador', 'membro')
                       $excludeClause
                     ORDER BY u.supervisor_eligible DESC, IFNULL(vw.supervision_count, 0) ASC, u.name"
                );
            } else {
                // Sem filtro de exame
                $supervisors = $userModel->statement(
                    "SELECT u.id, u.name, u.email, u.supervisor_eligible,
                            IFNULL(vw.supervision_count, 0) as supervision_count,
                            IFNULL(vw.workload_score, 0) as workload_score
                     FROM users u
                     LEFT JOIN vw_vigilante_workload vw ON vw.user_id = u.id
                     WHERE u.role IN ('vigilante', 'coordenador', 'membro')
                     ORDER BY u.supervisor_eligible DESC, IFNULL(vw.supervision_count, 0) ASC, u.name"
                );
            }

            Response::json([
                'success' => true,
                'supervisors' => $supervisors
            ]);
            return;
        }

        // Com juryId, usar o serviço de alocação para considerar conflitos
        $allocationService = new \App\Services\AllocationService();
        $supervisors = $allocationService->getEligibleSupervisors($juryId);

        Response::json([
            'success' => true,
            'supervisors' => $supervisors
        ]);
    }

    /**
     * API: Obter vigilantes elegíveis para o Wizard de criação de júris
     * Retorna vigilantes aprovados para a vaga especificada
     */
    public function getEligibleVigilantesForWizard(Request $request): void
    {
        try {
            $vacancyId = (int) $request->input('vacancy_id');

            if (!$vacancyId) {
                Response::json([
                    'success' => false,
                    'message' => 'ID da vaga é obrigatório'
                ], 400);
                return;
            }

            // Buscar vigilantes aprovados para esta vaga
            $db = \App\Database\Connection::getInstance();
            $stmt = $db->prepare("
                SELECT u.id, u.name, u.email,
                       COALESCE(vw.workload_score, 0) as workload_score,
                       0 as current_juries
                FROM users u
                INNER JOIN vacancy_applications a ON a.vigilante_id = u.id AND a.vacancy_id = ?
                LEFT JOIN vw_vigilante_workload vw ON vw.user_id = u.id
                WHERE a.status = 'aprovada'
                  AND u.role IN ('vigilante', 'supervisor')
                ORDER BY workload_score ASC, u.name ASC
            ");
            $stmt->execute([$vacancyId]);
            $vigilantes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            Response::json([
                'success' => true,
                'vigilantes' => $vigilantes,
                'total' => count($vigilantes)
            ]);
        } catch (\Exception $e) {
            error_log("Erro ao buscar vigilantes para wizard: " . $e->getMessage());
            Response::json([
                'success' => false,
                'message' => 'Erro ao carregar vigilantes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obter supervisores elegíveis para o Wizard de criação de júris
     * Retorna membros da comissão e docentes elegíveis para supervisão
     */
    public function getEligibleSupervisorsForWizard(Request $request): void
    {
        try {
            $vacancyId = (int) $request->input('vacancy_id');

            // Buscar supervisores elegíveis (membros da comissão + docentes com flag)
            $db = \App\Database\Connection::getInstance();
            $stmt = $db->prepare("
                SELECT u.id, u.name, u.email, u.role, u.supervisor_eligible,
                       CASE 
                           WHEN u.role = 'coordenador' THEN 'Coordenador'
                           WHEN u.role = 'membro' THEN 'Membro da Comissão'
                           ELSE 'Docente'
                       END as role_label,
                       COALESCE(vw.supervision_count, 0) as supervision_count,
                       COALESCE(vw.workload_score, 0) as workload_score
                FROM users u
                LEFT JOIN vw_vigilante_workload vw ON vw.user_id = u.id
                WHERE u.role IN ('vigilante', 'coordenador', 'membro')
                ORDER BY u.supervisor_eligible DESC, workload_score ASC, RAND()
            ");
            $stmt->execute();
            $supervisors = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Enriquecer os dados
            $supervisors = array_map(function ($s) {
                return [
                    'id' => (int) $s['id'],
                    'name' => $s['name'],
                    'email' => $s['email'],
                    'role' => $s['role_label'],
                    'supervision_count' => (int) $s['supervision_count'],
                    'workload_score' => (float) $s['workload_score']
                ];
            }, $supervisors);

            Response::json([
                'success' => true,
                'supervisors' => $supervisors,
                'total' => count($supervisors)
            ]);
        } catch (\Exception $e) {
            error_log("Erro ao buscar supervisores para wizard: " . $e->getMessage());
            Response::json([
                'success' => false,
                'message' => 'Erro ao carregar supervisores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Recarregar lista de vigilantes disponíveis (para atualização dinâmica)
     */
    public function getAvailableVigilantes(Request $request)
    {
        $userModel = new User();
        $vigilantes = $userModel->getVigilantesWithWorkload();

        Response::json([
            'success' => true,
            'vigilantes' => $vigilantes,
            'total' => count($vigilantes)
        ]);
    }

    /**
     * API: Recarregar lista de supervisores disponíveis
     */
    public function getAvailableSupervisors(Request $request)
    {
        $userModel = new User();
        $supervisors = $userModel->statement(
            "SELECT u.*, vw.supervision_count, vw.workload_score 
             FROM users u 
             LEFT JOIN vw_vigilante_workload vw ON vw.user_id = u.id
             WHERE u.role = 'vigilante'
             ORDER BY u.supervisor_eligible DESC, vw.workload_score ASC, RAND()"
        );

        Response::json([
            'success' => true,
            'supervisors' => $supervisors,
            'total' => count($supervisors)
        ]);
    }
}
