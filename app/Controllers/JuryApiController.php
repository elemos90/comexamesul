<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Models\User;
use App\Services\AllocationService;

/**
 * Controller para endpoints API de Júris
 * 
 * Responsável por:
 * - Estatísticas e métricas
 * - Listagem de vigilantes/supervisores elegíveis
 * - Dados auxiliares para frontend
 */
class JuryApiController extends Controller
{
    /**
     * API: Obter estatísticas de alocação
     */
    public function getAllocationStats(Request $request)
    {
        try {
            $allocationService = new AllocationService();
            $stats = $allocationService->getAllocationStats();

            Response::json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obter slots e ocupação de júri(s)
     */
    public function getJurySlots(Request $request)
    {
        try {
            $juryId = (int) $request->param('id');

            $allocationService = new AllocationService();
            $slots = $allocationService->getJurySlots($juryId);

            Response::json([
                'success' => true,
                'slots' => $slots
            ]);
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obter vigilantes elegíveis para um júri
     */
    public function getEligibleVigilantes(Request $request)
    {
        try {
            $juryId = (int) $request->param('id');

            $allocationService = new AllocationService();
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
        try {
            $juryId = (int) $request->param('id');

            // Se não há juryId, retornar todos os supervisores elegíveis
            if (!$juryId) {
                $userModel = new User();
                $supervisors = $userModel->statement(
                    "SELECT u.id, u.name, u.email, 
                            IFNULL(vw.supervision_count, 0) as supervision_count,
                            IFNULL(vw.workload_score, 0) as workload_score
                     FROM users u
                     LEFT JOIN vw_vigilante_workload vw ON vw.user_id = u.id
                     WHERE u.role = 'vigilante' AND u.available_for_vigilance = 1
                     ORDER BY IFNULL(vw.supervision_count, 0) ASC, u.name"
                );

                Response::json([
                    'success' => true,
                    'supervisors' => $supervisors
                ]);
                return;
            }

            // Com juryId, usar o serviço de alocação para considerar conflitos
            $allocationService = new AllocationService();
            $supervisors = $allocationService->getEligibleSupervisors($juryId);

            Response::json([
                'success' => true,
                'supervisors' => $supervisors
            ]);
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obter métricas detalhadas (KPIs)
     */
    public function getMetrics(Request $request)
    {
        try {
            $allocationService = new AllocationService();
            $stats = $allocationService->getAllocationStats();

            // Calcular métricas adicionais
            $db = database();

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
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Recarregar lista de vigilantes disponíveis (para atualização dinâmica)
     */
    public function getAvailableVigilantes(Request $request)
    {
        try {
            $userModel = new User();
            $vigilantes = $userModel->getVigilantesWithWorkload();

            Response::json([
                'success' => true,
                'vigilantes' => $vigilantes,
                'total' => count($vigilantes)
            ]);
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Recarregar lista de supervisores disponíveis
     */
    public function getAvailableSupervisors(Request $request)
    {
        try {
            $userModel = new User();
            $supervisors = $userModel->statement(
                "SELECT u.*, vw.supervision_count, vw.workload_score 
                 FROM users u 
                 LEFT JOIN vw_vigilante_workload vw ON vw.user_id = u.id
                 WHERE u.role = 'vigilante' AND u.available_for_vigilance = 1 
                 ORDER BY vw.workload_score ASC, u.name"
            );

            Response::json([
                'success' => true,
                'supervisors' => $supervisors,
                'total' => count($supervisors)
            ]);
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obter vigilantes elegíveis para um júri (alias)
     */
    public function getEligibleForJury(Request $request)
    {
        return $this->getEligibleVigilantes($request);
    }

    /**
     * API: Obter estatísticas de alocação de uma vaga
     */
    public function getVacancyStats(Request $request)
    {
        try {
            $vacancyId = (int) $request->param('id');
            $db = database();

            $stats = $db->prepare("
                SELECT 
                    COUNT(DISTINCT j.id) as total_juries,
                    COUNT(DISTINCT jv.vigilante_id) as allocated_vigilantes,
                    COUNT(DISTINCT j.supervisor_id) as supervisors_assigned,
                    SUM(j.candidates_quota) as total_candidates
                FROM juries j
                LEFT JOIN jury_vigilantes jv ON jv.jury_id = j.id
                WHERE j.vacancy_id = :vacancy_id
            ");
            $stats->execute(['vacancy_id' => $vacancyId]);

            Response::json([
                'success' => true,
                'stats' => $stats->fetch(\PDO::FETCH_ASSOC)
            ]);
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obter candidatos aprovados de uma vaga (para supervisão)
     */
    public function getVacancyApprovedCandidates(Request $request)
    {
        try {
            $vacancyId = (int) $request->param('id');
            $db = database();

            $candidates = $db->prepare("
                SELECT u.id, u.name, u.email, u.phone, u.supervisor_eligible
                FROM users u
                INNER JOIN vacancy_applications va ON va.user_id = u.id
                WHERE va.vacancy_id = :vacancy_id 
                  AND va.status = 'approved'
                ORDER BY u.supervisor_eligible DESC, u.name
            ");
            $candidates->execute(['vacancy_id' => $vacancyId]);

            Response::json([
                'success' => true,
                'candidates' => $candidates->fetchAll(\PDO::FETCH_ASSOC)
            ]);
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obter dados mestre (locais e salas)
     */
    public function getMasterDataLocationsRooms()
    {
        try {
            $db = database();

            $locations = $db->query("SELECT * FROM exam_locations ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
            $rooms = $db->query("SELECT * FROM exam_rooms ORDER BY location_id, code")->fetchAll(\PDO::FETCH_ASSOC);

            Response::json([
                'success' => true,
                'locations' => $locations,
                'rooms' => $rooms
            ]);
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obter disciplinas usadas em uma vaga
     */
    public function getVacancySubjects(Request $request)
    {
        try {
            $vacancyId = (int) $request->param('id');
            $db = database();

            $subjects = $db->prepare("
                SELECT DISTINCT subject 
                FROM juries 
                WHERE vacancy_id = :vacancy_id
                ORDER BY subject
            ");
            $subjects->execute(['vacancy_id' => $vacancyId]);

            Response::json([
                'success' => true,
                'subjects' => $subjects->fetchAll(\PDO::FETCH_COLUMN)
            ]);
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * API: Obter salas disponíveis (sem conflito de horário)
     * 
     * Parâmetros:
     * - location_id: ID do local
     * - exam_date: Data do exame (YYYY-MM-DD)
     * - start_time: Horário início (HH:MM)
     * - end_time: Horário fim (HH:MM)
     */
    public function getAvailableRooms(Request $request)
    {
        try {
            $locationId = (int) $request->query('location_id');
            $examDate = $request->query('exam_date');
            $startTime = $request->query('start_time');
            $endTime = $request->query('end_time');

            if (!$locationId || !$examDate || !$startTime || !$endTime) {
                Response::json([
                    'success' => false,
                    'message' => 'Parâmetros obrigatórios: location_id, exam_date, start_time, end_time'
                ], 400);
                return;
            }

            $db = database();

            // Normalizar horários para garantir formato H:i:s (MySQL TIME)
            if (strlen($startTime) == 5)
                $startTime .= ':00';
            if (strlen($endTime) == 5)
                $endTime .= ':00';

            // Buscar salas do local que NÃO estão ocupadas no horário especificado
            // Uma sala está ocupada se existe um júri onde:
            // - room_id = sala em questão
            // - exam_date = data selecionada  
            // - Horários se sobrepõem: (start_time < end_time_param) AND (end_time > start_time_param)
            $stmt = $db->prepare("
                SELECT er.id, er.code, er.name, er.capacity, er.floor, er.building, el.name as location_name
                FROM exam_rooms er
                INNER JOIN exam_locations el ON el.id = er.location_id
                WHERE er.location_id = :location_id
                  AND er.active = 1
                  AND er.id NOT IN (
                      SELECT DISTINCT j.room_id
                      FROM juries j
                      WHERE j.room_id IS NOT NULL
                        AND j.exam_date = :exam_date
                        AND (
                            (j.start_time < :end_time AND j.end_time > :start_time)
                        )
                  )
                ORDER BY er.code
            ");

            $stmt->execute([
                'location_id' => $locationId,
                'exam_date' => $examDate,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]);

            $rooms = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Também buscar salas ocupadas para dar feedback ao usuário
            $occupiedStmt = $db->prepare("
                SELECT DISTINCT er.id, er.code, er.name, j.subject, j.start_time, j.end_time
                FROM exam_rooms er
                INNER JOIN juries j ON j.room_id = er.id
                WHERE er.location_id = :location_id
                  AND j.exam_date = :exam_date
                  AND (j.start_time < :end_time AND j.end_time > :start_time)
                ORDER BY er.code
            ");

            $occupiedStmt->execute([
                'location_id' => $locationId,
                'exam_date' => $examDate,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]);

            $occupied = $occupiedStmt->fetchAll(\PDO::FETCH_ASSOC);

            Response::json([
                'success' => true,
                'available_rooms' => $rooms,
                'occupied_rooms' => $occupied,
                'total_available' => count($rooms),
                'total_occupied' => count($occupied)
            ]);
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
