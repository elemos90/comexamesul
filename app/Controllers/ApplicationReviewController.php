<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Models\VacancyApplication;
use App\Models\ExamVacancy;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\EmailNotificationService;
use App\Utils\Auth;
use App\Utils\Flash;
use App\Utils\Csrf;

class ApplicationReviewController extends Controller
{
    public function index(): string
    {
        $user = Auth::user();
        if (!$user || !in_array($user['role'], ['coordenador', 'membro'])) {
            Flash::add('error', 'Acesso restrito a coordenadores e membros.');
            redirect('/dashboard');
        }

        // Buscar todas as vagas
        $vacancyModel = new ExamVacancy();
        $vacancies = $vacancyModel->all();

        // Se houver vaga selecionada
        $selectedVacancyId = isset($_GET['vacancy']) ? (int) $_GET['vacancy'] : null;
        $selectedVacancy = null;
        $applications = [];
        $statistics = [];

        if ($selectedVacancyId) {
            $selectedVacancy = $vacancyModel->find($selectedVacancyId);
            
            if ($selectedVacancy) {
                // Buscar candidaturas da vaga
                $applicationModel = new VacancyApplication();
                $applications = $applicationModel->getByVacancy($selectedVacancyId);
                
                // Estatísticas
                $statistics = $applicationModel->countByStatus($selectedVacancyId);
            }
        }

        return $this->view('applications/index', [
            'user' => $user,
            'vacancies' => $vacancies,
            'selectedVacancy' => $selectedVacancy,
            'applications' => $applications,
            'statistics' => $statistics,
        ]);
    }

    public function approve(Request $request)
    {
        try {
            // Suporte AJAX
            $isAjax = $request->isAjax();
            if ($isAjax && ob_get_length()) ob_clean();
            
            $user = Auth::user();
            if (!$user || !in_array($user['role'], ['coordenador', 'membro'])) {
                if ($isAjax) {
                    Response::json(['success' => false, 'message' => 'Acesso restrito'], 403);
                    return;
                }
                Flash::add('error', 'Acesso restrito.');
                redirect('/dashboard');
            }

            // Validar CSRF
            if (!Csrf::validate($request)) {
                if ($isAjax) {
                    Response::json(['success' => false, 'message' => 'Token CSRF inválido'], 403);
                    return;
                }
                Flash::add('error', 'Token CSRF inválido');
                redirect('/applications');
            }

            $applicationId = (int) $request->param('id');
            
            $applicationModel = new VacancyApplication();
            $application = $applicationModel->find($applicationId);

            if (!$application) {
                if ($isAjax) {
                    Response::json(['success' => false, 'message' => 'Candidatura não encontrada'], 404);
                    return;
                }
                Flash::add('error', 'Candidatura não encontrada.');
                redirect('/applications');
            }

            // Verificar status (permitir aprovação de pendentes e rejeitadas)
            if (!in_array($application['status'], ['pendente', 'rejeitada'])) {
                if ($isAjax) {
                    Response::json(['success' => false, 'message' => 'Esta candidatura já foi aprovada'], 400);
                    return;
                }
                Flash::add('error', 'Esta candidatura já foi aprovada.');
                redirect('/applications?vacancy=' . $application['vacancy_id']);
            }

            $applicationModel->approve($applicationId, (int) $user['id']);

            ActivityLogger::log('vacancy_applications', $applicationId, 'approve', [
                'vacancy_id' => $application['vacancy_id'],
                'vigilante_id' => $application['vigilante_id'],
            ]);

            // Notificar vigilante sobre aprovação
            $emailService = new EmailNotificationService();
            $emailService->notifyApplicationApproved($applicationId);

            if ($isAjax) {
                if (ob_get_length()) ob_clean();
                Response::json([
                    'success' => true,
                    'message' => 'Candidatura aprovada com sucesso!',
                    'application_id' => $applicationId
                ]);
                exit();
            }

            Flash::add('success', 'Candidatura aprovada com sucesso.');
            redirect('/applications?vacancy=' . $application['vacancy_id']);
            
        } catch (\Exception $e) {
            if ($request->isAjax()) {
                if (ob_get_length()) ob_clean();
                Response::json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
                exit();
            }
            Flash::add('error', 'Erro ao aprovar candidatura');
            redirect('/applications');
        }
    }

    public function reject(Request $request)
    {
        try {
            // Suporte AJAX
            $isAjax = $request->isAjax();
            if ($isAjax && ob_get_length()) ob_clean();
            
            $user = Auth::user();
            if (!$user || !in_array($user['role'], ['coordenador', 'membro'])) {
                if ($isAjax) {
                    Response::json(['success' => false, 'message' => 'Acesso restrito'], 403);
                    return;
                }
                Flash::add('error', 'Acesso restrito.');
                redirect('/dashboard');
            }

            // Validar CSRF
            if (!Csrf::validate($request)) {
                if ($isAjax) {
                    Response::json(['success' => false, 'message' => 'Token CSRF inválido'], 403);
                    return;
                }
                Flash::add('error', 'Token CSRF inválido');
                redirect('/applications');
            }

            $applicationId = (int) $request->param('id');
            
            $applicationModel = new VacancyApplication();
            $application = $applicationModel->find($applicationId);

            if (!$application) {
                if ($isAjax) {
                    Response::json(['success' => false, 'message' => 'Candidatura não encontrada'], 404);
                    return;
                }
                Flash::add('error', 'Candidatura não encontrada.');
                redirect('/applications');
            }

            // Candidaturas aprovadas ou pendentes podem ser rejeitadas
            if ($application['status'] === 'rejeitada') {
                if ($isAjax) {
                    Response::json(['success' => false, 'message' => 'Esta candidatura já está rejeitada'], 400);
                    return;
                }
                Flash::add('warning', 'Esta candidatura já está rejeitada.');
                redirect('/applications?vacancy=' . $application['vacancy_id']);
            }

            // ⚠️ VALIDAÇÃO OBRIGATÓRIA: Motivo de rejeição
            $rejectionReason = trim($request->input('rejection_reason'));
            
            if (empty($rejectionReason)) {
                if ($isAjax) {
                    Response::json(['success' => false, 'message' => 'Motivo de rejeição é obrigatório'], 400);
                    return;
                }
                Flash::add('error', 'Motivo de rejeição é obrigatório');
                redirect('/applications?vacancy=' . $application['vacancy_id']);
            }

            // Rejeitar a candidatura
            $success = $applicationModel->reject($applicationId, $user['id'], $rejectionReason);

            if (!$success) {
                throw new \Exception('Erro ao rejeitar candidatura');
            }

            // Registrar atividade
            ActivityLogger::log('vacancy_applications', $applicationId, 'reject', [
                'vacancy_id' => $application['vacancy_id'],
                'vigilante_id' => $application['vigilante_id'],
                'rejection_reason' => $rejectionReason,
            ]);

            // Notificar vigilante sobre rejeição
            try {
                $emailService = new EmailNotificationService();
                $emailService->notifyApplicationRejected($applicationId, $rejectionReason);
            } catch (\Exception $emailError) {
                // Log do erro mas não bloqueia a operação
                error_log("Erro ao enviar email de rejeição: " . $emailError->getMessage());
            }

            if ($isAjax) {
                if (ob_get_length()) ob_clean();
                Response::json([
                    'success' => true,
                    'message' => 'Candidatura rejeitada com sucesso.',
                    'application_id' => $applicationId
                ]);
                exit(); // Garantir que pare aqui
            }

            Flash::add('success', 'Candidatura rejeitada.');
            redirect('/applications?vacancy=' . $application['vacancy_id']);
            
        } catch (\Exception $e) {
            if ($request->isAjax()) {
                if (ob_get_length()) ob_clean();
                Response::json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
                exit();
            }
            Flash::add('error', 'Erro ao rejeitar candidatura');
            redirect('/applications');
        }
    }

    public function revert(Request $request)
    {
        try {
            // Suporte AJAX
            $isAjax = $request->isAjax();
            if ($isAjax && ob_get_length()) ob_clean();
            
            $user = Auth::user();
            if (!$user || !in_array($user['role'], ['coordenador', 'membro'])) {
                if ($isAjax) {
                    Response::json(['success' => false, 'message' => 'Acesso restrito'], 403);
                    return;
                }
                Flash::add('error', 'Acesso restrito.');
                redirect('/dashboard');
            }

            // Validar CSRF
            if (!Csrf::validate($request)) {
                if ($isAjax) {
                    Response::json(['success' => false, 'message' => 'Token CSRF inválido'], 403);
                    return;
                }
                Flash::add('error', 'Token CSRF inválido');
                redirect('/applications');
            }

            $applicationId = (int) $request->param('id');
            
            $applicationModel = new VacancyApplication();
            $application = $applicationModel->find($applicationId);

            if (!$application) {
                if ($isAjax) {
                    Response::json(['success' => false, 'message' => 'Candidatura não encontrada'], 404);
                    return;
                }
                Flash::add('error', 'Candidatura não encontrada.');
                redirect('/applications');
            }

            // Verificar se a candidatura está aprovada ou rejeitada
            if ($application['status'] === 'pendente') {
                if ($isAjax) {
                    Response::json(['success' => false, 'message' => 'A candidatura já está pendente'], 400);
                    return;
                }
                Flash::add('warning', 'A candidatura já está pendente.');
                redirect('/applications?vacancy=' . $application['vacancy_id']);
            }

            // Reverter para pendente
            $success = $applicationModel->update($applicationId, [
                'status' => 'pendente',
                'reviewed_by' => null,
                'reviewed_at' => null,
                'rejection_reason' => null
            ]);

            if (!$success) {
                throw new \Exception('Erro ao reverter candidatura');
            }

            // Registrar atividade
            ActivityLogger::log('vacancy_applications', $applicationId, 'revert', [
                'previous_status' => $application['status'],
                'reverted_by' => $user['id'],
            ]);

            if ($isAjax) {
                if (ob_get_length()) ob_clean();
                Response::json([
                    'success' => true,
                    'message' => 'Candidatura revertida para pendente com sucesso'
                ]);
                exit();
            }

            Flash::add('success', 'Candidatura revertida para pendente.');
            redirect('/applications?vacancy=' . $application['vacancy_id']);
            
        } catch (\Exception $e) {
            if ($request->isAjax()) {
                if (ob_get_length()) ob_clean();
                Response::json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
                exit();
            }
            Flash::add('error', 'Erro ao reverter candidatura');
            redirect('/applications');
        }
    }

    public function toggleSupervisorEligible(Request $request)
    {
        try {
            // Suporte AJAX
            $isAjax = $request->isAjax();
            if ($isAjax && ob_get_length()) ob_clean();
            
            $user = Auth::user();
            if (!$user || $user['role'] !== 'coordenador') {
                if ($isAjax) {
                    Response::json(['success' => false, 'message' => 'Apenas coordenadores podem definir elegibilidade'], 403);
                    return;
                }
                Flash::add('error', 'Acesso restrito a coordenadores.');
                redirect('/dashboard');
            }

            // Validar CSRF
            if (!Csrf::validate($request)) {
                if ($isAjax) {
                    Response::json(['success' => false, 'message' => 'Token CSRF inválido'], 403);
                    return;
                }
                Flash::add('error', 'Token CSRF inválido');
                redirect('/applications');
            }

            $applicationId = (int) $request->param('id');
            $supervisorEligible = filter_var($request->input('supervisor_eligible'), FILTER_VALIDATE_BOOLEAN);
            
            $applicationModel = new VacancyApplication();
            $application = $applicationModel->find($applicationId);

            if (!$application) {
                if ($isAjax) {
                    Response::json(['success' => false, 'message' => 'Candidatura não encontrada'], 404);
                    return;
                }
                Flash::add('error', 'Candidatura não encontrada.');
                redirect('/applications');
            }

            // Apenas candidaturas aprovadas podem ser marcadas como elegíveis
            if ($application['status'] !== 'aprovada') {
                if ($isAjax) {
                    Response::json(['success' => false, 'message' => 'Apenas candidaturas aprovadas podem ser elegíveis a supervisor'], 400);
                    return;
                }
                Flash::add('warning', 'Apenas candidaturas aprovadas podem ser elegíveis a supervisor.');
                redirect('/applications?vacancy=' . $application['vacancy_id']);
            }

            // Atualizar elegibilidade na tabela USERS (não na candidatura)
            $userModel = new User();
            $success = $userModel->update($application['vigilante_id'], [
                'supervisor_eligible' => $supervisorEligible ? 1 : 0,
                'updated_at' => now()
            ]);

            if (!$success) {
                throw new \Exception('Erro ao atualizar elegibilidade');
            }

            // Registrar atividade
            ActivityLogger::log('users', $application['vigilante_id'], 'toggle_supervisor_eligible', [
                'supervisor_eligible' => $supervisorEligible,
                'application_id' => $applicationId,
                'vacancy_id' => $application['vacancy_id'],
                'updated_by' => $user['id'],
            ]);

            if ($isAjax) {
                if (ob_get_length()) ob_clean();
                Response::json([
                    'success' => true,
                    'message' => $supervisorEligible 
                        ? 'Vigilante marcado como elegível a supervisor' 
                        : 'Elegibilidade a supervisor removida'
                ]);
                exit();
            }

            $message = $supervisorEligible 
                ? 'Vigilante marcado como elegível a supervisor com sucesso.' 
                : 'Elegibilidade a supervisor removida.';
            Flash::add('success', $message);
            redirect('/applications?vacancy=' . $application['vacancy_id']);
            
        } catch (\Exception $e) {
            if ($request->isAjax()) {
                if (ob_get_length()) ob_clean();
                Response::json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
                exit();
            }
            Flash::add('error', 'Erro ao atualizar elegibilidade');
            redirect('/applications');
        }
    }

    public function approveAll(Request $request)
    {
        $user = Auth::user();
        if (!$user || !in_array($user['role'], ['coordenador', 'membro'])) {
            Flash::add('error', 'Acesso restrito.');
            redirect('/dashboard');
        }

        // ✅ Validar CSRF
        if (!Csrf::validate($request)) {
            Flash::add('error', 'Token CSRF inválido');
            redirect('/applications');
        }

        $vacancyId = (int) $request->input('vacancy_id');
        
        if (!$vacancyId) {
            Flash::add('error', 'Vaga não especificada.');
            redirect('/applications');
        }

        $applicationModel = new VacancyApplication();
        $pendingApplications = $applicationModel->getByVacancy($vacancyId, 'pendente');

        if (empty($pendingApplications)) {
            Flash::add('warning', 'Nenhuma candidatura pendente para aprovar.');
            redirect('/applications?vacancy=' . $vacancyId);
        }

        $count = 0;
        foreach ($pendingApplications as $app) {
            $applicationModel->approve((int) $app['id'], (int) $user['id']);
            
            ActivityLogger::log('vacancy_applications', (int) $app['id'], 'approve_bulk', [
                'vacancy_id' => $vacancyId,
                'vigilante_id' => $app['vigilante_id'],
            ]);
            
            $count++;
        }

        Flash::add('success', "Todas as {$count} candidaturas foram aprovadas com sucesso.");
        redirect('/applications?vacancy=' . $vacancyId);
    }

    public function rejectAll(Request $request)
    {
        $user = Auth::user();
        if (!$user || !in_array($user['role'], ['coordenador', 'membro'])) {
            Flash::add('error', 'Acesso restrito.');
            redirect('/dashboard');
        }

        // ✅ Validar CSRF
        if (!Csrf::validate($request)) {
            Flash::add('error', 'Token CSRF inválido');
            redirect('/applications');
        }

        $vacancyId = (int) $request->input('vacancy_id');
        
        if (!$vacancyId) {
            Flash::add('error', 'Vaga não especificada.');
            redirect('/applications');
        }

        $applicationModel = new VacancyApplication();
        $pendingApplications = $applicationModel->getByVacancy($vacancyId, 'pendente');

        if (empty($pendingApplications)) {
            Flash::add('warning', 'Nenhuma candidatura pendente para rejeitar.');
            redirect('/applications?vacancy=' . $vacancyId);
        }

        $count = 0;
        foreach ($pendingApplications as $app) {
            $applicationModel->reject((int) $app['id'], (int) $user['id']);
            
            ActivityLogger::log('vacancy_applications', (int) $app['id'], 'reject_bulk', [
                'vacancy_id' => $vacancyId,
                'vigilante_id' => $app['vigilante_id'],
            ]);
            
            $count++;
        }

        Flash::add('success', "Todas as {$count} candidaturas foram rejeitadas.");
        redirect('/applications?vacancy=' . $vacancyId);
    }

    /**
     * Mostrar histórico de uma candidatura
     */
    public function history(Request $request): string
    {
        $user = Auth::user();
        if (!$user || !in_array($user['role'], ['coordenador', 'membro'])) {
            Flash::add('error', 'Acesso restrito.');
            redirect('/dashboard');
        }

        $applicationId = (int) $request->param('id');
        
        $applicationModel = new VacancyApplication();
        $application = $applicationModel->find($applicationId);

        if (!$application) {
            Flash::add('error', 'Candidatura não encontrada.');
            redirect('/applications');
        }

        // Buscar histórico
        $historyModel = new \App\Models\ApplicationStatusHistory();
        $history = $historyModel->getByApplication($applicationId);

        // Buscar dados da vaga e vigilante
        $vacancyModel = new ExamVacancy();
        $vacancy = $vacancyModel->find($application['vacancy_id']);

        $userModel = new User();
        $vigilante = $userModel->find($application['vigilante_id']);

        return $this->view('applications/history', [
            'user' => $user,
            'application' => $application,
            'vacancy' => $vacancy,
            'vigilante' => $vigilante,
            'history' => $history,
        ]);
    }

    /**
     * API: Obter estatísticas de uma vaga (para atualização dinâmica)
     */
    public function getStats(Request $request)
    {
        try {
            if (ob_get_length()) ob_clean();
            
            $user = Auth::user();
            if (!$user || !in_array($user['role'], ['coordenador', 'membro'])) {
                Response::json(['success' => false, 'message' => 'Acesso restrito'], 403);
                return;
            }

            $vacancyId = (int) $request->input('vacancy');
            if (!$vacancyId) {
                Response::json(['success' => false, 'message' => 'Vaga não especificada'], 400);
                return;
            }
            
            $applicationModel = new VacancyApplication();
            $statistics = $applicationModel->countByStatus($vacancyId);
            
            Response::json([
                'success' => true,
                'stats' => [
                    'pending' => $statistics['pendente'] ?? 0,
                    'approved' => $statistics['aprovada'] ?? 0,
                    'rejected' => $statistics['rejeitada'] ?? 0,
                    'cancelled' => $statistics['cancelada'] ?? 0,
                    'total' => array_sum($statistics)
                ]
            ]);
            
        } catch (\Exception $e) {
            if (ob_get_length()) ob_clean();
            Response::json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
