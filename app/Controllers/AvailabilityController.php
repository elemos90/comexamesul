<?php

namespace App\Controllers;

use App\Http\Request;
use App\Models\User;
use App\Models\ExamVacancy;
use App\Models\VacancyApplication;
use App\Models\AvailabilityChangeRequest;
use App\Models\JuryVigilante;
use App\Services\ActivityLogger;
use App\Services\EmailNotificationService;
use App\Utils\Auth;
use App\Utils\Flash;
use App\Utils\FileUploader;

class AvailabilityController extends Controller
{
    public function show(): string
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'vigilante') {
            Flash::add('error', 'Funcao restrita a vigilantes.');
            redirect('/dashboard');
        }

        // Verificar se perfil está completo
        $userModel = new User();
        $missingFields = $userModel->getMissingProfileFields($user);
        $profileComplete = empty($missingFields);

        // Buscar vagas abertas (e fechar expiradas automaticamente)
        $vacancyModel = new ExamVacancy();
        $vacancyModel->closeExpired();
        $openVacancies = $vacancyModel->openVacancies();

        // Buscar candidaturas do vigilante
        $applicationModel = new VacancyApplication();
        $myApplications = $applicationModel->getByVigilante((int) $user['id']);

        // Buscar solicitações de mudança pendentes
        $changeRequestModel = new AvailabilityChangeRequest();
        $pendingRequests = $changeRequestModel->getByVigilante((int) $user['id']);
        
        // Filtrar apenas pendentes
        $pendingRequests = array_filter($pendingRequests, function($req) {
            return $req['status'] === 'pendente';
        });

        return $this->view('availability/index', [
            'user' => $user,
            'profileComplete' => $profileComplete,
            'missingFields' => $missingFields,
            'openVacancies' => $openVacancies,
            'myApplications' => $myApplications,
            'pendingRequests' => $pendingRequests,
        ]);
    }

    public function apply(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'vigilante') {
            Flash::add('error', 'Funcao restrita a vigilantes.');
            redirect('/availability');
        }

        $vacancyId = (int) $request->param('id');
        
        // Verificar se perfil está completo
        $userModel = new User();
        if (!$userModel->isProfileComplete($user)) {
            Flash::add('error', 'Complete seu perfil antes de se candidatar a uma vaga.');
            redirect('/profile');
        }

        // Verificar se vaga existe e está aberta
        $vacancyModel = new ExamVacancy();
        $vacancy = $vacancyModel->find($vacancyId);
        
        if (!$vacancy) {
            Flash::add('error', 'Vaga nao encontrada.');
            redirect('/availability');
        }
        
        if ($vacancy['status'] !== 'aberta') {
            $statusMsg = $vacancy['status'] === 'encerrada' 
                ? 'Esta vaga foi encerrada e arquivada. Nao aceita mais candidaturas.' 
                : 'Esta vaga ja foi fechada. Nao aceita mais candidaturas.';
            Flash::add('error', $statusMsg);
            redirect('/availability');
        }

        // Verificar deadline
        if (strtotime($vacancy['deadline_at']) < time()) {
            Flash::add('error', 'Prazo de candidatura encerrado.');
            redirect('/availability');
        }

        // Verificar se já se candidatou
        $applicationModel = new VacancyApplication();
        if ($applicationModel->hasApplied($vacancyId, (int) $user['id'])) {
            Flash::add('warning', 'Voce ja se candidatou a esta vaga.');
            redirect('/availability');
        }

        // Candidatar-se
        $notes = $request->input('notes');
        $applicationId = $applicationModel->apply($vacancyId, (int) $user['id'], $notes);

        ActivityLogger::log('vacancy_applications', $vacancyId, 'apply', [
            'vacancy_id' => $vacancyId,
            'vigilante_id' => $user['id'],
        ]);

        // Notificar coordenadores sobre nova candidatura
        $emailService = new EmailNotificationService();
        $emailService->notifyNewApplication($applicationId);

        Flash::add('success', 'Candidatura enviada com sucesso!');
        redirect('/availability');
    }

    public function requestCancel(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'vigilante') {
            Flash::add('error', 'Funcao restrita a vigilantes.');
            redirect('/availability');
        }

        $applicationId = (int) $request->param('id');
        
        $applicationModel = new VacancyApplication();
        $application = $applicationModel->find($applicationId);

        if (!$application || $application['vigilante_id'] != $user['id']) {
            Flash::add('error', 'Candidatura nao encontrada.');
            redirect('/availability');
        }

        if ($application['status'] !== 'aprovada') {
            Flash::add('error', 'Apenas candidaturas aprovadas podem ser canceladas.');
            redirect('/availability');
        }

        // Verificar se a vaga ainda está aberta
        $vacancyModel = new ExamVacancy();
        $vacancy = $vacancyModel->find($application['vacancy_id']);
        
        if (!$vacancy) {
            Flash::add('error', 'Vaga nao encontrada.');
            redirect('/availability');
        }
        
        if ($vacancy['status'] !== 'aberta') {
            $statusMsg = $vacancy['status'] === 'encerrada' 
                ? 'Esta vaga foi encerrada e arquivada. Nao e possivel alterar a candidatura.' 
                : 'Esta vaga ja foi fechada. Nao e possivel alterar a candidatura.';
            Flash::add('error', $statusMsg);
            redirect('/availability');
        }

        // Verificar se já está alocado a júris
        $juryVigilanteModel = new JuryVigilante();
        $allocations = $juryVigilanteModel->getByVigilante((int) $user['id']);
        
        $hasAllocation = !empty($allocations);
        $juryDetails = [];
        
        if ($hasAllocation) {
            foreach ($allocations as $allocation) {
                $juryDetails[] = [
                    'jury_id' => $allocation['jury_id'],
                    'subject' => $allocation['subject'],
                    'exam_date' => $allocation['exam_date'],
                    'location' => $allocation['location'],
                    'room' => $allocation['room'],
                ];
            }
        }

        // Verificar se já tem solicitação pendente
        $changeRequestModel = new AvailabilityChangeRequest();
        if ($changeRequestModel->hasPendingRequest((int) $user['id'], $applicationId)) {
            Flash::add('warning', 'Voce ja tem uma solicitacao pendente para esta candidatura.');
            redirect('/availability');
        }

        // Se não tem alocação, cancelar diretamente
        if (!$hasAllocation) {
            $applicationModel->cancelApplication($applicationId);
            ActivityLogger::log('vacancy_applications', $applicationId, 'cancel_direct');
            Flash::add('success', 'Candidatura cancelada com sucesso.');
            redirect('/availability');
        }

        // Se tem alocação, mostrar formulário de justificativa
        return $this->view('availability/request_cancel', [
            'user' => $user,
            'application' => $application,
            'allocations' => $allocations,
        ]);
    }

    public function submitCancelRequest(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'vigilante') {
            Flash::add('error', 'Funcao restrita a vigilantes.');
            redirect('/availability');
        }

        $applicationId = (int) $request->param('id');
        $reason = trim($request->input('reason'));

        // Validar justificativa
        if (empty($reason) || strlen($reason) < 20) {
            Flash::add('error', 'Justificativa deve ter no minimo 20 caracteres.');
            redirect('/availability/' . $applicationId . '/cancel');
        }

        $applicationModel = new VacancyApplication();
        $application = $applicationModel->find($applicationId);

        if (!$application || $application['vigilante_id'] != $user['id']) {
            Flash::add('error', 'Candidatura nao encontrada.');
            redirect('/availability');
        }

        // Verificar se a vaga ainda está aberta
        $vacancyModel = new ExamVacancy();
        $vacancy = $vacancyModel->find($application['vacancy_id']);
        
        if (!$vacancy) {
            Flash::add('error', 'Vaga nao encontrada.');
            redirect('/availability');
        }
        
        if ($vacancy['status'] !== 'aberta') {
            $statusMsg = $vacancy['status'] === 'encerrada' 
                ? 'Esta vaga foi encerrada e arquivada. Nao e possivel alterar a candidatura.' 
                : 'Esta vaga ja foi fechada. Nao e possivel alterar a candidatura.';
            Flash::add('error', $statusMsg);
            redirect('/availability');
        }

        // Verificar alocações
        $juryVigilanteModel = new JuryVigilante();
        $allocations = $juryVigilanteModel->getByVigilante((int) $user['id']);
        $hasAllocation = !empty($allocations);
        
        $juryDetails = [];
        if ($hasAllocation) {
            foreach ($allocations as $allocation) {
                $juryDetails[] = [
                    'jury_id' => $allocation['jury_id'],
                    'subject' => $allocation['subject'],
                    'exam_date' => $allocation['exam_date'],
                ];
            }
        }

        // Processar upload de anexo com validação robusta
        $attachmentPath = null;
        $attachmentOriginalName = null;

        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            try {
                $attachmentPath = FileUploader::upload(
                    $_FILES['attachment'],
                    'storage/uploads/justifications'
                );
                $attachmentOriginalName = $_FILES['attachment']['name'];
            } catch (\Exception $e) {
                Flash::add('error', $e->getMessage());
                redirect('/availability/' . $applicationId . '/cancel');
            }
        }

        // Criar solicitação
        $changeRequestModel = new AvailabilityChangeRequest();
        $requestId = $changeRequestModel->createRequest([
            'vigilante_id' => $user['id'],
            'application_id' => $applicationId,
            'request_type' => 'cancelamento',
            'reason' => $reason,
            'attachment_path' => $attachmentPath,
            'attachment_original_name' => $attachmentOriginalName,
            'has_allocation' => $hasAllocation ? 1 : 0,
            'jury_details' => !empty($juryDetails) ? json_encode($juryDetails) : null,
        ]);

        ActivityLogger::log('availability_change_requests', $requestId, 'create', [
            'application_id' => $applicationId,
            'has_allocation' => $hasAllocation,
        ]);

        Flash::add('success', 'Solicitacao de cancelamento enviada. Aguarde aprovacao do coordenador.');
        redirect('/availability');
    }

    /* DEPRECATED: Sistema simplificado usa apenas candidaturas específicas, não mais disponibilidade geral
    public function requestAvailabilityChange(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'vigilante') {
            Flash::add('error', 'Funcao restrita a vigilantes.');
            redirect('/availability');
        }

        $newStatus = (int) $request->param('status'); // 0 = indisponível, 1 = disponível
        
        // Verificar se já está alocado a júris
        $juryVigilanteModel = new JuryVigilante();
        $allocations = $juryVigilanteModel->getByVigilante((int) $user['id']);
        
        // Se não tem alocação E quer ficar indisponível, mudar direto
        if (empty($allocations) && $newStatus == 0) {
            $userModel = new User();
            $userModel->updateUser((int) $user['id'], [
                'available_for_vigilance' => 0,
            ]);
            
            ActivityLogger::log('users', (int) $user['id'], 'update_availability', [
                'available' => false,
                'direct' => true,
            ]);
            
            Flash::add('success', 'Disponibilidade atualizada para indisponivel.');
            redirect('/availability');
        }

        // Se não tem alocação E quer ficar disponível, mudar direto
        if (empty($allocations) && $newStatus == 1) {
            $userModel = new User();
            $userModel->updateUser((int) $user['id'], [
                'available_for_vigilance' => 1,
            ]);
            
            ActivityLogger::log('users', (int) $user['id'], 'update_availability', [
                'available' => true,
                'direct' => true,
            ]);
            
            Flash::add('success', 'Disponibilidade atualizada para disponivel.');
            redirect('/availability');
        }

        // Se tem alocação, exigir justificativa
        return $this->view('availability/request_change', [
            'user' => $user,
            'newStatus' => $newStatus,
            'allocations' => $allocations,
        ]);
    }

    public function submitAvailabilityChange(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'vigilante') {
            Flash::add('error', 'Funcao restrita a vigilantes.');
            redirect('/availability');
        }

        $newStatus = (int) $request->input('new_status');
        $reason = trim($request->input('reason'));

        // Validar justificativa
        if (empty($reason) || strlen($reason) < 20) {
            Flash::add('error', 'Justificativa deve ter no minimo 20 caracteres.');
            redirect('/availability/change/' . $newStatus);
        }

        // Verificar alocações
        $juryVigilanteModel = new JuryVigilante();
        $allocations = $juryVigilanteModel->getByVigilante((int) $user['id']);
        $hasAllocation = !empty($allocations);
        
        $juryDetails = [];
        if ($hasAllocation) {
            foreach ($allocations as $allocation) {
                $juryDetails[] = [
                    'jury_id' => $allocation['jury_id'],
                    'subject' => $allocation['subject'],
                    'exam_date' => $allocation['exam_date'],
                    'location' => $allocation['location'],
                    'room' => $allocation['room'],
                ];
            }
        }

        // Processar upload de anexo
        $attachmentPath = null;
        $attachmentOriginalName = null;

        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/storage/uploads/justifications/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

            if (!in_array($fileExtension, $allowedExtensions)) {
                Flash::add('error', 'Tipo de arquivo nao permitido. Use: PDF, JPG, PNG, DOC ou DOCX.');
                redirect('/availability/change/' . $newStatus);
            }

            if ($_FILES['attachment']['size'] > 5 * 1024 * 1024) {
                Flash::add('error', 'Arquivo muito grande. Tamanho maximo: 5MB.');
                redirect('/availability/change/' . $newStatus);
            }

            $fileName = uniqid('avail_') . '_' . time() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
                $attachmentPath = 'storage/uploads/justifications/' . $fileName;
                $attachmentOriginalName = $_FILES['attachment']['name'];
            }
        }

        // Criar solicitação de alteração
        $changeRequestModel = new AvailabilityChangeRequest();
        
        // Buscar uma candidatura aprovada do vigilante para vincular
        $applicationModel = new VacancyApplication();
        $applications = $applicationModel->getByVigilante((int) $user['id']);
        $approvedApp = null;
        foreach ($applications as $app) {
            if ($app['status'] === 'aprovada') {
                $approvedApp = $app;
                break;
            }
        }

        if (!$approvedApp) {
            Flash::add('error', 'Voce precisa ter uma candidatura aprovada para alterar disponibilidade.');
            redirect('/availability');
        }

        $requestId = $changeRequestModel->createRequest([
            'vigilante_id' => $user['id'],
            'application_id' => $approvedApp['id'],
            'request_type' => 'alteracao',
            'reason' => $reason,
            'attachment_path' => $attachmentPath,
            'attachment_original_name' => $attachmentOriginalName,
            'has_allocation' => $hasAllocation ? 1 : 0,
            'jury_details' => !empty($juryDetails) ? json_encode(['new_status' => $newStatus, 'juries' => $juryDetails]) : json_encode(['new_status' => $newStatus]),
        ]);

        ActivityLogger::log('availability_change_requests', $requestId, 'create_change', [
            'new_status' => $newStatus,
            'has_allocation' => $hasAllocation,
        ]);

        Flash::add('success', 'Solicitacao de alteracao de disponibilidade enviada. Aguarde aprovacao do coordenador.');
        redirect('/availability');
    }
    */

    public function cancelDirect(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'vigilante') {
            Flash::add('error', 'Funcao restrita a vigilantes.');
            redirect('/availability');
        }

        $applicationId = (int) $request->param('id');
        
        $applicationModel = new VacancyApplication();
        $application = $applicationModel->find($applicationId);

        if (!$application || $application['vigilante_id'] != $user['id']) {
            Flash::add('error', 'Candidatura nao encontrada.');
            redirect('/availability');
        }

        if ($application['status'] !== 'pendente') {
            Flash::add('error', 'Apenas candidaturas pendentes podem ser canceladas diretamente.');
            redirect('/availability');
        }

        // Verificar se a vaga ainda está aberta
        $vacancyModel = new ExamVacancy();
        $vacancy = $vacancyModel->find($application['vacancy_id']);
        
        if (!$vacancy) {
            Flash::add('error', 'Vaga nao encontrada.');
            redirect('/availability');
        }
        
        if ($vacancy['status'] !== 'aberta') {
            $statusMsg = $vacancy['status'] === 'encerrada' 
                ? 'Esta vaga foi encerrada e arquivada. Nao e possivel alterar a candidatura.' 
                : 'Esta vaga ja foi fechada. Nao e possivel alterar a candidatura.';
            Flash::add('error', $statusMsg);
            redirect('/availability');
        }

        $applicationModel->cancelApplication($applicationId);

        ActivityLogger::log('vacancy_applications', $applicationId, 'cancel_direct', [
            'vacancy_id' => $application['vacancy_id'],
            'vigilante_id' => $user['id'],
        ]);

        Flash::add('success', 'Candidatura cancelada com sucesso.');
        redirect('/availability');
    }

    public function reapply(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'vigilante') {
            Flash::add('error', 'Funcao restrita a vigilantes.');
            redirect('/availability');
            return;
        }

        $applicationId = (int) $request->param('id');
        
        $applicationModel = new VacancyApplication();
        $application = $applicationModel->find($applicationId);

        if (!$application || $application['vigilante_id'] != $user['id']) {
            Flash::add('error', 'Candidatura nao encontrada.');
            redirect('/availability');
            return;
        }

        if (!in_array($application['status'], ['cancelada', 'rejeitada'])) {
            Flash::add('error', 'Apenas candidaturas canceladas ou rejeitadas podem ser reativadas.');
            redirect('/availability');
            return;
        }

        // Verificar se a vaga ainda está aberta
        $vacancyModel = new ExamVacancy();
        $vacancy = $vacancyModel->find($application['vacancy_id']);

        if (!$vacancy) {
            Flash::add('error', 'Vaga nao encontrada.');
            redirect('/availability');
            return;
        }
        
        if ($vacancy['status'] !== 'aberta') {
            $statusMsg = $vacancy['status'] === 'encerrada' 
                ? 'Esta vaga foi encerrada e arquivada. Nao e possivel recandidatar-se.' 
                : 'Esta vaga ja foi fechada pelo coordenador. Nao e possivel recandidatar-se.';
            Flash::add('error', $statusMsg);
            redirect('/availability');
            return;
        }

        // Verificar se o perfil ainda está completo
        $userModel = new User();
        $currentUser = $userModel->find((int) $user['id']);
        
        if (!$userModel->isProfileComplete($currentUser)) {
            Flash::add('error', 'Complete seu perfil antes de se candidatar.');
            redirect('/profile');
        }

        // Verificar limite de recandidaturas (máximo 3)
        $reapplyCount = (int) ($application['reapply_count'] ?? 0);
        if ($reapplyCount >= 3) {
            Flash::add('error', 'Voce atingiu o limite de 3 recandidaturas para esta vaga. Entre em contato com a coordenacao.');
            redirect('/availability');
        }

        // Reativar candidatura (mudar status para pendente e incrementar contador)
        $applicationModel->update($applicationId, [
            'status' => 'pendente',
            'applied_at' => now(),
            'reviewed_at' => null,
            'reviewed_by' => null,
            'rejection_reason' => null,
            'reapply_count' => $reapplyCount + 1,
            'updated_at' => now(),
        ]);

        ActivityLogger::log('vacancy_applications', $applicationId, 'reapply', [
            'vacancy_id' => $application['vacancy_id'],
            'vigilante_id' => $user['id'],
            'previous_status' => $application['status'],
        ]);

        Flash::add('success', 'Candidatura reenviada com sucesso! Aguarde aprovacao do coordenador.');
        redirect('/availability');
    }
}
