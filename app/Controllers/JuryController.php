<?php

namespace App\Controllers;

use App\Database\Connection;
use App\Http\Request;
use App\Http\Response;
use App\Models\ExamReport;
use App\Models\ExamVacancy;
use App\Models\Jury;
use App\Models\JuryVigilante;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\AllocationPlannerService;
use App\Services\StatsCacheService;
use App\Utils\Auth;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Validator;

class JuryController extends Controller
{
    /**
     * Invalidar cache do dashboard quando júris são modificados
     */
    private function invalidateCache(): void
    {
        $cache = new StatsCacheService();
        $cache->forget('dashboard_stats');
        
        // Invalidar cache de todos os vigilantes
        $cache->flush(); // Limpa todo cache para garantir atualização
    }
    public function index(): string
    {
        $user = Auth::user();
        $juryModel = new Jury();
        $juryVigilantes = new JuryVigilante();
        $userModel = new User();
        $vacancyModel = new \App\Models\ExamVacancy();
        
        // NOVO: Filtro por vaga (padrão = vaga aberta atual)
        $vacancyId = isset($_GET['vacancy_id']) ? $_GET['vacancy_id'] : 'current';
        $vacancy = null;
        $allVacancies = [];
        
        // Se vacancy_id = 'all', mostrar todas
        if ($vacancyId === 'all') {
            $vacancyId = null;
        }
        // Se vacancy_id = 'current' ou vazio, pegar vaga aberta
        elseif ($vacancyId === 'current' || empty($vacancyId)) {
            $openVacancies = $vacancyModel->openVacancies();
            $vacancyId = !empty($openVacancies) ? (int) $openVacancies[0]['id'] : null;
        } else {
            $vacancyId = (int) $vacancyId;
        }
        
        // Buscar dados da vaga atual (se houver)
        if ($vacancyId) {
            $vacancy = $vacancyModel->find($vacancyId);
        }
        
        // Buscar todas as vagas para o dropdown (apenas para coordenador/membro)
        if ($user['role'] !== 'vigilante') {
            $allVacancies = $vacancyModel->statement('SELECT * FROM exam_vacancies ORDER BY created_at DESC LIMIT 10');
        }

        if ($user['role'] === 'vigilante') {
            // Vigilantes veem apenas seus próprios júris (filtrado por vaga se aplicável)
            if ($vacancyId) {
                $juries = $juryModel->statement(
                    "SELECT j.* FROM jury_vigilantes jv 
                     INNER JOIN juries j ON j.id = jv.jury_id 
                     WHERE jv.vigilante_id = :user AND j.vacancy_id = :vacancy_id
                     ORDER BY j.exam_date, j.start_time",
                    ['user' => (int) $user['id'], 'vacancy_id' => $vacancyId]
                );
            } else {
                $juries = $juryModel->statement(
                    "SELECT j.* FROM jury_vigilantes jv 
                     INNER JOIN juries j ON j.id = jv.jury_id 
                     WHERE jv.vigilante_id = :user 
                     ORDER BY j.exam_date, j.start_time",
                    ['user' => (int) $user['id']]
                );
            }
            
            // EAGER LOADING: Carregar todos vigilantes de uma vez
            $juryIds = array_column($juries, 'id');
            $allVigilantes = $juryVigilantes->getVigilantesForMultipleJuries($juryIds);
            
            // Agrupar vigilantes por jury_id
            $vigilantesByJury = [];
            foreach ($allVigilantes as $v) {
                $vigilantesByJury[$v['jury_id']][] = $v;
            }
            
            // Associar vigilantes aos júris
            foreach ($juries as &$jury) {
                $jury['vigilantes'] = $vigilantesByJury[$jury['id']] ?? [];
                $jury['has_report'] = $juryModel->hasSupervisorReport((int) $jury['id']);
            }
            unset($jury);
            $availableVigilantes = [];
            $supervisors = [];
        } else {
            // Coordenadores/membros veem júris filtrados por vaga
            if ($vacancyId) {
                $juries = $juryModel->statement(
                    "SELECT j.*, 
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
                     WHERE j.vacancy_id = :vacancy_id
                     ORDER BY j.subject, j.exam_date, j.start_time, j.room",
                    ['vacancy_id' => $vacancyId]
                );
            } else {
                $juries = $juryModel->withAllocations();
            }
            
            // EAGER LOADING: Carregar todos vigilantes de uma vez
            $juryIds = array_column($juries, 'id');
            $allVigilantes = $juryVigilantes->getVigilantesForMultipleJuries($juryIds);
            
            // Agrupar vigilantes por jury_id
            $vigilantesByJury = [];
            foreach ($allVigilantes as $v) {
                $vigilantesByJury[$v['jury_id']][] = $v;
            }
            
            // Associar vigilantes aos júris
            foreach ($juries as &$jury) {
                $jury['vigilantes'] = $vigilantesByJury[$jury['id']] ?? [];
                $jury['has_report'] = $juryModel->hasSupervisorReport((int) $jury['id']);
            }
            unset($jury);
            
            $availableVigilantes = $userModel->getVigilantesWithWorkload();
            $supervisors = $userModel->statement("SELECT u.* FROM users u WHERE supervisor_eligible = 1 ORDER BY u.name");
            
            // Preparar júris agrupados com vigilantes carregados
            // CORREÇÃO: Filtrar groupedJuries pela vaga se aplicável
            if ($vacancyId) {
                // Agrupar manualmente os júris já filtrados
                $groupedJuries = [];
                foreach ($juries as $jury) {
                    $key = $jury['subject'] . '|' . $jury['exam_date'] . '|' . $jury['start_time'] . '|' . $jury['end_time'] . '|' . $jury['location'];
                    if (!isset($groupedJuries[$key])) {
                        $groupedJuries[$key] = [
                            'subject' => $jury['subject'],
                            'exam_date' => $jury['exam_date'],
                            'start_time' => $jury['start_time'],
                            'end_time' => $jury['end_time'],
                            'location' => $jury['location'],
                            'juries' => []
                        ];
                    }
                    $groupedJuries[$key]['juries'][] = $jury;
                }
                $groupedJuries = array_values($groupedJuries);
            } else {
                $groupedJuries = $juryModel->getGroupedBySubjectAndTime();
            }
            
            // EAGER LOADING para júris agrupados
            $allGroupedJuryIds = [];
            foreach ($groupedJuries as $group) {
                foreach ($group['juries'] as $jury) {
                    $allGroupedJuryIds[] = $jury['id'];
                }
            }
            
            if (!empty($allGroupedJuryIds)) {
                $groupedVigilantes = $juryVigilantes->getVigilantesForMultipleJuries($allGroupedJuryIds);
                
                // Agrupar vigilantes por jury_id
                $groupedVigilantesByJury = [];
                foreach ($groupedVigilantes as $v) {
                    $groupedVigilantesByJury[$v['jury_id']][] = $v;
                }
                
                // Associar aos júris agrupados
                foreach ($groupedJuries as &$group) {
                    foreach ($group['juries'] as &$jury) {
                        $jury['vigilantes'] = $groupedVigilantesByJury[$jury['id']] ?? [];
                        $jury['has_report'] = $juryModel->hasSupervisorReport((int) $jury['id']);
                    }
                    unset($jury);
                }
                unset($group);
            }
        }

        // Se for vigilante, usar view antiga; se não, usar view de impressão
        if ($user['role'] === 'vigilante') {
            return $this->view('juries/index_vigilante', [
                'juries' => $juries,
                'user' => $user,
                'vacancy' => $vacancy,
                'vacancyId' => $vacancyId,
                'allVacancies' => $allVacancies,
            ]);
        }
        
        return $this->view('juries/index_print', [
            'juries' => $juries,
            'groupedJuries' => $groupedJuries ?? [],
            'vigilantes' => $availableVigilantes,
            'supervisors' => $supervisors,
            'user' => $user,
            'vacancy' => $vacancy,
            'vacancyId' => $vacancyId,
            'allVacancies' => $allVacancies,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->only(['subject','exam_date','start_time','end_time','location','room','candidates_quota','notes']);
        $validator = new Validator();
        $rules = [
            'subject' => 'required|min:3|max:180',
            'exam_date' => 'required|date',
            'start_time' => 'required|time',
            'end_time' => 'required|time',
            'location' => 'required|max:120',
            'room' => 'required|max:60',
            'candidates_quota' => 'required|numeric',
        ];
        if (!$validator->validate($data, $rules)) {
            Flash::add('error', 'Verifique os dados do juri.');
            $_SESSION['errors'] = $validator->errors();
            redirect('/juries');
        }
        
        // Validar data do júri: não pode ser no passado
        if (strtotime($data['exam_date']) < strtotime(date('Y-m-d'))) {
            Flash::add('error', 'Nao e possivel criar juris para datas passadas.');
            redirect('/juries');
        }
        
        $juryModel = new Jury();
        
        // Verificar se existem júris da mesma disciplina e data com horários diferentes
        $existingJuries = $juryModel->statement(
            "SELECT * FROM juries WHERE subject = :subject AND exam_date = :date",
            ['subject' => $data['subject'], 'date' => $data['exam_date']]
        );
        
        if (!empty($existingJuries)) {
            $firstJury = $existingJuries[0];
            if ($firstJury['start_time'] !== $data['start_time'] || $firstJury['end_time'] !== $data['end_time']) {
                Flash::add('warning', 'AVISO: Júris da mesma disciplina devem ter o mesmo horário para evitar fraudes. Horário esperado: ' . substr($firstJury['start_time'], 0, 5) . ' - ' . substr($firstJury['end_time'], 0, 5));
            }
        }
        
        $juryId = $juryModel->create([
            'subject' => $data['subject'],
            'exam_date' => $data['exam_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'location' => $data['location'],
            'room' => $data['room'],
            'candidates_quota' => (int) $data['candidates_quota'],
            'notes' => $data['notes'] ?? null,
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        ActivityLogger::log('juries', $juryId, 'create');
        
        // Invalidar cache
        $this->invalidateCache();
        
        Flash::add('success', 'Juri criado com sucesso.');
        redirect('/juries');
    }

    public function update(Request $request)
    {
        $id = (int) $request->param('id');
        $data = $request->only(['subject','exam_date','start_time','end_time','location','room','candidates_quota','notes']);
        $juryModel = new Jury();
        $jury = $juryModel->find($id);
        if (!$jury) {
            Flash::add('error', 'Juri nao encontrado.');
            redirect('/juries');
        }
        $juryModel->update($id, array_merge($data, ['updated_at' => now()]));
        ActivityLogger::log('juries', $id, 'update');
        Flash::add('success', 'Juri atualizado.');
        redirect('/juries');
    }

    public function delete(Request $request)
    {
        $id = (int) $request->param('id');
        $juryModel = new Jury();
        $jury = $juryModel->find($id);
        if (!$jury) {
            Flash::add('error', 'Juri nao encontrado.');
            redirect('/juries');
        }
        $juryModel->delete($id);
        ActivityLogger::log('juries', $id, 'delete');
        Flash::add('success', 'Juri eliminado.');
        redirect('/juries');
    }

    public function assign(Request $request)
    {
        $juryId = (int) $request->param('id');
        $vigilanteId = (int) $request->input('vigilante_id');

        $juryModel = new Jury();
        $jury = $juryModel->find($juryId);
        if (!$jury) {
            Response::json(['message' => 'Juri nao encontrado.'], 404);
        }

        $userModel = new User();
        $vigilante = $userModel->find($vigilanteId);
        if (!$vigilante || $vigilante['role'] !== 'vigilante') {
            Response::json(['message' => 'Vigilante invalido.'], 422);
        }
        if ((int) ($vigilante['available_for_vigilance'] ?? 0) !== 1) {
            Response::json(['message' => 'Vigilante sem disponibilidade activa.'], 422);
        }

        $juryVigilantes = new JuryVigilante();
        if ($juryVigilantes->vigilanteHasConflict($vigilanteId, $jury['exam_date'], $jury['start_time'], $jury['end_time'])) {
            Response::json(['message' => 'O vigilante ja esta alocado a um juri nesse horario.'], 409);
        }

        $exists = $juryVigilantes->statement(
            'SELECT * FROM jury_vigilantes WHERE jury_id = :jury AND vigilante_id = :vigilante',
            ['jury' => $juryId, 'vigilante' => $vigilanteId]
        );
        if ($exists) {
            Response::json(['message' => 'Vigilante ja alocado.'], 422);
        }

        $juryVigilantes->create([
            'jury_id' => $juryId,
            'vigilante_id' => $vigilanteId,
            'assigned_by' => Auth::id(),
            'created_at' => now(),
        ]);
        ActivityLogger::log('jury_vigilantes', $juryId, 'assign', ['vigilante_id' => $vigilanteId]);
        Response::json(['message' => 'Vigilante alocado com sucesso.']);
    }

    public function unassign(Request $request)
    {
        $juryId = (int) $request->param('id');
        $vigilanteId = (int) $request->input('vigilante_id');
        $juryVigilantes = new JuryVigilante();
        $juryVigilantes->execute(
            'DELETE FROM jury_vigilantes WHERE jury_id = :jury AND vigilante_id = :vigilante',
            ['jury' => $juryId, 'vigilante' => $vigilanteId]
        );
        ActivityLogger::log('jury_vigilantes', $juryId, 'unassign', ['vigilante_id' => $vigilanteId]);
        Response::json(['message' => 'Vigilante removido.']);
    }

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
                // Remover supervisor de todos os júris do mesmo exame
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
            
            // Verificar se o vigilante está disponível (não precisa ser obrigatoriamente elegível)
            if ($supervisor['role'] !== 'vigilante') {
                Response::json(['success' => false, 'message' => 'Apenas vigilantes podem ser supervisores.'], 422);
                return;
            }
            
            // Verificar se o supervisor já tem conflito de horário
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
                    'message' => "❌ {$supervisor['name']} já é supervisor de '{$conflict['subject']}' no horário {$conflict['start_time']}-{$conflict['end_time']}.\n\nEscolha outro vigilante ou remova-o do outro exame primeiro."
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
                'message' => "{$supervisor['name']} atribuído como supervisor de {$assignedCount} júri(s)."
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao definir supervisor: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            Response::json([
                'success' => false,
                'message' => 'Erro ao definir supervisor: ' . $e->getMessage()
            ], 500);
        }
    }

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
        
        Flash::add('success', "Criados {$totalCreated} júris para {$disciplinesCreated} disciplina(s) no local '{$location}' em " . date('d/m/Y', strtotime($examDate)) . ". Agora aloque vigilantes e supervisores.");
        redirect('/juries');
    }

    public function updateQuick(Request $request)
    {
        // Validar CSRF
        if (!Csrf::validate($request)) {
            Response::json(['success' => false, 'message' => 'Token CSRF inválido'], 403);
            return;
        }
        
        $id = (int) $request->param('id');
        $roomId = $request->input('room_id');
        $candidatesQuota = $request->input('candidates_quota');
        $notes = $request->input('notes');
        
        $juryModel = new Jury();
        $jury = $juryModel->find($id);
        
        if (!$jury) {
            Response::json(['success' => false, 'message' => 'Júri não encontrado.'], 404);
            return;
        }
        
        // Validar dados
        $validator = new Validator();
        if (!$validator->validate(['room_id' => $roomId, 'candidates_quota' => $candidatesQuota], [
            'room_id' => 'required|int',
            'candidates_quota' => 'required|int|min:1',
        ])) {
            Response::json(['success' => false, 'message' => 'Dados inválidos.', 'errors' => $validator->errors()], 400);
            return;
        }
        
        // Buscar informações da sala
        $roomModel = new \App\Models\ExamRoom();
        $room = $roomModel->find((int) $roomId);
        
        if (!$room) {
            Response::json(['success' => false, 'message' => 'Sala não encontrada.'], 404);
            return;
        }
        
        // Verificar capacidade da sala
        if ((int) $candidatesQuota > (int) $room['capacity']) {
            Response::json([
                'success' => false, 
                'message' => "O número de candidatos ({$candidatesQuota}) excede a capacidade da sala ({$room['capacity']})."
            ], 400);
            return;
        }
        
        // Atualizar júri
        $juryModel->update($id, [
            'room_id' => (int) $roomId,
            'room' => $room['code'],
            'candidates_quota' => (int) $candidatesQuota,
            'notes' => $notes,
            'updated_at' => now(),
        ]);
        
        ActivityLogger::log('juries', $id, 'update_quick', [
            'room_id' => $roomId,
            'room' => $room['code'],
            'candidates_quota' => $candidatesQuota,
            'notes' => $notes
        ]);
        
        Response::json(['success' => true, 'message' => 'Sala atualizada com sucesso!']);
    }

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
        }
        
        $validator = new Validator();
        $baseData = [
            'subject' => $subject,
            'exam_date' => $examDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'location' => $location,
        ];
        
        if (!$validator->validate($baseData, [
            'subject' => 'required|min:3|max:180',
            'exam_date' => 'required|date',
            'start_time' => 'required|time',
            'end_time' => 'required|time',
            'location' => 'required|max:120',
        ])) {
            Response::json(['success' => false, 'message' => 'Dados da disciplina inválidos.', 'errors' => $validator->errors()], 400);
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
        }
        
        Response::json(['success' => true, 'message' => "{$updatedCount} júri(s) atualizado(s) com sucesso!"]);
    }

    /**
     * API: Verifica se um vigilante/supervisor pode ser alocado a um júri
     */
    public function canAssign(Request $request)
    {
        $vigilanteId = (int) $request->input('vigilante_id');
        $juryId = (int) $request->input('jury_id');
        $type = $request->input('type', 'vigilante'); // 'vigilante' ou 'supervisor'
        
        $allocationService = new \App\Services\AllocationService();
        
        if ($type === 'supervisor') {
            $result = $allocationService->canAssignSupervisor($vigilanteId, $juryId);
        } else {
            $result = $allocationService->canAssignVigilante($vigilanteId, $juryId);
        }
        
        Response::json($result);
    }
    
    /**
     * API: Auto-alocação rápida (júri específico)
     */
    public function autoAllocateJury(Request $request)
    {
        $juryId = (int) $request->input('jury_id');
        
        $allocationService = new \App\Services\AllocationService();
        $result = $allocationService->autoAllocateJury($juryId, Auth::id());
        
        if ($result['success']) {
            ActivityLogger::log('juries', $juryId, 'auto_allocate', [
                'allocated' => $result['allocated'] ?? 0
            ]);
        }
        
        Response::json($result);
    }
    
    /**
     * API: Auto-alocação completa (toda disciplina)
     */
    public function autoAllocateDiscipline(Request $request)
    {
        $subject = $request->input('subject');
        $examDate = $request->input('exam_date');
        
        if (empty($subject) || empty($examDate)) {
            Response::json([
                'success' => false,
                'message' => 'Disciplina e data são obrigatórios'
            ], 400);
        }
        
        $allocationService = new \App\Services\AllocationService();
        $result = $allocationService->autoAllocateDiscipline($subject, $examDate, Auth::id());
        
        if ($result['success']) {
            ActivityLogger::log('juries', 0, 'auto_allocate_discipline', [
                'subject' => $subject,
                'exam_date' => $examDate,
                'total_allocated' => $result['total_allocated'] ?? 0
            ]);
        }
        
        Response::json($result);
    }
    
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
        $juryId = (int) $request->param('id');
        
        $allocationService = new \App\Services\AllocationService();
        $vigilantes = $allocationService->getEligibleVigilantes($juryId);
        
        Response::json([
            'success' => true,
            'vigilantes' => $vigilantes
        ]);
    }
    
    /**
     * API: Obter supervisores elegíveis (para um júri ou todos)
     */
    public function getEligibleSupervisors(Request $request)
    {
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
                 WHERE u.supervisor_eligible = 1
                 ORDER BY IFNULL(vw.supervision_count, 0) ASC, u.name"
            );
            
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
     * API: Trocar vigilantes (swap)
     */
    public function swapVigilantes(Request $request)
    {
        $fromVigilanteId = (int) $request->input('from_vigilante_id');
        $toVigilanteId = (int) $request->input('to_vigilante_id');
        $juryId = (int) $request->input('jury_id');
        
        if (!$fromVigilanteId || !$toVigilanteId || !$juryId) {
            Response::json([
                'success' => false,
                'message' => 'Parâmetros inválidos'
            ], 400);
        }
        
        $allocationService = new \App\Services\AllocationService();
        $result = $allocationService->swapVigilantes($fromVigilanteId, $toVigilanteId, $juryId, Auth::id());
        
        if ($result['success']) {
            ActivityLogger::log('jury_vigilantes', $juryId, 'swap', [
                'from' => $fromVigilanteId,
                'to' => $toVigilanteId
            ]);
        }
        
        Response::json($result);
    }
    
    /**
     * API: Obter métricas detalhadas (KPIs)
     */
    public function getMetrics(Request $request)
    {
        $allocationService = new \App\Services\AllocationService();
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
             WHERE u.supervisor_eligible = 1 
             ORDER BY vw.workload_score ASC, u.name"
        );
        
        Response::json([
            'success' => true,
            'supervisors' => $supervisors,
            'total' => count($supervisors)
        ]);
    }
    
    /**
     * Página de planejamento com drag-and-drop
     */
    public function planning(Request $request): string
    {
        $user = Auth::user();
        $juryModel = new Jury();
        $allocationService = new \App\Services\AllocationService();
        $vacancyModel = new \App\Models\ExamVacancy();
        
        // NOVO: Filtro por vaga (padrão = vaga aberta atual)
        $vacancyId = isset($_GET['vacancy_id']) ? $_GET['vacancy_id'] : 'current';
        $vacancy = null;
        $allVacancies = [];
        
        // Se vacancy_id = 'all', mostrar todas
        if ($vacancyId === 'all') {
            $vacancyId = null;
        }
        // Se vacancy_id = 'current' ou vazio, pegar vaga aberta
        elseif ($vacancyId === 'current' || empty($vacancyId)) {
            $openVacancies = $vacancyModel->openVacancies();
            $vacancyId = !empty($openVacancies) ? (int) $openVacancies[0]['id'] : null;
        } else {
            $vacancyId = (int) $vacancyId;
        }
        
        // Buscar dados da vaga atual (se houver)
        if ($vacancyId) {
            $vacancy = $vacancyModel->find($vacancyId);
        }
        
        // Buscar todas as vagas para o dropdown
        $allVacancies = $vacancyModel->statement('SELECT * FROM exam_vacancies ORDER BY created_at DESC LIMIT 10');
        
        // Buscar júris futuros agrupados (com filtro opcional)
        if ($vacancyId) {
            // Buscar júris filtrados por vaga COM dados da sala E supervisor
            $juries = $juryModel->statement(
                "SELECT j.*, 
                        er.name as room_name, 
                        er.code as room_code,
                        er.capacity as room_capacity, 
                        er.floor as room_floor, 
                        er.building as room_building,
                        COALESCE(el.name, j.location) as location,
                        supervisor.name as supervisor_name,
                        supervisor.phone as supervisor_phone
                 FROM juries j
                 LEFT JOIN exam_rooms er ON er.id = j.room_id
                 LEFT JOIN exam_locations el ON el.id = er.location_id
                 LEFT JOIN users supervisor ON supervisor.id = j.supervisor_id
                 WHERE j.vacancy_id = :vacancy_id 
                 ORDER BY j.exam_date, j.start_time",
                ['vacancy_id' => $vacancyId]
            );
            
            // Agrupar manualmente
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
            unset($jury);
        }
        unset($group);
        
        // Vigilantes disponíveis com carga
        $userModel = new User();
        $availableVigilantes = $userModel->getVigilantesWithWorkload();
        
        // Supervisores elegíveis
        $availableSupervisors = $userModel->statement(
            "SELECT u.*, vw.supervision_count, vw.workload_score 
             FROM users u 
             LEFT JOIN vw_vigilante_workload vw ON vw.user_id = u.id
             WHERE u.supervisor_eligible = 1 
             ORDER BY vw.workload_score ASC, u.name"
        );
        
        // Estatísticas (filtradas por vaga se aplicável)
        if ($vacancyId) {
            $stats = [
                'total_juries' => count($juries ?? []),
                'allocated_vigilantes' => $juryModel->statement(
                    "SELECT COUNT(DISTINCT jv.vigilante_id) as total
                     FROM jury_vigilantes jv
                     INNER JOIN juries j ON j.id = jv.jury_id
                     WHERE j.vacancy_id = :vacancy_id",
                    ['vacancy_id' => $vacancyId]
                )[0]['total'] ?? 0,
                'total_vigilantes_needed' => count($juries ?? []) * 2
            ];
        } else {
            $stats = $allocationService->getAllocationStats();
        }
        
        return $this->view('juries/planning', [
            'groupedJuries' => $groupedJuries,
            'vigilantes' => $availableVigilantes,
            'supervisors' => $availableSupervisors,
            'stats' => $stats,
            'user' => $user,
            'vacancy' => $vacancy,  // Passar vaga filtrada
            'vacancyId' => $vacancyId,  // Passar ID para manter filtro
            'allVacancies' => $allVacancies  // Passar todas as vagas para dropdown
        ]);
    }

    public function show(Request $request): string
    {
        $id = (int) $request->param('id');
        $juryModel = new Jury();
        $jury = $juryModel->find($id);
        if (!$jury) {
            http_response_code(404);
            return $this->view('errors/404');
        }
        if (!empty($jury['supervisor_id'])) {
            $userModel = new User();
            $supervisor = $userModel->find((int) $jury['supervisor_id']);
            $jury['supervisor_name'] = $supervisor['name'] ?? null;
        }
        $juryVigilantes = new JuryVigilante();
        $vigilantes = $juryVigilantes->vigilantesForJury($id);
        $reportModel = new ExamReport();
        $report = $reportModel->findByJury($id);
        return $this->view('juries/show', [
            'jury' => $jury,
            'vigilantes' => $vigilantes,
            'report' => $report,
        ]);
    }
    
    /**
     * API: PLANEJAR alocação automática por Local/Data
     * Endpoint: POST /api/alocacao/plan-local-date
     * 
     * Gera plano de alocação SEM gravar no BD
     * Retorna: JSON com plan, stats, avisos, bloqueios
     */
    public function planLocalDate(Request $request): void
    {
        // Validar autenticação e permissões
        $user = Auth::user();
        if (!in_array($user['role'], ['coordenador', 'membro'], true)) {
            Response::json([
                'ok' => false,
                'erro' => 'Permissão negada'
            ], 403);
            return;
        }
        
        // Obter parâmetros
        $data = $request->json();
        $location = $data['location'] ?? null;
        $date = $data['data'] ?? null;
        
        // Validar parâmetros
        if (!$location || !$date) {
            Response::json([
                'ok' => false,
                'erro' => 'Parâmetros inválidos: location e data são obrigatórios'
            ], 400);
            return;
        }
        
        // Validar formato de data
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            Response::json([
                'ok' => false,
                'erro' => 'Formato de data inválido. Use YYYY-MM-DD'
            ], 400);
            return;
        }
        
        try {
            // Executar planejamento
            $db = $this->getConnection();
            $plannerService = new AllocationPlannerService($db);
            $result = $plannerService->planLocalDate($location, $date);
            
            // Log de atividade
            $logger = new ActivityLogger($db);
            $logger->log(
                $user['id'],
                'allocation_plan_generated',
                'jury',
                null,
                [
                    'location' => $location,
                    'date' => $date,
                    'janelas' => $result['janela_count'] ?? 0,
                    'acoes' => $result['stats']['total_acoes'] ?? 0
                ]
            );
            
            Response::json($result);
            
        } catch (\Exception $e) {
            error_log("Erro ao gerar plano: " . $e->getMessage());
            Response::json([
                'ok' => false,
                'erro' => 'Erro ao gerar plano: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: APLICAR plano de alocação
     * Endpoint: POST /api/alocacao/apply-local-date
     * 
     * Grava plano de alocação no BD (transação)
     * Retorna: JSON com aplicadas e falhas
     */
    public function applyLocalDate(Request $request): void
    {
        // Validar autenticação e permissões
        $user = Auth::user();
        if (!in_array($user['role'], ['coordenador', 'membro'], true)) {
            Response::json([
                'ok' => false,
                'erro' => 'Permissão negada'
            ], 403);
            return;
        }
        
        // Obter parâmetros
        $data = $request->json();
        $location = $data['location'] ?? null;
        $date = $data['data'] ?? null;
        $plan = $data['plan'] ?? null;
        
        // Validar parâmetros
        if (!$location || !$date || !$plan || !is_array($plan)) {
            Response::json([
                'ok' => false,
                'erro' => 'Parâmetros inválidos: location, data e plan são obrigatórios'
            ], 400);
            return;
        }
        
        try {
            // Executar aplicação do plano
            $db = $this->getConnection();
            $plannerService = new AllocationPlannerService($db);
            $result = $plannerService->applyLocalDate($location, $date, $plan);
            
            // Log de atividade
            $logger = new ActivityLogger($db);
            $logger->log(
                $user['id'],
                'allocation_plan_applied',
                'jury',
                null,
                [
                    'location' => $location,
                    'date' => $date,
                    'aplicadas' => $result['aplicadas'] ?? 0,
                    'falhas' => count($result['falhas'] ?? [])
                ]
            );
            
            Response::json($result);
            
        } catch (\Exception $e) {
            error_log("Erro ao aplicar plano: " . $e->getMessage());
            Response::json([
                'ok' => false,
                'erro' => 'Erro ao aplicar plano: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Obter KPIs de alocação por Local/Data
     * Endpoint: GET /api/alocacao/kpis
     * 
     * Retorna: conflitos, desvio de score, ocupação, etc.
     */
    public function getKPIs(Request $request): void
    {
        // Validar autenticação
        $user = Auth::user();
        if (!$user) {
            Response::json([
                'ok' => false,
                'erro' => 'Não autenticado'
            ], 401);
            return;
        }
        
        // Obter parâmetros
        $location = $request->query('location');
        $date = $request->query('data');
        
        // Validar parâmetros
        if (!$location || !$date) {
            Response::json([
                'ok' => false,
                'erro' => 'Parâmetros inválidos: location e data são obrigatórios'
            ], 400);
            return;
        }
        
        try {
            // Obter KPIs
            $db = $this->getConnection();
            $plannerService = new AllocationPlannerService($db);
            $kpis = $plannerService->getKPIs($location, $date);
            
            Response::json([
                'ok' => true,
                'kpis' => $kpis
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao obter KPIs: " . $e->getMessage());
            Response::json([
                'ok' => false,
                'erro' => 'Erro ao obter KPIs: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Página de planejamento por vaga (wizard)
     */
    public function planningByVacancy(): string
    {
        $user = Auth::user();
        $vacancyModel = new \App\Models\ExamVacancy();
        $juryModel = new Jury();
        $allocationService = new \App\Services\SmartAllocationService();
        
        // Buscar vagas abertas
        $openVacancies = $vacancyModel->openVacancies();
        
        // CORREÇÃO #1 & #6: Pré-carregar estatísticas de todas as vagas (evita N+1)
        $vacanciesWithStats = [];
        foreach ($openVacancies as $vacancy) {
            // CORREÇÃO #6: Usar método otimizado que evita múltiplas queries
            $juries = $juryModel->getByVacancyWithStats((int) $vacancy['id']);
            $vacancy['has_juries'] = !empty($juries);
            $vacancy['stats'] = null;
            
            if ($vacancy['has_juries']) {
                $vacancy['stats'] = $allocationService->getVacancyAllocationStats((int) $vacancy['id']);
            }
            
            $vacanciesWithStats[] = $vacancy;
        }
        
        // Buscar dados mestre
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
     * API: Criar júris vinculados a uma vaga
     */
    public function createJuriesForVacancy(Request $request)
    {
        try {
            // Garantir que não há output antes do JSON
            if (ob_get_length()) ob_clean();
            
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
            $roomModel = new \App\Models\ExamRoom();
            $created = [];
            $totalCreated = 0;
            $conflicts = [];
            
            foreach ($disciplines as $discipline) {
                if (empty($discipline['subject']) || empty($discipline['start_time']) || empty($discipline['end_time'])) {
                    continue;
                }
                
                $rooms = $discipline['rooms'] ?? [];
                
                foreach ($rooms as $room) {
                    if (empty($room['room']) || empty($room['candidates_quota'])) {
                        continue;
                    }
                    
                    // Buscar detalhes da sala para criar texto descritivo e obter location_id
                    $roomDetails = null;
                    $roomLocationId = null;
                    if (!empty($room['room_id'])) {
                        $roomDetails = $roomModel->find((int) $room['room_id']);
                        if ($roomDetails) {
                            $roomLocationId = $roomDetails['location_id'];
                        }
                    }
                    
                    // Criar texto descritivo da sala
                    $roomText = $room['room']; // Código (fallback)
                    if ($roomDetails) {
                        // Usar nome da sala se existir, senão usar código
                        $roomText = $roomDetails['name'] ?: $roomDetails['code'];
                        
                        // Adicionar edifício e piso se existirem
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
                    
                    // Verificar conflito de sala
                    $conflictCheck = $juryModel->statement(
                        "SELECT id, subject, start_time, end_time 
                         FROM juries 
                         WHERE location = :location 
                           AND room = :room 
                           AND exam_date = :exam_date
                           AND (
                               (start_time < :end_time AND end_time > :start_time)
                           )",
                        [
                            'location' => $location,
                            'room' => $room['room'],
                            'exam_date' => $examDate,
                            'start_time' => $discipline['start_time'],
                            'end_time' => $discipline['end_time']
                        ]
                    );
                    
                    if (!empty($conflictCheck)) {
                        $conflicts[] = [
                            'room' => $room['room'],
                            'subject' => $discipline['subject'],
                            'time' => $discipline['start_time'] . '-' . $discipline['end_time'],
                            'existing' => $conflictCheck[0]['subject'] . ' (' . $conflictCheck[0]['start_time'] . '-' . $conflictCheck[0]['end_time'] . ')'
                        ];
                        continue; // Pular esta sala
                    }
                    
                    $juryId = $juryModel->create([
                        'vacancy_id' => $vacancyId,
                        'subject' => $discipline['subject'],
                        'exam_date' => $examDate,
                        'start_time' => $discipline['start_time'],
                        'end_time' => $discipline['end_time'],
                        'location' => $location,
                        'location_id' => $roomLocationId,
                        'room' => $roomText, // Texto descritivo com nome, edifício e piso
                        'room_id' => !empty($room['room_id']) ? (int) $room['room_id'] : null,
                        'candidates_quota' => (int) $room['candidates_quota'],
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
                    
                    ActivityLogger::log('juries', $juryId, 'create_for_vacancy', [
                        'vacancy_id' => $vacancyId,
                        'subject' => $discipline['subject'],
                        'room' => $room['room']
                    ]);
                    
                    $totalCreated++;
                }
            }
            
            if ($totalCreated === 0) {
                $conflictMsg = '';
                if (!empty($conflicts)) {
                    $conflictMsg = "\n\n⚠️ Conflitos de sala detectados:\n";
                    foreach ($conflicts as $c) {
                        $conflictMsg .= "- Sala {$c['room']}: {$c['subject']} ({$c['time']}) conflita com {$c['existing']}\n";
                    }
                }
                Response::json([
                    'success' => false,
                    'message' => 'Nenhum júri foi criado' . $conflictMsg,
                    'conflicts' => $conflicts
                ], 400);
                return;
            }
            
            $message = "Criados {$totalCreated} júris com sucesso";
            
            if (!empty($conflicts)) {
                $message .= "\n\n⚠️ Atenção: " . count($conflicts) . " sala(s) foram ignoradas por conflito de horário:";
                foreach ($conflicts as $c) {
                    $message .= "\n- Sala {$c['room']}: {$c['subject']} ({$c['time']}) conflita com {$c['existing']}";
                }
            }
            
            Response::json([
                'success' => true,
                'message' => $message,
                'juries' => $created,
                'total' => $totalCreated,
                'conflicts' => $conflicts,
                'has_conflicts' => !empty($conflicts)
            ]);
            
        } catch (\Exception $e) {
            if (ob_get_length()) ob_clean();
            Response::json([
                'success' => false,
                'message' => 'Erro ao criar júris: ' . $e->getMessage()
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
        }
        
        $allocationService = new \App\Services\SmartAllocationService();
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
        }
        
        $allocationService = new \App\Services\SmartAllocationService();
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
        }
        
        $allocationService = new \App\Services\SmartAllocationService();
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
        
        $allocationService = new \App\Services\SmartAllocationService();
        $vigilantes = $allocationService->getEligibleVigilantesForJury($juryId);
        
        // Debug info
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
        
        $vacancyModel = new \App\Models\ExamVacancy();
        $vacancy = $vacancyModel->find($vacancyId);
        
        if (!$vacancy) {
            Flash::add('error', 'Vaga não encontrada');
            redirect('/juries/planning-by-vacancy');
        }
        
        $juryModel = new Jury();
        $groupedJuries = $juryModel->getGroupedByVacancy($vacancyId);
        
        // Enriquecer com dados de alocação e verificar conflitos de sala
        $juryVigilantes = new JuryVigilante();
        foreach ($groupedJuries as &$locationGroup) {
            foreach ($locationGroup['disciplines'] as &$discipline) {
                foreach ($discipline['juries'] as &$jury) {
                    $jury['vigilantes'] = $juryVigilantes->vigilantesForJury((int) $jury['id']);
                    $jury['vigilantes_count'] = count($jury['vigilantes']);
                    $jury['required_vigilantes'] = $juryModel->calculateRequiredVigilantes((int) $jury['candidates_quota']);
                    
                    // Verificar conflito de sala
                    $conflicts = $juryModel->statement(
                        "SELECT id, subject, start_time, end_time 
                         FROM juries 
                         WHERE id != :id
                           AND location = :location 
                           AND room = :room 
                           AND exam_date = :exam_date
                           AND (
                               (start_time < :end_time AND end_time > :start_time)
                           )",
                        [
                            'id' => $jury['id'],
                            'location' => $jury['location'],
                            'room' => $jury['room'],
                            'exam_date' => $jury['exam_date'],
                            'start_time' => $jury['start_time'],
                            'end_time' => $jury['end_time']
                        ]
                    );
                    
                    $jury['has_room_conflict'] = !empty($conflicts);
                    $jury['room_conflicts'] = $conflicts;
                }
            }
        }
        
        // Estatísticas
        $allocationService = new \App\Services\SmartAllocationService();
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
     * API: Obter candidatos aprovados de uma vaga (para supervisão)
     */
    public function getVacancyApprovedCandidates(Request $request): void
    {
        $vacancyId = (int) $request->param('id');
        
        $allocationService = new \App\Services\SmartAllocationService();
        $candidates = $allocationService->getApprovedCandidates($vacancyId);
        
        Response::json([
            'success' => true,
            'candidates' => $candidates
        ]);
    }
    
    /**
     * Atribuir supervisor em lote
     */
    public function bulkAssignSupervisor(Request $request): void
    {
        try {
            if (ob_get_length()) ob_clean();
            
            $juryIds = $request->input('jury_ids');
            $supervisorId = $request->input('supervisor_id');
            
            if (empty($juryIds) || !is_array($juryIds)) {
                Response::json([
                    'success' => false,
                    'message' => 'Selecione pelo menos um júri'
                ], 400);
                return;
            }
            
            // supervisor_id = 0 significa "Comissão de Exames"
            // supervisor_id > 0 significa supervisor individual
            // supervisor_id = null/empty não é permitido
            
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
            if (ob_get_length()) ob_clean();
            Response::json([
                'success' => false,
                'message' => 'Erro ao atribuir supervisor: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Buscar detalhes de um júri (para edição)
     */
    public function getDetails(Request $request)
    {
        try {
            $juryId = (int) $request->param('id');
            
            $juryModel = new Jury();
            $jury = $juryModel->find($juryId);
            
            if (!$jury) {
                Response::json([
                    'success' => false,
                    'message' => 'Júri não encontrado'
                ], 404);
                return;
            }
            
            Response::json([
                'success' => true,
                'jury' => $jury
            ]);
            
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => 'Erro ao buscar júri: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Atualizar júri
     */
    public function updateJury(Request $request)
    {
        try {
            // Validar CSRF
            if (!Csrf::validate($request)) {
                Response::json([
                    'success' => false,
                    'message' => 'Token CSRF inválido'
                ], 403);
                return;
            }
            
            $juryId = (int) $request->param('id');
            $locationId = (int) $request->input('location_id');
            $roomId = (int) $request->input('room_id');
            
            // Buscar informações do local e sala
            $locationModel = new \App\Models\ExamLocation();
            $roomModel = new \App\Models\ExamRoom();
            
            $location = $locationModel->find($locationId);
            if (!$location) {
                Response::json(['success' => false, 'message' => 'Local não encontrado'], 404);
                return;
            }
            
            $room = $roomModel->find($roomId);
            if (!$room) {
                Response::json(['success' => false, 'message' => 'Sala não encontrada'], 404);
                return;
            }
            
            // Criar texto descritivo da sala
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
            
            // Dados a atualizar
            $data = [
                'subject' => $request->input('subject'),
                'exam_date' => $request->input('exam_date'),
                'start_time' => $request->input('start_time'),
                'end_time' => $request->input('end_time'),
                'location_id' => $locationId,
                'location' => $location['name'],
                'room_id' => $roomId,
                'room' => $roomText, // Texto descritivo com nome, edifício e piso
                'candidates_quota' => (int) $request->input('candidates_quota'),
                'notes' => $request->input('notes'),
                'updated_at' => now()
            ];
            
            // Validações
            if (empty($data['subject'])) {
                Response::json(['success' => false, 'message' => 'Disciplina é obrigatória'], 400);
                return;
            }
            
            if ($data['candidates_quota'] < 1 || $data['candidates_quota'] > 300) {
                Response::json(['success' => false, 'message' => 'Número de candidatos inválido (1-300)'], 400);
                return;
            }
            
            // Validar horários
            if ($data['end_time'] <= $data['start_time']) {
                Response::json(['success' => false, 'message' => 'Horário de término deve ser maior que o de início'], 400);
                return;
            }
            
            $juryModel = new Jury();
            $jury = $juryModel->find($juryId);
            
            if (!$jury) {
                Response::json(['success' => false, 'message' => 'Júri não encontrado'], 404);
                return;
            }
            
            // Verificar conflitos de sala (se mudou data/horário/sala)
            $hasConflict = false;
            $conflictMessage = '';
            
            if ($data['room_id'] != $jury['room_id'] || 
                $data['exam_date'] != $jury['exam_date'] ||
                $data['start_time'] != $jury['start_time'] ||
                $data['end_time'] != $jury['end_time']) {
                
                // Verificar se a sala está disponível
                $isAvailable = $roomModel->isAvailable(
                    $roomId,
                    $data['exam_date'],
                    $data['start_time'],
                    $data['end_time'],
                    $juryId  // Excluir o júri atual da verificação
                );
                
                if (!$isAvailable) {
                    $conflicts = $juryModel->statement(
                        "SELECT id, subject FROM juries 
                         WHERE id != :jury_id 
                         AND room_id = :room_id 
                         AND exam_date = :date
                         AND (start_time < :end_time AND end_time > :start_time)",
                        [
                            'jury_id' => $juryId,
                            'room_id' => $roomId,
                            'date' => $data['exam_date'],
                            'start_time' => $data['start_time'],
                            'end_time' => $data['end_time']
                        ]
                    );
                    
                    if (!empty($conflicts)) {
                        $hasConflict = true;
                        $conflictList = array_map(fn($c) => $c['subject'], $conflicts);
                        $conflictMessage = ' ⚠️ AVISO: Sala já está alocada para: ' . implode(', ', $conflictList);
                    }
                }
            }
            
            // Atualizar
            $result = $juryModel->update($juryId, $data);
            
            if ($result) {
                // Log de auditoria (com tratamento de erro)
                try {
                    ActivityLogger::log('juries', $juryId, 'update', [
                        'updated_by' => Auth::id(),
                        'changed_fields' => array_keys($data),
                        'old_values' => $jury,
                        'new_values' => $data
                    ]);
                } catch (\Exception $logError) {
                    error_log("AVISO: Erro ao gravar log: " . $logError->getMessage());
                }
                
                // Invalidar cache
                $this->invalidateCache();
                
                Response::json([
                    'success' => true,
                    'message' => 'Júri atualizado com sucesso!' . $conflictMessage,
                    'has_conflict' => $hasConflict
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'Erro ao atualizar júri'
                ], 500);
            }
            
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => 'Erro ao atualizar júri: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Eliminar júri
     */
    public function deleteJury(Request $request)
    {
        // Desabilitar exibição de erros
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        
        error_log("========== API deleteJury CHAMADA ==========");
        
        try {
            // Validar CSRF
            if (!Csrf::validate($request)) {
                error_log("ERRO: CSRF inválido");
                Response::json([
                    'success' => false,
                    'message' => 'Token CSRF inválido'
                ], 403);
                return;
            }
            
            $juryId = (int) $request->param('id');
            error_log("DEBUG: Tentando eliminar júri ID: $juryId");
            
            $juryModel = new Jury();
            $jury = $juryModel->find($juryId);
            
            if (!$jury) {
                error_log("ERRO: Júri $juryId não encontrado");
                Response::json(['success' => false, 'message' => 'Júri não encontrado'], 404);
                return;
            }
            
            // Guardar dados para log antes de eliminar
            $juryData = [
                'id' => $jury['id'],
                'subject' => $jury['subject'] ?? 'N/A',
                'room' => $jury['room'] ?? 'N/A',
                'exam_date' => $jury['exam_date'] ?? 'N/A',
                'vacancy_id' => $jury['vacancy_id'] ?? null
            ];
            
            // Eliminar alocações de vigilantes primeiro
            try {
                $juryVigilantes = new JuryVigilante();
                $juryVigilantes->statement(
                    "DELETE FROM jury_vigilantes WHERE jury_id = :jury",
                    ['jury' => $juryId]
                );
                error_log("DEBUG: Vigilantes do júri $juryId removidos");
            } catch (\Exception $e) {
                error_log("AVISO: Erro ao remover vigilantes: " . $e->getMessage());
                // Continuar mesmo assim
            }
            
            // Eliminar júri
            $result = $juryModel->delete($juryId);
            
            if ($result) {
                error_log("DEBUG: Júri $juryId eliminado com sucesso");
                
                // Log de auditoria (com tratamento de erro)
                try {
                    ActivityLogger::log('juries', $juryId, 'delete', [
                        'deleted_by' => Auth::id(),
                        'jury_data' => $juryData,
                        'timestamp' => now()
                    ]);
                } catch (\Exception $logError) {
                    error_log("AVISO: Erro ao gravar log: " . $logError->getMessage());
                }
                
                // Invalidar cache
                $this->invalidateCache();
                
                Response::json([
                    'success' => true,
                    'message' => 'Júri eliminado com sucesso!'
                ]);
            } else {
                error_log("ERRO: Falha ao eliminar júri $juryId");
                Response::json([
                    'success' => false,
                    'message' => 'Erro ao eliminar júri'
                ], 500);
            }
            
        } catch (\Exception $e) {
            error_log("ERRO em deleteJury: " . $e->getMessage());
            error_log("TRACE: " . $e->getTraceAsString());
            
            Response::json([
                'success' => false,
                'message' => 'Erro ao eliminar júri: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    
    /**
     * API: Obter dados mestre (locais e salas)
     * GET /api/master-data/locations-rooms
     */
    public function getMasterDataLocationsRooms(): void
    {
        try {
            $locationModel = new \App\Models\ExamLocation();
            $roomModel = new \App\Models\ExamRoom();
            
            // Buscar locais ativos
            $locations = $locationModel->getActive();
            
            // Buscar salas ativas com informações do local
            $rooms = $roomModel->getAllWithLocation(true);
            
            Response::json([
                'success' => true,
                'locations' => $locations ?? [],
                'rooms' => $rooms ?? []
            ]);
            
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => 'Erro ao carregar dados: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Obter disciplinas usadas em uma vaga
     * GET /api/vacancies/{vacancy_id}/subjects
     */
    public function getVacancySubjects(int $vacancyId): void
    {
        try {
            $juryModel = new Jury();
            
            $subjects = $juryModel->statement(
                "SELECT DISTINCT subject FROM juries WHERE vacancy_id = :vacancy_id ORDER BY subject ASC",
                ['vacancy_id' => $vacancyId]
            );
            
            $subjectList = array_map(fn($row) => $row['subject'], $subjects);
            
            Response::json([
                'success' => true,
                'subjects' => $subjectList ?? []
            ]);
            
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => 'Erro ao carregar disciplinas: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Criar múltiplos júris de uma vez (criação em lote)
     * POST /juries/create-bulk
     */
    public function createBulk(Request $request): void
    {
        try {
            // Obter dados
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
            $roomModel = new \App\Models\ExamRoom();
            $locationModel = new \App\Models\ExamLocation();
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
                    error_log("DEBUG: Sala inválida - room_id: $roomId, candidates: $candidates");
                    continue; // Pular salas inválidas
                }
                
                // Buscar informações da sala
                $room = $roomModel->find($roomId);
                if (!$room) {
                    error_log("DEBUG: Sala ID $roomId não encontrada");
                    continue;
                }
                
                // Criar texto descritivo da sala
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
                    'room' => $roomText, // Texto descritivo com nome, edifício e piso
                    'candidates_quota' => $candidates,
                    'vigilantes_capacity' => 2, // Padrão
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
     * Atualiza room, room_id, location, location_id com os dados atuais
     */
    public function syncRoomNames(Request $request)
    {
        try {
            $juryModel = new Jury();
            $roomModel = new \App\Models\ExamRoom();
            $locationModel = new \App\Models\ExamLocation();
            
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
            // Validar CSRF
            if (!Csrf::validate($request)) {
                Response::json([
                    'success' => false,
                    'message' => 'Token CSRF inválido'
                ], 403);
                return;
            }
            
            $vacancyId = (int) $request->param('vacancy_id');
            $subject = $request->input('subject');
            $examDate = $request->input('exam_date');
            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');
            $rooms = $request->input('rooms', []);
            
            // Validações
            if (empty($subject) || empty($examDate) || empty($startTime) || empty($endTime)) {
                Response::json(['success' => false, 'message' => 'Todos os campos são obrigatórios'], 400);
                return;
            }
            
            if (empty($rooms)) {
                Response::json(['success' => false, 'message' => 'Adicione pelo menos uma sala'], 400);
                return;
            }
            
            if ($endTime <= $startTime) {
                Response::json(['success' => false, 'message' => 'Horário de término deve ser maior que o de início'], 400);
                return;
            }
            
            $juryModel = new Jury();
            $db = Connection::getInstance();
            
            // Iniciar transação
            $db->beginTransaction();
            
            try {
                $updatedJuries = [];
                $newJuries = [];
                $removedJuries = [];
                
                // Buscar júris existentes desta disciplina/horário/vaga
                $existingJuries = $juryModel->statement(
                    "SELECT * FROM juries 
                     WHERE vacancy_id = :vacancy_id 
                     AND subject = :subject
                     ORDER BY id",
                    [
                        'vacancy_id' => $vacancyId,
                        'subject' => $subject
                    ]
                );
                
                $existingJuryIds = array_column($existingJuries, 'id');
                $processedJuryIds = [];
                
                // Processar salas
                foreach ($rooms as $roomData) {
                    $roomName = $roomData['room'];
                    $candidatesQuota = (int) $roomData['candidates_quota'];
                    
                    if ($candidatesQuota < 1 || $candidatesQuota > 300) {
                        throw new \Exception("Número de candidatos inválido para sala {$roomName}");
                    }
                    
                    $juryData = [
                        'subject' => $subject,
                        'exam_date' => $examDate,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'room' => $roomName,
                        'candidates_quota' => $candidatesQuota,
                        'updated_at' => now()
                    ];
                    
                    // Se tem jury_id, é uma atualização
                    if (isset($roomData['jury_id']) && $roomData['jury_id'] > 0) {
                        $juryId = (int) $roomData['jury_id'];
                        $juryModel->update($juryId, $juryData);
                        $updatedJuries[] = $juryId;
                        $processedJuryIds[] = $juryId;
                    } else {
                        // Nova sala - criar júri
                        $juryData['vacancy_id'] = $vacancyId;
                        $juryData['created_at'] = now();
                        
                        // Buscar primeiro júri existente para pegar local/location
                        if (!empty($existingJuries)) {
                            $firstJury = $existingJuries[0];
                            $juryData['location'] = $firstJury['location'];
                            $juryData['location_id'] = $firstJury['location_id'];
                            $juryData['room_id'] = $firstJury['room_id'];
                        }
                        
                        $newJuryId = $juryModel->create($juryData);
                        $newJuries[] = $newJuryId;
                        $processedJuryIds[] = $newJuryId;
                    }
                }
                
                // Identificar júris para remover (existiam mas não vieram no request)
                $juriesToRemove = array_diff($existingJuryIds, $processedJuryIds);
                
                // Remover júris não mais necessários
                if (!empty($juriesToRemove)) {
                    foreach ($juriesToRemove as $juryId) {
                        // Remover alocações de vigilantes primeiro
                        $db->prepare("DELETE FROM jury_vigilantes WHERE jury_id = ?")
                           ->execute([$juryId]);
                        
                        // Remover o júri
                        $juryModel->delete($juryId);
                        $removedJuries[] = $juryId;
                    }
                }
                
                // Commit
                $db->commit();
                
                // Log de atividade
                ActivityLogger::log('juries', 0, 'update_discipline', [
                    'vacancy_id' => $vacancyId,
                    'subject' => $subject,
                    'updated' => count($updatedJuries),
                    'created' => count($newJuries),
                    'removed' => count($removedJuries)
                ]);
                
                $message = "Disciplina atualizada com sucesso!";
                if (count($newJuries) > 0) {
                    $message .= " " . count($newJuries) . " sala(s) adicionada(s).";
                }
                if (count($removedJuries) > 0) {
                    $message .= " " . count($removedJuries) . " sala(s) removida(s).";
                }
                
                Response::json([
                    'success' => true,
                    'message' => $message,
                    'updated' => count($updatedJuries),
                    'created' => count($newJuries),
                    'removed' => count($removedJuries)
                ]);
                
            } catch (\Exception $e) {
                $db->rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => 'Erro ao atualizar disciplina: ' . $e->getMessage()
            ], 500);
        }
    }
}
