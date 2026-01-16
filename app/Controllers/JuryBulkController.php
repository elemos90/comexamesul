<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Models\ExamLocation;
use App\Models\ExamRoom;
use App\Models\Jury;
use App\Services\ActivityLogger;
use App\Utils\Auth;
use App\Utils\Flash;
use App\Utils\Validator;

/**
 * Controller para Operações em Lote de Júris
 * 
 * Gerencia criação, atualização e sincronização de múltiplos júris
 * de uma só vez.
 * 
 * @package App\Controllers
 */
class JuryBulkController extends Controller
{
    /**
     * Criar júris em lote por disciplina
     */
    public function createBatch(Request $request)
    {
        $data = $request->only(['subject', 'exam_date', 'start_time', 'end_time', 'location', 'notes']);
        $rooms = $request->input('rooms');

        if (empty($rooms) || !is_array($rooms)) {
            Flash::add('error', 'Adicione pelo menos uma sala.');
            redirect('/juries');
        }

        $validator = new Validator();
        $rules = [
            'subject' => 'required|min:3|max:180',
            'exam_date' => 'required|date',
            'start_time' => 'required|time',
            'end_time' => 'required|time',
            'location' => 'required|max:120',
        ];

        if (!$validator->validate($data, $rules)) {
            Flash::add('error', 'Verifique os dados da disciplina.');
            $_SESSION['errors'] = $validator->errors();
            redirect('/juries');
        }

        // Validar data do júri: não pode ser no passado
        if (strtotime($data['exam_date']) < strtotime(date('Y-m-d'))) {
            Flash::add('error', 'Nao e possivel criar juris para datas passadas.');
            redirect('/juries');
        }

        $juryModel = new Jury();
        $createdCount = 0;

        foreach ($rooms as $room) {
            if (empty($room['room']) || empty($room['candidates_quota'])) {
                continue;
            }

            $juryId = $juryModel->create([
                'subject' => $data['subject'],
                'exam_date' => $data['exam_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'location' => $data['location'],
                'room' => $room['room'],
                'candidates_quota' => (int) $room['candidates_quota'],
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            ActivityLogger::log('juries', $juryId, 'create_batch', [
                'subject' => $data['subject'],
                'room' => $room['room']
            ]);

            $createdCount++;
        }

        Flash::add('success', "Criados {$createdCount} júris para a disciplina {$data['subject']}. Agora arraste vigilantes e supervisores para cada sala.");
        redirect('/juries');
    }

    /**
     * Criar júris em lote por local/data
     */
    public function createLocationBatch(Request $request)
    {
        $location = $request->input('location');
        $examDate = $request->input('exam_date');
        $disciplines = $request->input('disciplines');

        if (empty($location) || empty($examDate)) {
            Flash::add('error', 'Local e data são obrigatórios.');
            redirect('/juries');
        }

        if (empty($disciplines) || !is_array($disciplines)) {
            Flash::add('error', 'Adicione pelo menos uma disciplina com salas.');
            redirect('/juries');
        }

        $validator = new Validator();
        $baseRules = [
            'location' => 'required|max:120',
            'exam_date' => 'required|date',
        ];

        if (!$validator->validate(['location' => $location, 'exam_date' => $examDate], $baseRules)) {
            Flash::add('error', 'Verifique os dados do local.');
            $_SESSION['errors'] = $validator->errors();
            redirect('/juries');
        }

        // Validar data do júri: não pode ser no passado
        if (strtotime($examDate) < strtotime(date('Y-m-d'))) {
            Flash::add('error', 'Nao e possivel criar juris para datas passadas.');
            redirect('/juries');
        }

        $juryModel = new Jury();
        $totalCreated = 0;
        $disciplinesCreated = 0;

        foreach ($disciplines as $discipline) {
            if (empty($discipline['subject']) || empty($discipline['start_time']) || empty($discipline['end_time'])) {
                continue;
            }

            $rooms = $discipline['rooms'] ?? [];
            if (empty($rooms) || !is_array($rooms)) {
                continue;
            }

            $roomsCreated = 0;
            foreach ($rooms as $room) {
                if (empty($room['room']) || empty($room['candidates_quota'])) {
                    continue;
                }

                $juryId = $juryModel->create([
                    'subject' => $discipline['subject'],
                    'exam_date' => $examDate,
                    'start_time' => $discipline['start_time'],
                    'end_time' => $discipline['end_time'],
                    'location' => $location,
                    'room' => $room['room'],
                    'candidates_quota' => (int) $room['candidates_quota'],
                    'notes' => null,
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                ActivityLogger::log('juries', $juryId, 'create_location_batch', [
                    'location' => $location,
                    'subject' => $discipline['subject'],
                    'room' => $room['room']
                ]);

                $roomsCreated++;
                $totalCreated++;
            }

            if ($roomsCreated > 0) {
                $disciplinesCreated++;
            }
        }

        if ($totalCreated === 0) {
            Flash::add('error', 'Nenhum júri foi criado. Verifique os dados inseridos.');
            redirect('/juries');
        }

        Flash::add('success', "Criados {$totalCreated} júri(s) em {$disciplinesCreated} disciplina(s) no local {$location}.");
        redirect('/juries');
    }

    /**
     * Atualizar júris em lote
     */
    public function updateBatch(Request $request)
    {
        $subject = $request->input('subject');
        $examDate = $request->input('exam_date');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');
        $location = $request->input('location');
        $juries = $request->input('juries');

        if (empty($juries) || !is_array($juries)) {
            Response::json(['success' => false, 'message' => 'Nenhum júri para atualizar.'], 400);
            return;
        }

        $validator = new Validator();
        $baseData = [
            'subject' => $subject,
            'exam_date' => $examDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'location' => $location,
        ];

        if (
            !$validator->validate($baseData, [
                'subject' => 'required|min:3|max:180',
                'exam_date' => 'required|date',
                'start_time' => 'required|time',
                'end_time' => 'required|time',
                'location' => 'required|max:120',
            ])
        ) {
            Response::json(['success' => false, 'message' => 'Dados da disciplina inválidos.', 'errors' => $validator->errors()], 400);
            return;
        }

        $juryModel = new Jury();
        $updatedCount = 0;

        foreach ($juries as $juryData) {
            if (empty($juryData['id']) || empty($juryData['room'])) {
                continue;
            }

            $juryId = (int) $juryData['id'];
            $jury = $juryModel->find($juryId);

            if (!$jury) {
                continue;
            }

            $juryModel->update($juryId, [
                'subject' => $subject,
                'exam_date' => $examDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'location' => $location,
                'room' => $juryData['room'],
                'candidates_quota' => (int) ($juryData['candidates_quota'] ?? $jury['candidates_quota']),
                'updated_at' => now(),
            ]);

            ActivityLogger::log('juries', $juryId, 'update_batch', [
                'subject' => $subject,
                'room' => $juryData['room']
            ]);

            $updatedCount++;
        }

        if ($updatedCount === 0) {
            Response::json(['success' => false, 'message' => 'Nenhum júri foi atualizado.'], 400);
            return;
        }

        Response::json(['success' => true, 'message' => "{$updatedCount} júri(s) atualizado(s) com sucesso!"]);
    }

    /**
     * Criar múltiplos júris de uma vez (criação em lote via API)
     */
    public function createBulk(Request $request): void
    {
        try {
            $data = $request->all();

            // Validar campos obrigatórios
            $required = ['vacancy_id', 'subject', 'exam_date', 'start_time', 'end_time', 'location_id', 'rooms'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    Response::json(['success' => false, 'message' => "Campo '{$field}' é obrigatório"], 400);
                    return;
                }
            }

            if (empty($data['rooms']) || !is_array($data['rooms'])) {
                Response::json(['success' => false, 'message' => 'Adicione pelo menos uma sala'], 400);
                return;
            }

            $juryModel = new Jury();
            $roomModel = new ExamRoom();
            $locationModel = new ExamLocation();
            $createdJuries = [];

            // Buscar informações do local
            $location = $locationModel->find((int) $data['location_id']);
            if (!$location) {
                Response::json(['success' => false, 'message' => 'Local não encontrado'], 404);
                return;
            }

            // Para cada sala, criar júri
            foreach ($data['rooms'] as $roomData) {
                $roomId = (int) ($roomData['room_id'] ?? 0);
                $candidates = (int) ($roomData['candidates_quota'] ?? $roomData['candidates'] ?? 0);

                if ($roomId <= 0 || $candidates <= 0) {
                    continue;
                }

                // Buscar informações da sala
                $room = $roomModel->find($roomId);
                if (!$room) {
                    continue;
                }

                // Criar texto descritivo da sala
                $roomText = $this->buildRoomDescription($room);

                // Inserir júri
                $juryId = $juryModel->create([
                    'vacancy_id' => (int) $data['vacancy_id'],
                    'subject' => $data['subject'],
                    'exam_date' => $data['exam_date'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'location_id' => (int) $data['location_id'],
                    'location' => $location['name'],
                    'room_id' => $roomId,
                    'room' => $roomText,
                    'candidates_quota' => $candidates,
                    'vigilantes_capacity' => 2,
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                if ($juryId) {
                    $createdJuries[] = [
                        'id' => $juryId,
                        'room' => $roomData['room_code'] ?? '',
                        'candidates' => $candidates
                    ];
                }
            }

            if (count($createdJuries) > 0) {
                Response::json([
                    'success' => true,
                    'message' => count($createdJuries) . ' júri(s) criado(s) com sucesso para ' . $data['subject'] . '!',
                    'created_count' => count($createdJuries),
                    'created_juries' => $createdJuries
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'Nenhum júri foi criado. Verifique os dados das salas.'
                ], 400);
            }

        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => 'Erro ao criar júris: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sincronizar TODOS os dados de salas em todos os júris
     */
    public function syncRoomNames(Request $request)
    {
        try {
            $juryModel = new Jury();
            $roomModel = new ExamRoom();
            $locationModel = new ExamLocation();

            // Buscar todos os júris com room_id
            $juries = $juryModel->statement(
                "SELECT id, room_id FROM juries WHERE room_id IS NOT NULL"
            );

            $updated = 0;
            $notFound = 0;

            foreach ($juries as $jury) {
                $room = $roomModel->find($jury['room_id']);

                if (!$room) {
                    $notFound++;
                    continue;
                }

                // Buscar dados do local
                $location = $locationModel->find($room['location_id']);

                // Criar texto descritivo da sala
                $roomText = $this->buildRoomDescription($room);

                // Atualizar júri com TODOS os dados da sala
                $juryModel->update($jury['id'], [
                    'room' => $roomText,
                    'room_id' => $room['id'],
                    'location_id' => $room['location_id'],
                    'location' => $location ? $location['name'] : null,
                    'updated_at' => now()
                ]);

                $updated++;
            }

            ActivityLogger::log('juries', 0, 'sync_room_data', [
                'updated' => $updated,
                'not_found' => $notFound,
                'fields' => 'room, room_id, location_id, location'
            ]);

            Response::json([
                'success' => true,
                'message' => "Sincronização concluída!",
                'updated' => $updated,
                'not_found' => $notFound
            ]);

        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => 'Erro ao sincronizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar disciplina/exame inteira (múltiplas salas)
     */
    public function updateDiscipline(Request $request)
    {
        try {
            $vacancyId = (int) $request->param('vacancy_id');
            $subject = $request->input('subject');
            $examDate = $request->input('exam_date');
            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');
            $locationId = (int) $request->input('location_id');

            if (!$vacancyId || !$subject || !$examDate || !$startTime || !$endTime || !$locationId) {
                // Determine which field is missing for better error message
                $missing = [];
                if (!$vacancyId)
                    $missing[] = 'vacancy_id';
                if (!$subject)
                    $missing[] = 'subject';
                if (!$examDate)
                    $missing[] = 'exam_date';
                if (!$startTime)
                    $missing[] = 'start_time';
                if (!$endTime)
                    $missing[] = 'end_time';
                if (!$locationId)
                    $missing[] = 'location_id';

                Response::json(['success' => false, 'message' => 'Dados incompletos: ' . implode(', ', $missing)], 400);
                return;
            }

            $juryModel = new Jury();
            $locationModel = new ExamLocation();

            // Buscar informações do local
            $location = $locationModel->find($locationId);
            if (!$location) {
                Response::json(['success' => false, 'message' => 'Local não encontrado'], 404);
                return;
            }

            // Buscar todos os júris desta vaga com o mesmo subject
            $originalSubject = $request->input('original_subject') ?? $subject;
            $juries = $juryModel->statement(
                "SELECT id FROM juries WHERE vacancy_id = :vacancy_id AND subject = :subject",
                ['vacancy_id' => $vacancyId, 'subject' => $originalSubject]
            );

            if (empty($juries)) {
                Response::json(['success' => false, 'message' => 'Nenhum júri encontrado para atualizar'], 404);
                return;
            }

            $updated = 0;
            foreach ($juries as $jury) {
                $juryModel->update($jury['id'], [
                    'subject' => $subject,
                    'exam_date' => $examDate,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'location_id' => $locationId,
                    'location' => $location['name'],
                    'updated_at' => now()
                ]);

                ActivityLogger::log('juries', $jury['id'], 'update_discipline', [
                    'subject' => $subject,
                    'vacancy_id' => $vacancyId
                ]);

                $updated++;
            }

            Response::json([
                'success' => true,
                'message' => "{$updated} júri(s) atualizado(s) para a disciplina '{$subject}'",
                'updated' => $updated
            ]);

        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => 'Erro ao atualizar disciplina: ' . $e->getMessage()
            ], 500);
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

    // ========================================
    // Métodos Privados de Apoio
    // ========================================

    /**
     * Constrói descrição textual da sala
     */
    private function buildRoomDescription(array $room): string
    {
        $roomText = $room['name'] ?: $room['code'];

        $locationParts = [];
        if (!empty($room['building'])) {
            $locationParts[] = $room['building'];
        }
        if (!empty($room['floor'])) {
            $locationParts[] = $room['floor'];
        }
        if (!empty($locationParts)) {
            $roomText .= ' (' . implode(' | ', $locationParts) . ')';
        }

        return $roomText;
    }
}
