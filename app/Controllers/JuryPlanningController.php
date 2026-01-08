<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Models\ExamVacancy;
use App\Models\Jury;
use App\Models\JuryVigilante;
use App\Models\User;
use App\Services\AllocationPlannerService;
use App\Services\AllocationService;
use App\Services\SmartAllocationService;
use App\Utils\Auth;
use App\Utils\Flash;

/**
 * Controller para planejamento de júris
 * 
 * Responsável por:
 * - Páginas de planejamento
 * - Wizard de criação de júris
 * - Planejamento automático por local/data
 */
class JuryPlanningController extends Controller
{
    /**
     * Página de planejamento com drag-and-drop
     */
    public function planning(Request $request): string
    {
        $user = Auth::user();
        $juryModel = new Jury();
        $allocationService = new AllocationService();
        $vacancyModel = new ExamVacancy();

        // Filtro por vaga
        $vacancyId = isset($_GET['vacancy_id']) ? $_GET['vacancy_id'] : 'current';
        $vacancy = null;

        if ($vacancyId === 'all') {
            $vacancyId = null;
        } elseif ($vacancyId === 'current' || empty($vacancyId)) {
            $openVacancies = $vacancyModel->openVacancies();
            $vacancyId = !empty($openVacancies) ? (int) $openVacancies[0]['id'] : null;
        } else {
            $vacancyId = (int) $vacancyId;
        }

        if ($vacancyId) {
            $vacancy = $vacancyModel->find($vacancyId);
        }

        $allVacancies = $vacancyModel->statement('SELECT * FROM exam_vacancies ORDER BY created_at DESC LIMIT 10');

        // Buscar júris
        if ($vacancyId) {
            $juries = $juryModel->statement(
                "SELECT j.*, 
                        er.name as room_name, er.code as room_code,
                        er.capacity as room_capacity, er.floor as room_floor, er.building as room_building,
                        COALESCE(el.name, j.location) as location,
                        supervisor.name as supervisor_name, supervisor.phone as supervisor_phone
                 FROM juries j
                 LEFT JOIN exam_rooms er ON er.id = j.room_id
                 LEFT JOIN exam_locations el ON el.id = er.location_id
                 LEFT JOIN users supervisor ON supervisor.id = j.supervisor_id
                 WHERE j.vacancy_id = :vacancy_id 
                 ORDER BY j.exam_date, j.start_time",
                ['vacancy_id' => $vacancyId]
            );

            $groupedJuries = [];
            foreach ($juries as $jury) {
                $key = $jury['subject'] . '_' . $jury['exam_date'] . '_' . $jury['start_time'];
                if (!isset($groupedJuries[$key])) {
                    $groupedJuries[$key] = [
                        'subject' => $jury['subject'],
                        'exam_date' => $jury['exam_date'],
                        'start_time' => $jury['start_time'],
                        'end_time' => $jury['end_time'],
                        'juries' => []
                    ];
                }
                $groupedJuries[$key]['juries'][] = $jury;
            }
            $groupedJuries = array_values($groupedJuries);
        } else {
            $groupedJuries = $juryModel->getGroupedBySubjectAndTime();
        }

        // Enriquecer com dados de alocação
        foreach ($groupedJuries as &$group) {
            foreach ($group['juries'] as &$jury) {
                $juryVigilantes = new JuryVigilante();
                $jury['vigilantes'] = $juryVigilantes->vigilantesForJury((int) $jury['id']);
                $jury['slots'] = $allocationService->getJurySlots((int) $jury['id']);
            }
        }

        $userModel = new User();
        $availableVigilantes = $userModel->getVigilantesWithWorkload();
        $availableSupervisors = $userModel->statement(
            "SELECT u.*, vw.supervision_count, vw.workload_score 
             FROM users u 
             LEFT JOIN vw_vigilante_workload vw ON vw.user_id = u.id
             WHERE u.supervisor_eligible = 1 
             ORDER BY vw.workload_score ASC, u.name"
        );

        $stats = $vacancyId
            ? [
                'total_juries' => count($juries ?? []),
                'allocated_vigilantes' => $juryModel->statement(
                    "SELECT COUNT(DISTINCT jv.vigilante_id) as total
                     FROM jury_vigilantes jv
                     INNER JOIN juries j ON j.id = jv.jury_id
                     WHERE j.vacancy_id = :vacancy_id",
                    ['vacancy_id' => $vacancyId]
                )[0]['total'] ?? 0,
                'total_vigilantes_needed' => count($juries ?? []) * 2
            ]
            : $allocationService->getAllocationStats();

        return $this->view('juries/planning', [
            'groupedJuries' => $groupedJuries,
            'vigilantes' => $availableVigilantes,
            'supervisors' => $availableSupervisors,
            'stats' => $stats,
            'user' => $user,
            'vacancy' => $vacancy,
            'vacancyId' => $vacancyId,
            'allVacancies' => $allVacancies
        ]);
    }

    /**
     * Página de planejamento por vaga (wizard)
     */
    public function planningByVacancy(): string
    {
        $user = Auth::user();
        $vacancyModel = new ExamVacancy();
        $juryModel = new Jury();
        $allocationService = new SmartAllocationService();

        $openVacancies = $vacancyModel->openVacancies();
        $vacanciesWithStats = [];

        foreach ($openVacancies as $vacancy) {
            $juries = $juryModel->getByVacancyWithStats((int) $vacancy['id']);
            $vacancy['has_juries'] = !empty($juries);
            $vacancy['stats'] = $vacancy['has_juries']
                ? $allocationService->getVacancyAllocationStats((int) $vacancy['id'])
                : null;
            $vacanciesWithStats[] = $vacancy;
        }

        $locationModel = new \App\Models\ExamLocation();
        $locations = $locationModel->statement("SELECT * FROM exam_locations WHERE active = 1 ORDER BY name");

        $disciplineModel = new \App\Models\Discipline();
        $disciplines = $disciplineModel->getActive();

        $roomModel = new \App\Models\ExamRoom();
        $rooms = $roomModel->getAllWithLocation(true);

        return $this->view('juries/planning_by_vacancy', [
            'vacancies' => $vacanciesWithStats,
            'locations' => $locations,
            'disciplines' => $disciplines,
            'rooms' => $rooms,
            'user' => $user,
        ]);
    }

    /**
     * Página de gerenciamento de júris por vaga
     */
    public function manageVacancyJuries(Request $request): string
    {
        $vacancyId = (int) $request->param('id');
        $user = Auth::user();

        $vacancyModel = new ExamVacancy();
        $vacancy = $vacancyModel->find($vacancyId);

        if (!$vacancy) {
            Flash::add('error', 'Vaga não encontrada');
            redirect('/juries/planning-by-vacancy');
        }

        $juryModel = new Jury();
        $groupedJuries = $juryModel->getGroupedByVacancy($vacancyId);

        $juryVigilantes = new JuryVigilante();
        foreach ($groupedJuries as &$locationGroup) {
            foreach ($locationGroup['disciplines'] as &$discipline) {
                foreach ($discipline['juries'] as &$jury) {
                    $jury['vigilantes'] = $juryVigilantes->vigilantesForJury((int) $jury['id']);
                    $jury['vigilantes_count'] = count($jury['vigilantes']);
                    $jury['required_vigilantes'] = $juryModel->calculateRequiredVigilantes((int) $jury['candidates_quota']);
                }
            }
        }

        $allocationService = new SmartAllocationService();
        $stats = $allocationService->getVacancyAllocationStats($vacancyId);
        $candidates = $allocationService->getApprovedCandidates($vacancyId);

        return $this->view('juries/manage_vacancy', [
            'vacancy' => $vacancy,
            'groupedJuries' => $groupedJuries,
            'stats' => $stats,
            'candidates' => $candidates,
            'user' => $user,
        ]);
    }

    /**
     * API: Criar júris vinculados a uma vaga
     */
    public function createJuriesForVacancy(Request $request)
    {
        try {
            if (ob_get_length())
                ob_clean();

            $vacancyId = (int) $request->input('vacancy_id');
            $location = $request->input('location');
            $examDate = $request->input('exam_date');
            $disciplines = $request->input('disciplines');

            if (!$vacancyId || !$location || !$examDate || empty($disciplines)) {
                Response::json(['success' => false, 'message' => 'Dados incompletos'], 400);
                return;
            }

            $juryModel = new Jury();
            $roomModel = new \App\Models\ExamRoom();
            $totalCreated = 0;

            foreach ($disciplines as $discipline) {
                if (empty($discipline['subject']) || empty($discipline['start_time']) || empty($discipline['end_time'])) {
                    continue;
                }

                foreach ($discipline['rooms'] ?? [] as $room) {
                    if (empty($room['room']) || empty($room['candidates_quota'])) {
                        continue;
                    }

                    $roomDetails = !empty($room['room_id']) ? $roomModel->find((int) $room['room_id']) : null;
                    $roomText = $roomDetails ? ($roomDetails['name'] ?: $roomDetails['code']) : $room['room'];

                    $juryModel->create([
                        'vacancy_id' => $vacancyId,
                        'subject' => $discipline['subject'],
                        'exam_date' => $examDate,
                        'start_time' => $discipline['start_time'],
                        'end_time' => $discipline['end_time'],
                        'location' => $location,
                        'location_id' => $roomDetails['location_id'] ?? null,
                        'room' => $roomText,
                        'room_id' => $room['room_id'] ?? null,
                        'candidates_quota' => (int) $room['candidates_quota'],
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $totalCreated++;
                }
            }

            Response::json([
                'success' => true,
                'message' => "{$totalCreated} júri(s) criado(s) com sucesso",
                'total_created' => $totalCreated
            ]);
        } catch (\Exception $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Planejar alocação por local/data
     */
    public function planLocalDate(Request $request): void
    {
        try {
            $user = Auth::user();
            if (!in_array($user['role'], ['coordenador', 'membro'], true)) {
                Response::json(['ok' => false, 'erro' => 'Permissão negada'], 403);
                return;
            }

            $data = $request->json();
            $location = $data['location'] ?? null;
            $date = $data['data'] ?? null;

            if (!$location || !$date) {
                Response::json(['ok' => false, 'erro' => 'Parâmetros inválidos'], 400);
                return;
            }

            $db = database();
            $plannerService = new AllocationPlannerService($db);
            $result = $plannerService->planLocalDate($location, $date);

            Response::json($result);
        } catch (\Exception $e) {
            Response::json(['ok' => false, 'erro' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Aplicar plano de alocação
     */
    public function applyLocalDate(Request $request): void
    {
        try {
            $user = Auth::user();
            if (!in_array($user['role'], ['coordenador', 'membro'], true)) {
                Response::json(['ok' => false, 'erro' => 'Permissão negada'], 403);
                return;
            }

            $data = $request->json();
            $location = $data['location'] ?? null;
            $date = $data['data'] ?? null;
            $plan = $data['plan'] ?? null;

            if (!$location || !$date || !$plan) {
                Response::json(['ok' => false, 'erro' => 'Parâmetros inválidos'], 400);
                return;
            }

            $db = database();
            $plannerService = new AllocationPlannerService($db);
            $result = $plannerService->applyLocalDate($location, $date, $plan);

            Response::json($result);
        } catch (\Exception $e) {
            Response::json(['ok' => false, 'erro' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Obter KPIs de alocação
     */
    public function getKPIs(Request $request): void
    {
        try {
            $location = $request->query('location');
            $date = $request->query('data');

            if (!$location || !$date) {
                Response::json(['ok' => false, 'erro' => 'Parâmetros inválidos'], 400);
                return;
            }

            $db = database();
            $plannerService = new AllocationPlannerService($db);
            $kpis = $plannerService->getKPIs($location, $date);

            Response::json(['ok' => true, 'kpis' => $kpis]);
        } catch (\Exception $e) {
            Response::json(['ok' => false, 'erro' => $e->getMessage()], 500);
        }
    }
}
