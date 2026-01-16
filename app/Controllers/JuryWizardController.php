<?php

namespace App\Controllers;

use App\Database\Connection;
use App\Http\Request;
use App\Http\Response;
use App\Models\Discipline;
use App\Models\ExamLocation;
use App\Models\ExamRoom;
use App\Models\ExamVacancy;
use App\Models\Jury;
use App\Models\JuryVigilante;
use App\Services\ActivityLogger;
use App\Services\SmartAllocationService;
use App\Utils\Auth;
use App\Utils\Flash;

/**
 * Controller para o Wizard de Criação de Júris por Vaga
 * 
 * Gerencia todo o fluxo de criação, validação e gestão de júris
 * vinculados a uma vaga específica.
 * 
 * @package App\Controllers
 */
class JuryWizardController extends Controller
{
    /**
     * Página de planejamento por vaga (wizard)
     */
    public function planningByVacancy(): string
    {
        $user = Auth::user();
        $vacancyModel = new ExamVacancy();
        $juryModel = new Jury();
        $allocationService = new SmartAllocationService();

        // Buscar vagas abertas
        $openVacancies = $vacancyModel->openVacancies();

        // Pré-carregar estatísticas de todas as vagas (evita N+1)
        $vacanciesWithStats = [];
        foreach ($openVacancies as $vacancy) {
            $juries = $juryModel->getByVacancyWithStats((int) $vacancy['id']);
            $vacancy['has_juries'] = !empty($juries);
            $vacancy['stats'] = null;

            if ($vacancy['has_juries']) {
                $vacancy['stats'] = $allocationService->getVacancyAllocationStats((int) $vacancy['id']);
            }

            $vacanciesWithStats[] = $vacancy;
        }

        // Buscar dados mestre
        $locationModel = new ExamLocation();
        $locations = $locationModel->statement("SELECT * FROM exam_locations WHERE active = 1 ORDER BY name");

        $disciplineModel = new Discipline();
        $disciplines = $disciplineModel->getActive();

        $roomModel = new ExamRoom();
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
                Response::json([
                    'success' => false,
                    'message' => 'Dados incompletos'
                ], 400);
                return;
            }

            $juryModel = new Jury();
            $roomModel = new ExamRoom();
            $created = [];
            $totalCreated = 0;
            $conflicts = [];

            // ========== VALIDAÇÃO DE CONFLITOS DE PAPÉIS (VIGILANTE VS SUPERVISOR) ==========
            $allVigilanteIds = [];
            $allSupervisorIds = [];

            // Coletar todos os vigilantes propostos
            foreach ($disciplines as $discipline) {
                if (!empty($discipline['rooms'])) {
                    foreach ($discipline['rooms'] as $room) {
                        if (!empty($room['vigilantes']) && is_array($room['vigilantes'])) {
                            foreach ($room['vigilantes'] as $vId) {
                                if ((int) $vId > 0) {
                                    $allVigilanteIds[] = (int) $vId;
                                }
                            }
                        }
                    }
                }
            }
            $allVigilanteIds = array_unique($allVigilanteIds);

            // Coletar todos os supervisores propostos
            $proposedSupervisors = $request->input('blockSupervisors') ?? $request->input('supervisors') ?? [];
            foreach ($proposedSupervisors as $sId) {
                if ((int) $sId > 0) {
                    $allSupervisorIds[] = (int) $sId;
                }
            }
            $allSupervisorIds = array_unique($allSupervisorIds);

            // Verificar interseção
            $roleConflicts = array_intersect($allVigilanteIds, $allSupervisorIds);

            if (!empty($roleConflicts)) {
                $userModel = new \App\Models\User();
                $conflictNames = [];
                foreach ($roleConflicts as $userId) {
                    $u = $userModel->find($userId);
                    if ($u) {
                        $conflictNames[] = $u['name'];
                    }
                }

                Response::json([
                    'success' => false,
                    'message' => "❌ Conflito de papéis detectado!\n\nOs seguintes utilizadores foram atribuídos como VIGILANTE e SUPERVISOR ao mesmo tempo:\n- " . implode("\n- ", $conflictNames) . "\n\nPor favor, corrija as atribuições antes de salvar."
                ], 422);
                return;
            }
            // ==============================================================================

            foreach ($disciplines as $discipline) {
                if (empty($discipline['subject']) || empty($discipline['start_time']) || empty($discipline['end_time'])) {
                    continue;
                }

                $rooms = $discipline['rooms'] ?? [];

                foreach ($rooms as $room) {
                    if (empty($room['room']) || empty($room['candidates_quota'])) {
                        continue;
                    }

                    // Buscar detalhes da sala
                    $roomDetails = null;
                    $roomLocationId = null;
                    if (!empty($room['room_id'])) {
                        $roomDetails = $roomModel->find((int) $room['room_id']);
                        if ($roomDetails) {
                            $roomLocationId = $roomDetails['location_id'];
                        }
                    }

                    // Criar texto descritivo da sala
                    $roomText = $this->buildRoomDescription($room, $roomDetails);

                    // Verificar conflito de sala
                    $conflictCheck = $this->checkRoomConflict(
                        $juryModel,
                        $location,
                        $room['room'],
                        $examDate,
                        $discipline['start_time'],
                        $discipline['end_time']
                    );

                    if (!empty($conflictCheck)) {
                        $conflicts[] = [
                            'room' => $room['room'],
                            'subject' => $discipline['subject'],
                            'time' => $discipline['start_time'] . '-' . $discipline['end_time'],
                            'existing' => $conflictCheck[0]['subject'] . ' (' . $conflictCheck[0]['start_time'] . '-' . $conflictCheck[0]['end_time'] . ')'
                        ];
                        continue;
                    }

                    $vigilantesCount = count($room['vigilantes'] ?? []);
                    $minRequired = max(1, ceil((int) $room['candidates_quota'] / 30));
                    $capacity = max($vigilantesCount, $minRequired, 2); // Ensure capacity covers current allocation

                    $juryId = $juryModel->create([
                        'vacancy_id' => $vacancyId,
                        'subject' => $discipline['subject'],
                        'exam_date' => $examDate,
                        'start_time' => $discipline['start_time'],
                        'end_time' => $discipline['end_time'],
                        'location' => $location,
                        'location_id' => $roomLocationId,
                        'room' => $roomText,
                        'room_id' => !empty($room['room_id']) ? (int) $room['room_id'] : null,
                        'candidates_quota' => (int) $room['candidates_quota'],
                        'vigilantes_capacity' => $capacity, // Fix for SQL Error 1644
                        'notes' => null,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $created[] = [
                        'id' => $juryId,
                        'subject' => $discipline['subject'],
                        'room' => $room['room']
                    ];

                    // Guardar vigilantes alocados
                    $this->assignVigilantesToJury($juryId, $room['vigilantes'] ?? []);

                    ActivityLogger::log('juries', $juryId, 'create_for_vacancy', [
                        'vacancy_id' => $vacancyId,
                        'subject' => $discipline['subject'],
                        'room' => $room['room'],
                        'vigilantes_count' => count($room['vigilantes'] ?? [])
                    ]);

                    $totalCreated++;
                }
            }

            if ($totalCreated === 0) {
                $message = $this->buildConflictMessage($conflicts);
                Response::json([
                    'success' => false,
                    'message' => 'Nenhum júri foi criado' . $message,
                    'conflicts' => $conflicts
                ], 400);
                return;
            }

            $message = "Criados {$totalCreated} júris com sucesso";
            $message .= $this->buildConflictMessage($conflicts);

            // Guardar supervisores por bloco
            $supervisorCount = $this->assignBlockSupervisors($request, $created);
            if ($supervisorCount > 0) {
                $message .= "\n✅ {$supervisorCount} supervisor(es) atribuído(s)";
            }

            Response::json([
                'success' => true,
                'message' => $message,
                'juries' => $created,
                'total' => $totalCreated,
                'conflicts' => $conflicts,
                'has_conflicts' => !empty($conflicts),
                'vacancy_id' => $vacancyId
            ]);

        } catch (\Exception $e) {
            if (ob_get_length())
                ob_clean();
            Response::json([
                'success' => false,
                'message' => 'Erro ao criar júris: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Validar o planeamento de uma vaga
     */
    public function validateVacancyPlanning(Request $request)
    {
        $id = (int) $request->param('id');

        try {
            if (ob_get_length())
                ob_clean();

            if (!$id) {
                Response::json(['success' => false, 'message' => 'ID da vaga não fornecido'], 400);
                return;
            }

            $db = Connection::getInstance();

            // Verificar se a vaga existe
            $vacancyStmt = $db->prepare("SELECT id, title FROM exam_vacancies WHERE id = :id");
            $vacancyStmt->execute(['id' => $id]);
            $vacancy = $vacancyStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$vacancy) {
                Response::json(['success' => false, 'message' => 'Vaga não encontrada'], 404);
                return;
            }

            // Verificar estatísticas
            $statsStmt = $db->prepare("
                SELECT 
                    COUNT(*) as total_juries,
                    COALESCE(SUM(candidates_quota), 0) as total_candidates,
                    SUM(CASE WHEN supervisor_id IS NULL OR supervisor_id = 0 THEN 1 ELSE 0 END) as sem_supervisor
                FROM juries 
                WHERE vacancy_id = :id
            ");
            $statsStmt->execute(['id' => $id]);
            $stats = $statsStmt->fetch(\PDO::FETCH_ASSOC);

            // Contar vagas livres de vigilantes
            $vigilanteStmt = $db->prepare("
                SELECT j.id, j.room, j.candidates_quota,
                       (SELECT COUNT(*) FROM jury_vigilantes WHERE jury_id = j.id) as vigilantes_count
                FROM juries j
                WHERE j.vacancy_id = :id
            ");
            $vigilanteStmt->execute(['id' => $id]);
            $juries = $vigilanteStmt->fetchAll(\PDO::FETCH_ASSOC);

            $vagasLivres = 0;
            foreach ($juries as $jury) {
                $minVigilantes = max(1, ceil(($jury['candidates_quota'] ?? 0) / 30));
                if (($jury['vigilantes_count'] ?? 0) < $minVigilantes) {
                    $vagasLivres += ($minVigilantes - ($jury['vigilantes_count'] ?? 0));
                }
            }

            // Verificar se pode validar
            $semSupervisor = (int) ($stats['sem_supervisor'] ?? 0);
            if ($vagasLivres > 0 || $semSupervisor > 0) {
                Response::json([
                    'success' => false,
                    'message' => "Não é possível validar com pendências: $vagasLivres vaga(s) de vigilante, $semSupervisor júri(s) sem supervisor"
                ], 400);
                return;
            }

            ActivityLogger::log('vacancies', $id, 'validate_planning', [
                'total_juries' => $stats['total_juries'] ?? 0,
                'total_candidates' => $stats['total_candidates'] ?? 0
            ]);

            Response::json([
                'success' => true,
                'message' => 'Planeamento validado com sucesso',
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            if (ob_get_length())
                ob_clean();
            Response::json([
                'success' => false,
                'message' => 'Erro ao validar planeamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Auto-alocar vigilantes em todos os júris de uma vaga
     */
    public function autoAllocateVacancy(Request $request)
    {
        $vacancyId = (int) $request->input('vacancy_id');

        if (!$vacancyId) {
            Response::json([
                'success' => false,
                'message' => 'ID da vaga não fornecido'
            ], 400);
            return;
        }

        $allocationService = new SmartAllocationService();
        $result = $allocationService->autoAllocateVacancy($vacancyId, Auth::id());

        if ($result['success']) {
            ActivityLogger::log('juries', 0, 'auto_allocate_vacancy', [
                'vacancy_id' => $vacancyId,
                'total_allocated' => $result['stats']['total_allocated'] ?? 0
            ]);
        }

        Response::json($result);
    }

    /**
     * API: Desalocar todos vigilantes de uma vaga
     */
    public function clearVacancyAllocations(Request $request)
    {
        $vacancyId = (int) $request->input('vacancy_id');

        if (!$vacancyId) {
            Response::json([
                'success' => false,
                'message' => 'ID da vaga não fornecido'
            ], 400);
            return;
        }

        $allocationService = new SmartAllocationService();
        $result = $allocationService->clearVacancyAllocations($vacancyId);

        ActivityLogger::log('juries', 0, 'clear_vacancy_allocations', [
            'vacancy_id' => $vacancyId
        ]);

        Response::json($result);
    }

    /**
     * API: Obter estatísticas de alocação de uma vaga
     */
    public function getVacancyStats(Request $request)
    {
        $vacancyId = (int) $request->param('id');

        if (!$vacancyId) {
            Response::json([
                'success' => false,
                'message' => 'ID da vaga não fornecido'
            ], 400);
            return;
        }

        $allocationService = new SmartAllocationService();
        $stats = $allocationService->getVacancyAllocationStats($vacancyId);

        Response::json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * API: Obter vigilantes elegíveis para um júri
     */
    public function getEligibleForJury(Request $request)
    {
        $juryId = (int) $request->param('id');

        if (!$juryId) {
            Response::json([
                'success' => false,
                'message' => 'ID do júri não fornecido'
            ], 400);
            return;
        }

        $allocationService = new SmartAllocationService();
        $vigilantes = $allocationService->getEligibleVigilantesForJury($juryId);

        $juryModel = new Jury();
        $jury = $juryModel->find($juryId);

        Response::json([
            'success' => true,
            'vigilantes' => $vigilantes,
            'debug' => [
                'jury_id' => $juryId,
                'vacancy_id' => $jury['vacancy_id'] ?? null,
                'total_found' => count($vigilantes)
            ],
            'total' => count($vigilantes)
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

        // Enriquecer com dados de alocação
        $juryVigilantes = new JuryVigilante();
        foreach ($groupedJuries as &$locationGroup) {
            foreach ($locationGroup['disciplines'] as &$discipline) {
                foreach ($discipline['juries'] as &$jury) {
                    $jury['vigilantes'] = $juryVigilantes->vigilantesForJury((int) $jury['id']);
                    $jury['vigilantes_count'] = count($jury['vigilantes']);
                    $jury['required_vigilantes'] = $juryModel->calculateRequiredVigilantes((int) $jury['candidates_quota']);

                    // Verificar conflito de sala
                    $conflicts = $this->checkRoomConflictExcluding(
                        $juryModel,
                        (int) $jury['id'],
                        $jury['location'],
                        $jury['room'],
                        $jury['exam_date'],
                        $jury['start_time'],
                        $jury['end_time']
                    );

                    $jury['has_room_conflict'] = !empty($conflicts);
                    $jury['room_conflicts'] = $conflicts;
                }
            }
        }

        // Estatísticas
        $allocationService = new SmartAllocationService();
        $stats = $allocationService->getVacancyAllocationStats($vacancyId);

        // Candidatos aprovados
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
     * API: Obter candidatos aprovados de uma vaga
     */
    public function getVacancyApprovedCandidates(Request $request): void
    {
        $vacancyId = (int) $request->param('id');

        $allocationService = new SmartAllocationService();
        $candidates = $allocationService->getApprovedCandidates($vacancyId);

        Response::json([
            'success' => true,
            'candidates' => $candidates
        ]);
    }

    // ========================================
    // Métodos Privados de Apoio
    // ========================================

    /**
     * Constrói descrição textual da sala
     */
    private function buildRoomDescription(array $room, ?array $roomDetails): string
    {
        $roomText = $room['room'];

        if ($roomDetails) {
            $roomText = $roomDetails['name'] ?: $roomDetails['code'];

            $locationParts = [];
            if (!empty($roomDetails['building'])) {
                $locationParts[] = $roomDetails['building'];
            }
            if (!empty($roomDetails['floor'])) {
                $locationParts[] = $roomDetails['floor'];
            }

            if (!empty($locationParts)) {
                $roomText .= ' (' . implode(' | ', $locationParts) . ')';
            }
        }

        return $roomText;
    }

    /**
     * Verifica conflito de sala
     */
    private function checkRoomConflict(Jury $juryModel, string $location, string $room, string $examDate, string $startTime, string $endTime): array
    {
        return $juryModel->statement(
            "SELECT id, subject, start_time, end_time 
             FROM juries 
             WHERE location = :location 
               AND room = :room 
               AND exam_date = :exam_date
               AND (start_time < :end_time AND end_time > :start_time)",
            [
                'location' => $location,
                'room' => $room,
                'exam_date' => $examDate,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]
        );
    }

    /**
     * Verifica conflito de sala excluindo um júri específico
     */
    private function checkRoomConflictExcluding(Jury $juryModel, int $excludeId, string $location, string $room, string $examDate, string $startTime, string $endTime): array
    {
        return $juryModel->statement(
            "SELECT id, subject, start_time, end_time 
             FROM juries 
             WHERE id != :id
               AND location = :location 
               AND room = :room 
               AND exam_date = :exam_date
               AND (start_time < :end_time AND end_time > :start_time)",
            [
                'id' => $excludeId,
                'location' => $location,
                'room' => $room,
                'exam_date' => $examDate,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]
        );
    }

    /**
     * Atribui vigilantes a um júri
     */
    private function assignVigilantesToJury(int $juryId, array $vigilantes): void
    {
        if (empty($vigilantes))
            return;

        $db = Connection::getInstance();

        foreach ($vigilantes as $vigilanteId) {
            $vigilanteId = (int) $vigilanteId;
            if ($vigilanteId <= 0)
                continue;

            $existsStmt = $db->prepare(
                "SELECT id FROM jury_vigilantes WHERE jury_id = :jury AND vigilante_id = :vigilante"
            );
            $existsStmt->execute(['jury' => $juryId, 'vigilante' => $vigilanteId]);

            if (!$existsStmt->fetch()) {
                $insertStmt = $db->prepare(
                    "INSERT INTO jury_vigilantes (jury_id, vigilante_id, created_at, assigned_by) 
                     VALUES (:jury, :vigilante, NOW(), :assigned_by)"
                );
                $insertStmt->execute([
                    'jury' => $juryId,
                    'vigilante' => $vigilanteId,
                    'assigned_by' => Auth::id()
                ]);

                ActivityLogger::log('jury_vigilantes', $juryId, 'assign_from_wizard', [
                    'vigilante_id' => $vigilanteId
                ]);
            }
        }
    }

    /**
     * Atribui supervisores por bloco
     */
    private function assignBlockSupervisors(Request $request, array $created): int
    {
        $blockSupervisors = $request->input('blockSupervisors') ?? $request->input('supervisors') ?? [];

        if (empty($created) || empty($blockSupervisors)) {
            return 0;
        }

        $db = Connection::getInstance();
        $maxJuriesPerSupervisor = 10;
        $supervisorCount = 0;

        foreach ($blockSupervisors as $blockIndex => $supervisorId) {
            $supervisorId = (int) $supervisorId;
            if ($supervisorId <= 0)
                continue;

            $startIndex = $blockIndex * $maxJuriesPerSupervisor;
            $endIndex = min($startIndex + $maxJuriesPerSupervisor, count($created));

            for ($i = $startIndex; $i < $endIndex; $i++) {
                if (isset($created[$i])) {
                    $juryId = $created[$i]['id'];
                    $updateStmt = $db->prepare(
                        "UPDATE juries SET supervisor_id = :supervisor_id, updated_at = NOW() WHERE id = :jury_id"
                    );
                    $updateStmt->execute([
                        'supervisor_id' => $supervisorId,
                        'jury_id' => $juryId
                    ]);

                    ActivityLogger::log('juries', $juryId, 'assign_supervisor_from_wizard', [
                        'supervisor_id' => $supervisorId,
                        'block_index' => $blockIndex
                    ]);
                }
            }

            $supervisorCount++;
        }

        return $supervisorCount;
    }

    /**
     * Constrói mensagem de conflitos
     */
    private function buildConflictMessage(array $conflicts): string
    {
        if (empty($conflicts)) {
            return '';
        }

        $message = "\n\n⚠️ Atenção: " . count($conflicts) . " sala(s) foram ignoradas por conflito de horário:";
        foreach ($conflicts as $c) {
            $message .= "\n- Sala {$c['room']}: {$c['subject']} ({$c['time']}) conflita com {$c['existing']}";
        }

        return $message;
    }
}
