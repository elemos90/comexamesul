<?php

namespace App\Controllers;

use App\Http\Request;
use App\Models\ExamVacancy;
use App\Models\User;
use App\Models\VacancyApplication;
use App\Models\Jury;
use App\Services\ActivityLogger;
use App\Utils\Auth;
use App\Utils\Flash;
use App\Utils\Validator;

class VacancyController extends Controller
{
    public function index(): string
    {
        $user = Auth::user();
        $model = new ExamVacancy();
        
        // Fechar vagas expiradas automaticamente
        $model->closeExpired();
        
        $vacancies = $user['role'] === 'vigilante'
            ? $model->openVacancies()
            : $model->statement('SELECT * FROM exam_vacancies ORDER BY created_at DESC');

        // Verificar se há vaga aberta para desabilitar botão de criar nova
        $openVacancy = $model->openVacancies();
        $hasOpenVacancy = !empty($openVacancy);

        return $this->view('vacancies/index', [
            'vacancies' => $vacancies,
            'user' => $user,
            'hasOpenVacancy' => $hasOpenVacancy,
            'openVacancy' => $hasOpenVacancy ? $openVacancy[0] : null,
        ]);
    }

    public function show(Request $request): string
    {
        $id = (int) $request->param('id');
        $model = new ExamVacancy();
        $vacancy = $model->find($id);
        if (!$vacancy) {
            http_response_code(404);
            return $this->view('errors/404');
        }

        $user = Auth::user();
        $availableCount = null;
        if (in_array($user['role'], ['coordenador', 'membro'], true)) {
            $availableCount = count((new User())->availableVigilantes());
        }

        return $this->view('vacancies/show', [
            'vacancy' => $vacancy,
            'user' => $user,
            'availableCount' => $availableCount,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->only(['title', 'description', 'deadline_at']);
        
        // Armazenar valores preenchidos para mostrar em caso de erro
        $_SESSION['old'] = $data;
        
        // VALIDAÇÃO CRÍTICA: Verificar se já existe vaga aberta
        $model = new ExamVacancy();
        $openVacancies = $model->openVacancies();
        
        if (!empty($openVacancies)) {
            $existingVacancy = $openVacancies[0];
            $deadline = date('d/m/Y H:i', strtotime($existingVacancy['deadline_at']));
            
            Flash::add('error', 
                'Não é possível criar uma nova vaga enquanto houver outra aberta. ' .
                'A vaga "' . htmlspecialchars($existingVacancy['title']) . '" ' .
                '(prazo: ' . $deadline . ') está atualmente aberta. ' .
                'Feche ou encerre esta vaga antes de criar uma nova.'
            );
            redirect('/vacancies');
        }
        
        $validator = new Validator();
        $rules = [
            'title' => 'required|min:3|max:180',
            'description' => 'required|min:10',
            'deadline_at' => 'required|date',
        ];
        
        if (!$validator->validate($data, $rules)) {
            $errors = $validator->errors();
            $_SESSION['errors'] = $errors;
            
            // Criar mensagem mais descritiva
            $errorMessages = [];
            foreach ($errors as $field => $messages) {
                $errorMessages = array_merge($errorMessages, $messages);
            }
            Flash::add('error', 'Verifique os dados da vaga: ' . implode(' ', $errorMessages));
            redirect('/vacancies');
        }

        $deadline = $this->normalizeDateTime($data['deadline_at']);
        if (!$deadline) {
            Flash::add('error', 'Informe uma data limite valida.');
            $_SESSION['errors'] = ['deadline_at' => ['Formato de data invalido.']];
            redirect('/vacancies');
        }

        $payload = [
            'title' => trim($data['title']),
            'description' => trim($data['description']),
            'deadline_at' => $deadline,
            'status' => 'aberta',
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $model->create($payload);
        ActivityLogger::log('vacancies', null, 'create', $payload);
        Flash::add('success', 'Vaga criada.');
        redirect('/vacancies');
    }

    public function update(Request $request)
    {
        $id = (int) $request->param('id');
        $data = $request->only(['title', 'description', 'deadline_at', 'status']);

        $validator = new Validator();
        $rules = [
            'title' => 'required|min:3|max:180',
            'description' => 'required|min:10',
            'deadline_at' => 'required|date',
            'status' => 'required|in:aberta,fechada,encerrada',
        ];
        if (!$validator->validate($data, $rules)) {
            Flash::add('error', 'Verifique os dados da vaga.');
            $_SESSION['errors'] = $validator->errors();
            redirect('/vacancies');
        }

        $model = new ExamVacancy();
        $vacancy = $model->find($id);
        if (!$vacancy) {
            Flash::add('error', 'Vaga nao encontrada.');
            redirect('/vacancies');
        }

        // Bloquear edicao de vagas encerradas
        if ($vacancy['status'] === 'encerrada') {
            Flash::add('error', 'Vagas encerradas nao podem ser editadas. Esta vaga esta arquivada permanentemente.');
            redirect('/vacancies');
        }

        // Impedir mudanca para estado encerrado via edicao (deve usar botao Encerrar)
        if ($data['status'] === 'encerrada') {
            Flash::add('error', 'Para encerrar uma vaga, use o botao "Encerrar" na listagem.');
            redirect('/vacancies');
        }
        
        // VALIDAÇÃO CRÍTICA: Ao reabrir vaga, verificar se já existe outra aberta
        if ($vacancy['status'] !== 'aberta' && $data['status'] === 'aberta') {
            $openVacancies = $model->openVacancies();
            
            if (!empty($openVacancies)) {
                $existingVacancy = $openVacancies[0];
                $deadline = date('d/m/Y H:i', strtotime($existingVacancy['deadline_at']));
                
                Flash::add('error', 
                    'Não é possível reabrir esta vaga enquanto houver outra aberta. ' .
                    'A vaga "' . htmlspecialchars($existingVacancy['title']) . '" ' .
                    '(prazo: ' . $deadline . ') está atualmente aberta. ' .
                    'Feche essa vaga primeiro antes de reabrir outra.'
                );
                redirect('/vacancies');
            }
        }

        $deadline = $this->normalizeDateTime($data['deadline_at']);
        if (!$deadline) {
            Flash::add('warning', 'Nao foi possivel interpretar a nova data limite. Mantivemos o valor anterior.');
            $deadline = $vacancy['deadline_at'];
        }

        $payload = [
            'title' => trim($data['title']),
            'description' => trim($data['description']),
            'deadline_at' => $deadline,
            'status' => $data['status'],
            'updated_at' => now(),
        ];

        $model->update($id, $payload);
        ActivityLogger::log('vacancies', $id, 'update');
        Flash::add('success', 'Vaga atualizada.');
        redirect('/vacancies');
    }

    public function close(Request $request)
    {
        $id = (int) $request->param('id');
        $model = new ExamVacancy();
        $vacancy = $model->find($id);
        if (!$vacancy) {
            Flash::add('error', 'Vaga nao encontrada.');
            redirect('/vacancies');
        }
        $model->update($id, ['status' => 'fechada', 'updated_at' => now()]);
        ActivityLogger::log('vacancies', $id, 'close');
        Flash::add('success', 'Vaga fechada.');
        redirect('/vacancies');
    }

    public function finalize(Request $request)
    {
        $id = (int) $request->param('id');
        $model = new ExamVacancy();
        $vacancy = $model->find($id);
        
        if (!$vacancy) {
            Flash::add('error', 'Vaga nao encontrada.');
            redirect('/vacancies');
        }
        
        if ($vacancy['status'] !== 'fechada') {
            Flash::add('error', 'Apenas vagas fechadas podem ser encerradas.');
            redirect('/vacancies');
        }
        
        $model->update($id, [
            'status' => 'encerrada',
            'updated_at' => now()
        ]);
        
        ActivityLogger::log('vacancies', $id, 'finalize', [
            'title' => $vacancy['title'],
            'previous_status' => 'fechada'
        ]);
        
        Flash::add('success', 'Vaga encerrada e arquivada com sucesso.');
        redirect('/vacancies');
    }

    public function delete(Request $request)
    {
        $id = (int) $request->param('id');
        $model = new ExamVacancy();
        $vacancy = $model->find($id);
        
        if (!$vacancy) {
            Flash::add('error', 'Vaga nao encontrada.');
            redirect('/vacancies');
        }

        // Verificar se a vaga tem júris associados
        $juryModel = new Jury();
        $juries = $juryModel->statement(
            "SELECT COUNT(*) as count FROM juries WHERE vacancy_id = :vacancy_id",
            ['vacancy_id' => $id]
        );
        
        if (!empty($juries) && $juries[0]['count'] > 0) {
            Flash::add('error', 'Nao e possivel remover esta vaga pois existem ' . $juries[0]['count'] . ' juri(s) associado(s). Remova os juris primeiro.');
            redirect('/vacancies');
        }

        // Verificar se a vaga tem candidaturas aprovadas
        $applicationModel = new VacancyApplication();
        $approvedApps = $applicationModel->statement(
            "SELECT COUNT(*) as count FROM vacancy_applications WHERE vacancy_id = :vacancy_id AND status = 'aprovada'",
            ['vacancy_id' => $id]
        );
        
        if (!empty($approvedApps) && $approvedApps[0]['count'] > 0) {
            Flash::add('error', 'Nao e possivel remover esta vaga pois existem ' . $approvedApps[0]['count'] . ' candidatura(s) aprovada(s). Esta vaga possui historico importante.');
            redirect('/vacancies');
        }

        // Verificar se há qualquer histórico de candidaturas (mesmo rejeitadas/canceladas)
        $allApps = $applicationModel->statement(
            "SELECT COUNT(*) as count FROM vacancy_applications WHERE vacancy_id = :vacancy_id",
            ['vacancy_id' => $id]
        );
        
        if (!empty($allApps) && $allApps[0]['count'] > 0) {
            Flash::add('warning', 'Esta vaga possui ' . $allApps[0]['count'] . ' candidatura(s) registrada(s). Recomenda-se apenas fechar a vaga ao inves de remove-la para preservar o historico.');
            // Permitir exclusão mas com aviso
        }

        // Log antes de deletar para manter registro
        ActivityLogger::log('vacancies', $id, 'delete', [
            'title' => $vacancy['title'],
            'status' => $vacancy['status'],
        ]);

        $model->delete($id);
        Flash::add('success', 'Vaga removida com sucesso.');
        redirect('/vacancies');
    }

    private function normalizeDateTime(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    }
}