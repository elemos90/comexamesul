<?php

namespace App\Services;

use App\Models\EmailNotification;
use App\Models\User;
use App\Models\VacancyApplication;
use App\Models\ExamVacancy;

class EmailNotificationService
{
    private EmailNotification $emailModel;
    private User $userModel;
    private string $fromEmail;
    private string $fromName;
    private string $appUrl;

    public function __construct()
    {
        $this->emailModel = new EmailNotification();
        $this->userModel = new User();
        $this->fromEmail = $_ENV['MAIL_FROM'] ?? 'noreply@unilicungo.ac.mz';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? 'Comissão de Exames';
        $this->appUrl = $_ENV['APP_URL'] ?? 'http://localhost';
    }

    /**
     * Notificar aprovação de candidatura
     */
    public function notifyApplicationApproved(int $applicationId): bool
    {
        $applicationModel = new VacancyApplication();
        $application = $applicationModel->find($applicationId);
        
        if (!$application) {
            return false;
        }

        $vigilante = $this->userModel->find($application['vigilante_id']);
        $vacancyModel = new ExamVacancy();
        $vacancy = $vacancyModel->find($application['vacancy_id']);

        $subject = '🎉 Candidatura Aprovada - ' . $vacancy['title'];
        $body = $this->renderTemplate('application_approved', [
            'vigilante_name' => $vigilante['name'],
            'vacancy_title' => $vacancy['title'],
            'app_url' => $this->appUrl,
        ]);

        return $this->emailModel->queue($vigilante['id'], 'application_approved', $subject, $body) > 0;
    }

    /**
     * Notificar rejeição de candidatura
     */
    public function notifyApplicationRejected(int $applicationId, ?string $reason = null): bool
    {
        $applicationModel = new VacancyApplication();
        $application = $applicationModel->find($applicationId);
        
        if (!$application) {
            return false;
        }

        $vigilante = $this->userModel->find($application['vigilante_id']);
        $vacancyModel = new ExamVacancy();
        $vacancy = $vacancyModel->find($application['vacancy_id']);

        $subject = '❌ Candidatura Rejeitada - ' . $vacancy['title'];
        $body = $this->renderTemplate('application_rejected', [
            'vigilante_name' => $vigilante['name'],
            'vacancy_title' => $vacancy['title'],
            'rejection_reason' => $reason ?? 'Não especificado',
            'app_url' => $this->appUrl,
        ]);

        return $this->emailModel->queue($vigilante['id'], 'application_rejected', $subject, $body) > 0;
    }

    /**
     * Notificar cancelamento aprovado
     */
    public function notifyCancellationApproved(int $applicationId): bool
    {
        $applicationModel = new VacancyApplication();
        $application = $applicationModel->find($applicationId);
        
        if (!$application) {
            return false;
        }

        $vigilante = $this->userModel->find($application['vigilante_id']);
        $vacancyModel = new ExamVacancy();
        $vacancy = $vacancyModel->find($application['vacancy_id']);

        $subject = '✅ Solicitação de Cancelamento Aprovada - ' . $vacancy['title'];
        $body = $this->renderTemplate('cancellation_approved', [
            'vigilante_name' => $vigilante['name'],
            'vacancy_title' => $vacancy['title'],
            'app_url' => $this->appUrl,
        ]);

        return $this->emailModel->queue($vigilante['id'], 'cancellation_approved', $subject, $body) > 0;
    }

    /**
     * Notificar cancelamento rejeitado
     */
    public function notifyCancellationRejected(int $applicationId, string $reason): bool
    {
        $applicationModel = new VacancyApplication();
        $application = $applicationModel->find($applicationId);
        
        if (!$application) {
            return false;
        }

        $vigilante = $this->userModel->find($application['vigilante_id']);
        $vacancyModel = new ExamVacancy();
        $vacancy = $vacancyModel->find($application['vacancy_id']);

        $subject = '❌ Solicitação de Cancelamento Rejeitada - ' . $vacancy['title'];
        $body = $this->renderTemplate('cancellation_rejected', [
            'vigilante_name' => $vigilante['name'],
            'vacancy_title' => $vacancy['title'],
            'rejection_reason' => $reason,
            'app_url' => $this->appUrl,
        ]);

        return $this->emailModel->queue($vigilante['id'], 'cancellation_rejected', $subject, $body) > 0;
    }

    /**
     * Notificar prazo próximo de candidatura
     */
    public function notifyDeadlineApproaching(int $vacancyId): int
    {
        $vacancyModel = new ExamVacancy();
        $vacancy = $vacancyModel->find($vacancyId);
        
        if (!$vacancy) {
            return 0;
        }

        // Buscar vigilantes com perfil completo que ainda não se candidataram
        $sql = "SELECT u.* 
                FROM users u 
                WHERE u.role = 'vigilante' 
                  AND u.profile_completed = 1
                  AND u.id NOT IN (
                      SELECT va.vigilante_id 
                      FROM vacancy_applications va 
                      WHERE va.vacancy_id = :vacancy_id
                  )";
        
        $db = $this->userModel->getDb();
        $stmt = $db->prepare($sql);
        $stmt->execute(['vacancy_id' => $vacancyId]);
        $vigilantes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $count = 0;
        $deadline = new \DateTime($vacancy['deadline_at']);
        $hoursLeft = (int) ((strtotime($vacancy['deadline_at']) - time()) / 3600);

        foreach ($vigilantes as $vigilante) {
            $subject = '⏰ Prazo Próximo - ' . $vacancy['title'];
            $body = $this->renderTemplate('deadline_approaching', [
                'vigilante_name' => $vigilante['name'],
                'vacancy_title' => $vacancy['title'],
                'deadline' => $deadline->format('d/m/Y H:i'),
                'hours_left' => $hoursLeft,
                'app_url' => $this->appUrl,
            ]);

            if ($this->emailModel->queue($vigilante['id'], 'deadline_approaching', $subject, $body) > 0) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Notificar coordenadores sobre nova candidatura
     */
    public function notifyNewApplication(int $applicationId): int
    {
        $applicationModel = new VacancyApplication();
        $application = $applicationModel->find($applicationId);
        
        if (!$application) {
            return 0;
        }

        $vigilante = $this->userModel->find($application['vigilante_id']);
        $vacancyModel = new ExamVacancy();
        $vacancy = $vacancyModel->find($application['vacancy_id']);

        // Buscar coordenadores e membros
        $coordinators = $this->userModel->findByRole(['coordenador', 'membro']);

        $count = 0;
        foreach ($coordinators as $coordinator) {
            $subject = '📝 Nova Candidatura - ' . $vacancy['title'];
            $body = $this->renderTemplate('new_application', [
                'coordinator_name' => $coordinator['name'],
                'vigilante_name' => $vigilante['name'],
                'vacancy_title' => $vacancy['title'],
                'app_url' => $this->appUrl,
            ]);

            if ($this->emailModel->queue($coordinator['id'], 'new_application', $subject, $body) > 0) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Notificar coordenadores sobre solicitação de cancelamento
     */
    public function notifyCancellationRequest(int $requestId): int
    {
        $requestModel = new \App\Models\AvailabilityChangeRequest();
        $request = $requestModel->find($requestId);
        
        if (!$request) {
            return 0;
        }

        $vigilante = $this->userModel->find($request['vigilante_id']);
        $applicationModel = new VacancyApplication();
        $application = $applicationModel->find($request['application_id']);
        $vacancyModel = new ExamVacancy();
        $vacancy = $vacancyModel->find($application['vacancy_id']);

        // Buscar coordenadores
        $coordinators = $this->userModel->findByRole(['coordenador']);

        $count = 0;
        $isUrgent = $request['has_allocation'] == 1;

        foreach ($coordinators as $coordinator) {
            $subject = ($isUrgent ? '🚨 ' : '🔄 ') . 'Solicitação de Cancelamento - ' . $vacancy['title'];
            $body = $this->renderTemplate('cancellation_request', [
                'coordinator_name' => $coordinator['name'],
                'vigilante_name' => $vigilante['name'],
                'vacancy_title' => $vacancy['title'],
                'reason' => $request['reason'],
                'is_urgent' => $isUrgent,
                'app_url' => $this->appUrl,
            ]);

            if ($this->emailModel->queue($coordinator['id'], 'cancellation_request', $subject, $body) > 0) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Renderizar template de email
     */
    private function renderTemplate(string $template, array $data): string
    {
        $templates = [
            'application_approved' => "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .header { background: #10b981; color: white; padding: 20px; text-align: center; }
                        .content { padding: 30px; background: #f9fafb; }
                        .button { background: #10b981; color: white; padding: 12px 24px; 
                                  text-decoration: none; border-radius: 6px; display: inline-block; 
                                  margin: 20px 0; }
                        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='header'>
                        <h2>🎉 Candidatura Aprovada!</h2>
                    </div>
                    <div class='content'>
                        <p>Olá <strong>{$data['vigilante_name']}</strong>,</p>
                        
                        <p>Temos o prazer de informar que sua candidatura para <strong>{$data['vacancy_title']}</strong> foi <strong style='color: #10b981;'>APROVADA</strong>!</p>
                        
                        <p>Você será notificado em breve sobre a alocação aos júris e demais instruções.</p>
                        
                        <p style='text-align: center;'>
                            <a href='{$data['app_url']}/availability' class='button'>Ver Minhas Candidaturas</a>
                        </p>
                        
                        <p>Parabéns e até breve!</p>
                        
                        <p>Atenciosamente,<br>
                        <strong>Comissão de Exames de Admissão</strong><br>
                        UniLicungo</p>
                    </div>
                    <div class='footer'>
                        Este é um email automático. Por favor, não responda.
                    </div>
                </body>
                </html>
            ",
            
            'application_rejected' => "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .header { background: #ef4444; color: white; padding: 20px; text-align: center; }
                        .content { padding: 30px; background: #f9fafb; }
                        .reason-box { background: #fee2e2; border-left: 4px solid #ef4444; 
                                      padding: 15px; margin: 20px 0; }
                        .button { background: #2563eb; color: white; padding: 12px 24px; 
                                  text-decoration: none; border-radius: 6px; display: inline-block; 
                                  margin: 20px 0; }
                        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='header'>
                        <h2>❌ Candidatura Rejeitada</h2>
                    </div>
                    <div class='content'>
                        <p>Olá <strong>{$data['vigilante_name']}</strong>,</p>
                        
                        <p>Informamos que sua candidatura para <strong>{$data['vacancy_title']}</strong> foi <strong style='color: #ef4444;'>rejeitada</strong>.</p>
                        
                        <div class='reason-box'>
                            <strong>Motivo da Rejeição:</strong><br>
                            {$data['rejection_reason']}
                        </div>
                        
                        <p>Você pode corrigir as pendências e <strong>recandidatar-se</strong> enquanto a vaga estiver aberta.</p>
                        
                        <p style='text-align: center;'>
                            <a href='{$data['app_url']}/availability' class='button'>Ver Vagas Abertas</a>
                        </p>
                        
                        <p>Atenciosamente,<br>
                        <strong>Comissão de Exames de Admissão</strong><br>
                        UniLicungo</p>
                    </div>
                    <div class='footer'>
                        Este é um email automático. Por favor, não responda.
                    </div>
                </body>
                </html>
            ",
            
            'deadline_approaching' => "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .header { background: #f59e0b; color: white; padding: 20px; text-align: center; }
                        .content { padding: 30px; background: #f9fafb; }
                        .button { background: #f59e0b; color: white; padding: 12px 24px; 
                                  text-decoration: none; border-radius: 6px; display: inline-block; 
                                  margin: 20px 0; }
                        .urgency { background: #fef3c7; border: 2px solid #f59e0b; 
                                   padding: 15px; margin: 20px 0; text-align: center; 
                                   font-size: 18px; font-weight: bold; }
                        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='header'>
                        <h2>⏰ Prazo Próximo de Candidatura!</h2>
                    </div>
                    <div class='content'>
                        <p>Olá <strong>{$data['vigilante_name']}</strong>,</p>
                        
                        <p>O prazo para candidatura à vaga <strong>{$data['vacancy_title']}</strong> está próximo do fim!</p>
                        
                        <div class='urgency'>
                            ⏰ Restam apenas {$data['hours_left']} horas!<br>
                            Prazo final: {$data['deadline']}
                        </div>
                        
                        <p>Não perca esta oportunidade! Candidate-se agora:</p>
                        
                        <p style='text-align: center;'>
                            <a href='{$data['app_url']}/availability' class='button'>Candidatar-me Agora</a>
                        </p>
                        
                        <p>Atenciosamente,<br>
                        <strong>Comissão de Exames de Admissão</strong><br>
                        UniLicungo</p>
                    </div>
                    <div class='footer'>
                        Este é um email automático. Por favor, não responda.
                    </div>
                </body>
                </html>
            ",
            
            'new_application' => "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
                        .content { padding: 30px; background: #f9fafb; }
                        .button { background: #2563eb; color: white; padding: 12px 24px; 
                                  text-decoration: none; border-radius: 6px; display: inline-block; 
                                  margin: 20px 0; }
                        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='header'>
                        <h2>📝 Nova Candidatura Recebida</h2>
                    </div>
                    <div class='content'>
                        <p>Olá <strong>{$data['coordinator_name']}</strong>,</p>
                        
                        <p>Uma nova candidatura foi recebida:</p>
                        
                        <ul>
                            <li><strong>Vigilante:</strong> {$data['vigilante_name']}</li>
                            <li><strong>Vaga:</strong> {$data['vacancy_title']}</li>
                        </ul>
                        
                        <p>Por favor, revise a candidatura e aprove ou rejeite conforme critérios estabelecidos.</p>
                        
                        <p style='text-align: center;'>
                            <a href='{$data['app_url']}/applications' class='button'>Revisar Candidaturas</a>
                        </p>
                        
                        <p>Atenciosamente,<br>
                        <strong>Sistema de Gestão de Exames</strong></p>
                    </div>
                    <div class='footer'>
                        Este é um email automático. Por favor, não responda.
                    </div>
                </body>
                </html>
            ",
            
            'cancellation_request' => "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .header { background: " . ($data['is_urgent'] ? '#ef4444' : '#f59e0b') . "; color: white; padding: 20px; text-align: center; }
                        .content { padding: 30px; background: #f9fafb; }
                        .reason-box { background: #fef3c7; border-left: 4px solid #f59e0b; 
                                      padding: 15px; margin: 20px 0; }
                        .button { background: " . ($data['is_urgent'] ? '#ef4444' : '#f59e0b') . "; color: white; padding: 12px 24px; 
                                  text-decoration: none; border-radius: 6px; display: inline-block; 
                                  margin: 20px 0; }
                        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='header'>
                        <h2>" . ($data['is_urgent'] ? '🚨' : '🔄') . " Solicitação de Cancelamento</h2>
                    </div>
                    <div class='content'>
                        <p>Olá <strong>{$data['coordinator_name']}</strong>,</p>
                        
                        <p><strong>{$data['vigilante_name']}</strong> solicitou o cancelamento de sua candidatura para <strong>{$data['vacancy_title']}</strong>.</p>
                        
                        " . ($data['is_urgent'] ? "<p style='color: #ef4444; font-weight: bold;'>⚠️ URGENTE: O vigilante já está alocado a júris!</p>" : "") . "
                        
                        <div class='reason-box'>
                            <strong>Justificativa:</strong><br>
                            {$data['reason']}
                        </div>
                        
                        <p>Por favor, revise a solicitação e aprove ou rejeite.</p>
                        
                        <p style='text-align: center;'>
                            <a href='{$data['app_url']}/applications' class='button'>Revisar Solicitação</a>
                        </p>
                        
                        <p>Atenciosamente,<br>
                        <strong>Sistema de Gestão de Exames</strong></p>
                    </div>
                    <div class='footer'>
                        Este é um email automático. Por favor, não responda.
                    </div>
                </body>
                </html>
            ",
            
            'cancellation_approved' => "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .header { background: #10b981; color: white; padding: 20px; text-align: center; }
                        .content { padding: 30px; background: #f9fafb; }
                        .button { background: #10b981; color: white; padding: 12px 24px; 
                                  text-decoration: none; border-radius: 6px; display: inline-block; 
                                  margin: 20px 0; }
                        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='header'>
                        <h2>✅ Solicitação de Cancelamento Aprovada</h2>
                    </div>
                    <div class='content'>
                        <p>Olá <strong>{$data['vigilante_name']}</strong>,</p>
                        
                        <p>Sua solicitação de cancelamento para <strong>{$data['vacancy_title']}</strong> foi <strong style='color: #10b981;'>aprovada</strong>.</p>
                        
                        <p>Sua candidatura foi cancelada com sucesso. Você pode recandidatar-se se desejar, desde que a vaga ainda esteja aberta.</p>
                        
                        <p style='text-align: center;'>
                            <a href='{$data['app_url']}/availability' class='button'>Ver Minhas Candidaturas</a>
                        </p>
                        
                        <p>Atenciosamente,<br>
                        <strong>Comissão de Exames de Admissão</strong><br>
                        UniLicungo</p>
                    </div>
                    <div class='footer'>
                        Este é um email automático. Por favor, não responda.
                    </div>
                </body>
                </html>
            ",
            
            'cancellation_rejected' => "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .header { background: #ef4444; color: white; padding: 20px; text-align: center; }
                        .content { padding: 30px; background: #f9fafb; }
                        .reason-box { background: #fee2e2; border-left: 4px solid #ef4444; 
                                      padding: 15px; margin: 20px 0; }
                        .button { background: #2563eb; color: white; padding: 12px 24px; 
                                  text-decoration: none; border-radius: 6px; display: inline-block; 
                                  margin: 20px 0; }
                        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='header'>
                        <h2>❌ Solicitação de Cancelamento Rejeitada</h2>
                    </div>
                    <div class='content'>
                        <p>Olá <strong>{$data['vigilante_name']}</strong>,</p>
                        
                        <p>Informamos que sua solicitação de cancelamento para <strong>{$data['vacancy_title']}</strong> foi <strong style='color: #ef4444;'>rejeitada</strong>.</p>
                        
                        <div class='reason-box'>
                            <strong>Motivo da Rejeição:</strong><br>
                            {$data['rejection_reason']}
                        </div>
                        
                        <p>Sua candidatura permanece ativa. Entre em contato com a coordenação para mais esclarecimentos.</p>
                        
                        <p style='text-align: center;'>
                            <a href='{$data['app_url']}/availability' class='button'>Ver Minhas Candidaturas</a>
                        </p>
                        
                        <p>Atenciosamente,<br>
                        <strong>Comissão de Exames de Admissão</strong><br>
                        UniLicungo</p>
                    </div>
                    <div class='footer'>
                        Este é um email automático. Por favor, não responda.
                    </div>
                </body>
                </html>
            ",
        ];

        return $templates[$template] ?? '';
    }

    /**
     * Enviar um email (método principal)
     */
    public function send(int $notificationId): bool
    {
        $notification = $this->emailModel->find($notificationId);
        if (!$notification) {
            return false;
        }

        $user = $this->userModel->find($notification['user_id']);
        if (!$user || !$user['email']) {
            $this->emailModel->markAsFailed($notificationId, 'Usuário não encontrado ou sem email');
            return false;
        }

        // Configurar headers
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
            'Reply-To: ' . $this->fromEmail,
            'X-Mailer: PHP/' . phpversion(),
        ];

        // Enviar email
        $success = mail(
            $user['email'],
            $notification['subject'],
            $notification['body'],
            implode("\r\n", $headers)
        );

        if ($success) {
            $this->emailModel->markAsSent($notificationId);
        } else {
            $this->emailModel->markAsFailed($notificationId, 'Falha ao enviar email via mail()');
        }

        return $success;
    }
}
