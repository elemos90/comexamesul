<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Models\Jury;
use App\Models\JuryVigilante;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\AllocationService;
use App\Services\SmartAllocationService;
use App\Services\SupervisorAllocationService;
use App\Utils\Auth;

/**
 * Controller para alocação de vigilantes e supervisores
 * 
 * Responsável por:
 * - Atribuir/remover vigilantes
 * - Definir supervisores
 * - Auto-alocação
 * - Swap de vigilantes
 */
class JuryAllocationController extends Controller
{
    /**
     * Atribuir vigilante a um júri
     */
    public function assign(Request $request)
    {
        try {
            $juryId = (int) $request->param('id');
            $vigilanteId = (int) $request->input('vigilante_id');

            $juryModel = new Jury();
            $jury = $juryModel->find($juryId);
            if (!$jury) {
                Response::json(['success' => false, 'message' => 'Júri não encontrado.'], 404);
                return;
            }

            $userModel = new User();
            $vigilante = $userModel->find($vigilanteId);
            if (!$vigilante || $vigilante['role'] !== 'vigilante') {
                Response::json(['success' => false, 'message' => 'Vigilante inválido.'], 422);
                return;
            }
            if ((int) ($vigilante['available_for_vigilance'] ?? 0) !== 1) {
                Response::json(['success' => false, 'message' => 'Vigilante sem disponibilidade activa.'], 422);
                return;
            }

            $juryVigilantes = new JuryVigilante();
            if ($juryVigilantes->vigilanteHasConflict($vigilanteId, $jury['exam_date'], $jury['start_time'], $jury['end_time'])) {
                Response::json(['success' => false, 'message' => 'O vigilante já está alocado a um júri nesse horário.'], 409);
                return;
            }

            // VALIDAÇÃO CRÍTICA: Verificar se a pessoa já é supervisor do mesmo exame
            $isSupervisorOfExam = $juryVigilantes->statement(
                "SELECT j.id, j.room FROM juries j
                 WHERE j.supervisor_id = :vigilante_id
                   AND j.subject = :subject
                   AND j.exam_date = :exam_date
                   AND j.start_time = :start_time
                   AND j.end_time = :end_time",
                [
                    'vigilante_id' => $vigilanteId,
                    'subject' => $jury['subject'],
                    'exam_date' => $jury['exam_date'],
                    'start_time' => $jury['start_time'],
                    'end_time' => $jury['end_time']
                ]
            );

            if (!empty($isSupervisorOfExam)) {
                Response::json([
                    'success' => false,
                    'message' => "❌ {$vigilante['name']} já é SUPERVISOR deste exame.\n\n⚠️ Uma pessoa NÃO pode ser vigilante e supervisor ao mesmo tempo no mesmo exame.\n\nRemova-o(a) primeiro como supervisor ou escolha outro vigilante."
                ], 422);
                return;
            }

            $exists = $juryVigilantes->statement(
                'SELECT * FROM jury_vigilantes WHERE jury_id = :jury AND vigilante_id = :vigilante',
                ['jury' => $juryId, 'vigilante' => $vigilanteId]
            );
            if ($exists) {
                Response::json(['success' => false, 'message' => 'Vigilante já alocado.'], 422);
                return;
            }

            $juryVigilantes->create([
                'jury_id' => $juryId,
                'vigilante_id' => $vigilanteId,
                'assigned_by' => Auth::id(),
                'created_at' => now(),
            ]);

            ActivityLogger::log('jury_vigilantes', $juryId, 'assign', ['vigilante_id' => $vigilanteId]);
            Response::json(['success' => true, 'message' => 'Vigilante alocado com sucesso.']);
        } catch (\Exception $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remover vigilante de um júri
     */
    public function unassign(Request $request)
    {
        try {
            $juryId = (int) $request->param('id');
            $vigilanteId = (int) $request->input('vigilante_id');

            $juryVigilantes = new JuryVigilante();
            $juryVigilantes->execute(
                'DELETE FROM jury_vigilantes WHERE jury_id = :jury AND vigilante_id = :vigilante',
                ['jury' => $juryId, 'vigilante' => $vigilanteId]
            );

            ActivityLogger::log('jury_vigilantes', $juryId, 'unassign', ['vigilante_id' => $vigilanteId]);
            Response::json(['success' => true, 'message' => 'Vigilante removido.']);
        } catch (\Exception $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Definir supervisor para um júri (e todos do mesmo exame)
     */
    public function setSupervisor(Request $request)
    {
        try {
            $juryId = (int) $request->param('id');
            $supervisorId = (int) $request->input('supervisor_id');

            $juryModel = new Jury();
            $jury = $juryModel->find($juryId);

            if (!$jury) {
                Response::json(['success' => false, 'message' => 'Júri não encontrado.'], 404);
                return;
            }

            // Se supervisor_id = 0, remover supervisor
            if ($supervisorId === 0) {
                $affectedJuries = $juryModel->statement(
                    "SELECT id FROM juries 
                     WHERE subject = :subject 
                       AND exam_date = :exam_date 
                       AND start_time = :start_time 
                       AND end_time = :end_time",
                    [
                        'subject' => $jury['subject'],
                        'exam_date' => $jury['exam_date'],
                        'start_time' => $jury['start_time'],
                        'end_time' => $jury['end_time']
                    ]
                );

                $removedCount = 0;
                foreach ($affectedJuries as $affectedJury) {
                    $juryModel->update($affectedJury['id'], [
                        'supervisor_id' => null,
                        'updated_at' => now()
                    ]);
                    $removedCount++;
                }

                ActivityLogger::log('juries', $juryId, 'remove_supervisor', [
                    'previous_supervisor' => $jury['supervisor_id'],
                    'affected_juries' => $removedCount
                ]);

                Response::json([
                    'success' => true,
                    'message' => "Supervisor removido de {$removedCount} júri(s) do mesmo exame."
                ]);
                return;
            }

            // Validar supervisor
            $userModel = new User();
            $supervisor = $userModel->find($supervisorId);

            if (!$supervisor) {
                Response::json(['success' => false, 'message' => 'Supervisor não encontrado.'], 404);
                return;
            }

            if ($supervisor['role'] !== 'vigilante') {
                Response::json(['success' => false, 'message' => 'Apenas vigilantes podem ser supervisores.'], 422);
                return;
            }

            // Verificar conflitos de horário
            $conflicts = $juryModel->statement(
                "SELECT j.id, j.subject, j.start_time, j.end_time 
                 FROM juries j
                 WHERE j.supervisor_id = :supervisor_id
                   AND j.exam_date = :exam_date
                   AND (j.start_time < :end_time AND j.end_time > :start_time)
                   AND NOT (j.subject = :subject 
                           AND j.exam_date = :exam_date2 
                           AND j.start_time = :start_time2 
                           AND j.end_time = :end_time2)",
                [
                    'supervisor_id' => $supervisorId,
                    'exam_date' => $jury['exam_date'],
                    'start_time' => $jury['start_time'],
                    'end_time' => $jury['end_time'],
                    'subject' => $jury['subject'],
                    'exam_date2' => $jury['exam_date'],
                    'start_time2' => $jury['start_time'],
                    'end_time2' => $jury['end_time']
                ]
            );

            if (!empty($conflicts)) {
                $conflict = $conflicts[0];
                Response::json([
                    'success' => false,
                    'message' => "❌ {$supervisor['name']} já é supervisor de '{$conflict['subject']}' no horário {$conflict['start_time']}-{$conflict['end_time']}."
                ], 422);
                return;
            }

            // VALIDAÇÃO CRÍTICA: Verificar se o supervisor já está alocado como vigilante no mesmo exame
            $juryVigilantes = new JuryVigilante();
            $isVigilanteInExam = $juryVigilantes->statement(
                "SELECT j.id, j.room FROM juries j
                 INNER JOIN jury_vigilantes jv ON jv.jury_id = j.id
                 WHERE jv.vigilante_id = :supervisor_id
                   AND j.subject = :subject
                   AND j.exam_date = :exam_date
                   AND j.start_time = :start_time
                   AND j.end_time = :end_time",
                [
                    'supervisor_id' => $supervisorId,
                    'subject' => $jury['subject'],
                    'exam_date' => $jury['exam_date'],
                    'start_time' => $jury['start_time'],
                    'end_time' => $jury['end_time']
                ]
            );

            if (!empty($isVigilanteInExam)) {
                $room = $isVigilanteInExam[0]['room'];
                Response::json([
                    'success' => false,
                    'message' => "❌ {$supervisor['name']} já está alocado(a) como VIGILANTE na sala '{$room}' deste exame.\n\n⚠️ Uma pessoa NÃO pode ser vigilante e supervisor ao mesmo tempo no mesmo exame.\n\nRemova-o(a) primeiro da lista de vigilantes ou escolha outro supervisor."
                ], 422);
                return;
            }

            // Atualizar supervisor em todos os júris do mesmo exame
            $affectedJuries = $juryModel->statement(
                "SELECT id FROM juries 
                 WHERE subject = :subject 
                   AND exam_date = :exam_date 
                   AND start_time = :start_time 
                   AND end_time = :end_time",
                [
                    'subject' => $jury['subject'],
                    'exam_date' => $jury['exam_date'],
                    'start_time' => $jury['start_time'],
                    'end_time' => $jury['end_time']
                ]
            );

            $assignedCount = 0;
            foreach ($affectedJuries as $affectedJury) {
                $juryModel->update($affectedJury['id'], [
                    'supervisor_id' => $supervisorId,
                    'updated_at' => now()
                ]);
                $assignedCount++;
            }

            ActivityLogger::log('juries', $juryId, 'set_supervisor', [
                'supervisor' => $supervisorId,
                'supervisor_name' => $supervisor['name'],
                'affected_juries' => $assignedCount
            ]);

            Response::json([
                'success' => true,
                'message' => "Supervisor '{$supervisor['name']}' atribuído a {$assignedCount} júri(s)."
            ]);
        } catch (\Exception $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Verificar se vigilante/supervisor pode ser alocado
     */
    public function canAssign(Request $request)
    {
        try {
            $vigilanteId = (int) $request->input('vigilante_id');
            $juryId = (int) $request->input('jury_id');
            $type = $request->input('type', 'vigilante');

            $allocationService = new AllocationService();

            if ($type === 'supervisor') {
                $result = $allocationService->canAssignSupervisor($vigilanteId, $juryId);
            } else {
                $result = $allocationService->canAssignVigilante($vigilanteId, $juryId);
            }

            Response::json($result);
        } catch (\Exception $e) {
            Response::json(['can_assign' => false, 'reason' => $e->getMessage()], 500);
        }
    }

    /**
     * Auto-alocação de um júri específico
     */
    public function autoAllocateJury(Request $request)
    {
        try {
            $juryId = (int) $request->input('jury_id');

            $allocationService = new AllocationService();
            $result = $allocationService->autoAllocateJury($juryId, Auth::id());

            if ($result['success']) {
                ActivityLogger::log('juries', $juryId, 'auto_allocate', [
                    'allocated' => $result['allocated'] ?? 0
                ]);
            }

            Response::json($result);
        } catch (\Exception $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Auto-alocação de todos os júris de uma disciplina
     */
    public function autoAllocateDiscipline(Request $request)
    {
        try {
            $subject = $request->input('subject');
            $examDate = $request->input('exam_date');

            if (empty($subject) || empty($examDate)) {
                Response::json([
                    'success' => false,
                    'message' => 'Disciplina e data são obrigatórios'
                ], 400);
                return;
            }

            $allocationService = new AllocationService();
            $result = $allocationService->autoAllocateDiscipline($subject, $examDate, Auth::id());

            if ($result['success']) {
                ActivityLogger::log('juries', 0, 'auto_allocate_discipline', [
                    'subject' => $subject,
                    'exam_date' => $examDate,
                    'total_allocated' => $result['total_allocated'] ?? 0
                ]);
            }

            Response::json($result);
        } catch (\Exception $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Auto-alocação de todos os júris de uma vaga
     */
    public function autoAllocateVacancy(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Limpar todas as alocações de uma vaga
     */
    public function clearVacancyAllocations(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Trocar vigilantes (swap)
     */
    public function swapVigilantes(Request $request)
    {
        try {
            $fromVigilanteId = (int) $request->input('from_vigilante_id');
            $toVigilanteId = (int) $request->input('to_vigilante_id');
            $juryId = (int) $request->input('jury_id');

            if (!$fromVigilanteId || !$toVigilanteId || !$juryId) {
                Response::json([
                    'success' => false,
                    'message' => 'Parâmetros inválidos'
                ], 400);
                return;
            }

            $allocationService = new AllocationService();
            $result = $allocationService->swapVigilantes($fromVigilanteId, $toVigilanteId, $juryId, Auth::id());

            if ($result['success']) {
                ActivityLogger::log('jury_vigilantes', $juryId, 'swap', [
                    'from' => $fromVigilanteId,
                    'to' => $toVigilanteId
                ]);
            }

            Response::json($result);
        } catch (\Exception $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Atribuir supervisor em lote
     */
    public function bulkAssignSupervisor(Request $request): void
    {
        try {
            if (ob_get_length())
                ob_clean();

            $juryIds = $request->input('jury_ids');
            $supervisorId = $request->input('supervisor_id');

            if (empty($juryIds) || !is_array($juryIds)) {
                Response::json([
                    'success' => false,
                    'message' => 'Selecione pelo menos um júri'
                ], 400);
                return;
            }

            if ($supervisorId === '' || $supervisorId === null) {
                Response::json([
                    'success' => false,
                    'message' => 'Selecione um tipo de supervisão'
                ], 400);
                return;
            }

            $supervisorId = (int) $supervisorId;
            $juryModel = new Jury();
            $updated = 0;

            foreach ($juryIds as $juryId) {
                $result = $juryModel->update((int) $juryId, [
                    'supervisor_id' => $supervisorId,
                    'updated_at' => now()
                ]);

                if ($result) {
                    $updated++;
                    ActivityLogger::log('juries', (int) $juryId, 'assign_supervisor', [
                        'supervisor_id' => $supervisorId,
                        'type' => $supervisorId == 0 ? 'committee' : 'individual',
                        'assigned_by' => Auth::id()
                    ]);
                }
            }

            $supervisorLabel = $supervisorId == 0 ? 'Comissão de Exames' : 'Supervisor';

            Response::json([
                'success' => true,
                'message' => "{$supervisorLabel} atribuído a {$updated} júri(s) com sucesso",
                'updated' => $updated
            ]);
        } catch (\Exception $e) {
            if (ob_get_length())
                ob_clean();
            Response::json([
                'success' => false,
                'message' => 'Erro ao atribuir supervisor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-alocação equilibrada de supervisores por bloco
     * 
     * Distribui os júris de uma disciplina/horário entre múltiplos supervisores
     * de forma equilibrada, respeitando o limite MAX_JURIS_POR_SUPERVISOR
     */
    public function autoAllocateSupervisors(Request $request): void
    {
        try {
            if (ob_get_length())
                ob_clean();

            $subject = $request->input('subject');
            $examDate = $request->input('exam_date');
            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');
            $vacancyId = $request->input('vacancy_id');

            if (empty($subject) || empty($examDate) || empty($startTime) || empty($endTime)) {
                Response::json([
                    'success' => false,
                    'message' => 'Parâmetros obrigatórios em falta (subject, exam_date, start_time, end_time)'
                ], 400);
                return;
            }

            $allocationService = new SupervisorAllocationService();

            // Buscar todos os júris do bloco
            $juries = $allocationService->getJuriesInBlock(
                $subject,
                $examDate,
                $startTime,
                $endTime,
                $vacancyId ? (int) $vacancyId : null
            );

            if (empty($juries)) {
                Response::json([
                    'success' => false,
                    'message' => 'Nenhum júri encontrado para os critérios especificados.'
                ], 404);
                return;
            }

            $juryIds = array_column($juries, 'id');

            // Executar alocação equilibrada
            $result = $allocationService->allocateBlockedSupervisors(
                $juryIds,
                $vacancyId ? (int) $vacancyId : null
            );

            ActivityLogger::log('supervisor_allocation', null, 'auto_allocate_block', [
                'subject' => $subject,
                'exam_date' => $examDate,
                'start_time' => $startTime,
                'jury_count' => count($juryIds),
                'result' => $result['success'] ? 'success' : 'failed',
                'allocated_by' => Auth::id()
            ]);

            Response::json($result);
        } catch (\Exception $e) {
            if (ob_get_length())
                ob_clean();
            Response::json([
                'success' => false,
                'message' => 'Erro na auto-alocação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Definir supervisor para um ÚNICO júri (sem propagar para outros)
     * 
     * Usado para ajustes manuais via drag-and-drop
     */
    public function setSupervisorSingle(Request $request): void
    {
        try {
            if (ob_get_length())
                ob_clean();

            $juryId = (int) $request->param('id');
            $supervisorId = (int) $request->input('supervisor_id');

            $allocationService = new SupervisorAllocationService();
            $result = $allocationService->reassignJury($juryId, $supervisorId);

            if ($result['success']) {
                ActivityLogger::log('juries', $juryId, 'set_supervisor_single', [
                    'supervisor_id' => $supervisorId,
                    'assigned_by' => Auth::id()
                ]);
            }

            Response::json($result);
        } catch (\Exception $e) {
            if (ob_get_length())
                ob_clean();
            Response::json([
                'success' => false,
                'message' => 'Erro ao definir supervisor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remover supervisor de um único júri
     */
    public function removeSupervisorFromJury(Request $request): void
    {
        try {
            if (ob_get_length())
                ob_clean();

            $juryId = (int) $request->param('id');
            $juryModel = new Jury();
            $jury = $juryModel->find($juryId);

            if (!$jury) {
                Response::json(['success' => false, 'message' => 'Júri não encontrado.'], 404);
                return;
            }

            // Remover supervisor de todos os júris do mesmo exame (bloco)
            $affectedJuries = $juryModel->statement(
                "SELECT id FROM juries 
                 WHERE subject = :subject 
                   AND exam_date = :exam_date 
                   AND start_time = :start_time 
                   AND end_time = :end_time",
                [
                    'subject' => $jury['subject'],
                    'exam_date' => $jury['exam_date'],
                    'start_time' => $jury['start_time'],
                    'end_time' => $jury['end_time']
                ]
            );

            $removedCount = 0;
            foreach ($affectedJuries as $affectedJury) {
                $juryModel->update($affectedJury['id'], [
                    'supervisor_id' => null,
                    'updated_at' => now()
                ]);
                $removedCount++;
            }

            ActivityLogger::log('juries', $juryId, 'remove_supervisor_block', [
                'removed_by' => Auth::id(),
                'affected_juries' => $removedCount
            ]);

            Response::json([
                'success' => true,
                'message' => "Supervisor removido de {$removedCount} júri(s)."
            ]);
        } catch (\Exception $e) {
            if (ob_get_length())
                ob_clean();
            Response::json([
                'success' => false,
                'message' => 'Erro ao remover supervisor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estatísticas de supervisão para uma vaga
     */
    public function getSupervisorStats(Request $request): void
    {
        try {
            if (ob_get_length())
                ob_clean();

            $vacancyId = (int) $request->param('vacancyId');

            if (!$vacancyId) {
                Response::json([
                    'success' => false,
                    'message' => 'ID da vaga não fornecido'
                ], 400);
                return;
            }

            $allocationService = new SupervisorAllocationService();
            $stats = $allocationService->getSupervisionStats($vacancyId);
            $config = $allocationService->getConfig();

            Response::json([
                'success' => true,
                'stats' => $stats,
                'config' => [
                    'max_juries_per_supervisor' => $config['max_juries_per_supervisor']
                ]
            ]);
        } catch (\Exception $e) {
            if (ob_get_length())
                ob_clean();
            Response::json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter carga de um supervisor específico numa vaga
     */
    public function getSupervisorLoad(Request $request): void
    {
        try {
            if (ob_get_length())
                ob_clean();

            $supervisorId = (int) $request->param('supervisorId');
            $vacancyId = (int) $request->input('vacancy_id');

            if (!$supervisorId) {
                Response::json([
                    'success' => false,
                    'message' => 'ID do supervisor não fornecido'
                ], 400);
                return;
            }

            $allocationService = new SupervisorAllocationService();
            $load = $allocationService->getSupervisorLoad($supervisorId, $vacancyId);
            $config = $allocationService->getConfig();

            Response::json([
                'success' => true,
                'supervisor_id' => $supervisorId,
                'load' => $load,
                'max_load' => $config['max_juries_per_supervisor'],
                'status' => $load > $config['max_juries_per_supervisor'] ? 'overloaded' :
                    ($load == $config['max_juries_per_supervisor'] ? 'full' : 'available')
            ]);
        } catch (\Exception $e) {
            if (ob_get_length())
                ob_clean();
            Response::json([
                'success' => false,
                'message' => 'Erro ao obter carga: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Obter status de alocação de vigilantes para um júri
     */
    public function getVigilanteStatus(Request $request): void
    {
        try {
            if (ob_get_length())
                ob_clean();

            $juryId = (int) $request->param('id');
            if (!$juryId) {
                Response::json(['success' => false, 'message' => 'ID do júri inválido'], 400);
                return;
            }

            $allocationService = new AllocationService();
            $status = $allocationService->getVigilanteAllocationStatus($juryId);

            Response::json(['success' => true, 'status' => $status]);
        } catch (\Exception $e) {
            if (ob_get_length())
                ob_clean();
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Auto-distribuir vigilantes para um bloco de exames
     * Respeita o mínimo de vigilantes calculado por candidatos
     */
    public function autoDistributeVigilantes(Request $request): void
    {
        try {
            if (ob_get_length())
                ob_clean();

            $subject = $request->input('subject');
            $examDate = $request->input('exam_date');
            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');
            $vacancyId = $request->input('vacancy_id');

            if (empty($subject) || empty($examDate) || empty($startTime) || empty($endTime)) {
                Response::json([
                    'success' => false,
                    'message' => 'Parâmetros obrigatórios em falta'
                ], 400);
                return;
            }

            // 1. Buscar júris do bloco
            $allocationService = new SupervisorAllocationService(); // Reutilizando método auxiliar
            $juries = $allocationService->getJuriesInBlock(
                $subject,
                $examDate,
                $startTime,
                $endTime,
                $vacancyId ? (int) $vacancyId : null
            );

            if (empty($juries)) {
                Response::json(['success' => false, 'message' => 'Nenhum júri encontrado'], 404);
                return;
            }

            $juryIds = array_column($juries, 'id');

            // 2. Executar auto-distribuição de vigilantes
            $vigilanteService = new AllocationService();
            $result = $vigilanteService->autoDistributeVigilantes($juryIds, Auth::id());

            ActivityLogger::log('jury_vigilantes', 0, 'auto_distribute_block', [
                'subject' => $subject,
                'exam_date' => $examDate,
                'jury_count' => count($juryIds),
                'allocated' => $result['allocated'] ?? 0,
                'assigned_by' => Auth::id()
            ]);

            Response::json($result);

        } catch (\Exception $e) {
            if (ob_get_length())
                ob_clean();
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
