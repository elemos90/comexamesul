<?php

namespace App\Services;

use App\Models\VacancyApplication;
use App\Models\User;
use App\Models\ExamVacancy;
use App\Services\NotificationService;

/**
 * Email Notification Service
 * Handles email template and sending
 */
class EmailNotificationService
{
    /**
     * Notify coordinators about a new application
     */
    public function notifyNewApplication(int $applicationId): void
    {
        // 1. Fetch application details
        $appModel = new VacancyApplication();
        $application = $appModel->find($applicationId);

        if (!$application) {
            return;
        }

        // 2. Fetch related data
        $vacancyModel = new ExamVacancy();
        $vacancy = $vacancyModel->find($application['vacancy_id']);

        $userModel = new User();
        $vigilante = $userModel->find($application['vigilante_id']);

        if (!$vacancy || !$vigilante) {
            return;
        }

        // 3. Find coordinators
        $coordinators = $userModel->statement(
            "SELECT u.* FROM users u 
             INNER JOIN user_roles ur ON ur.user_id = u.id 
             WHERE ur.role = 'coordenador' AND u.is_active = 1"
        );

        // 4. Send email to each coordinator
        foreach ($coordinators as $coord) {
            $notification = [
                'type' => 'alerta',
                'subject' => 'Nova Candidatura: ' . $vacancy['title'],
                'message' => "Uma nova candidatura foi recebida.\n\n" .
                    "Vigilante: {$vigilante['name']}\n" .
                    "Vaga: {$vacancy['title']}\n" .
                    "Data: " . date('d/m/Y H:i') . "\n\n" .
                    "Aceda ao portal para analisar a candidatura."
            ];

            $this->send($coord, $notification);
        }
    }

    /**
     * Notify vigilante about approved application
     */
    public function notifyApplicationApproved(int $applicationId): void
    {
        $appModel = new VacancyApplication();
        $application = $appModel->find($applicationId);

        if (!$application)
            return;

        $userModel = new User();
        $vigilante = $userModel->find($application['vigilante_id']);

        $vacancyModel = new ExamVacancy();
        $vacancy = $vacancyModel->find($application['vacancy_id']);

        if (!$vigilante || !$vacancy)
            return;

        $notification = [
            'type' => 'alerta', // Green/Positive usually, but using standard types
            'subject' => 'Candidatura Aprovada: ' . $vacancy['title'],
            'message' => "Parabéns, {$vigilante['name']}!\n\n" .
                "Sua candidatura para a vaga '{$vacancy['title']}' foi APROVADA.\n\n" .
                "Fique atento às próximas instruções para alocação de júris."
        ];

        $this->send($vigilante, $notification);

        // Criar notificação no sistema
        $notificationService = new NotificationService();
        $notificationService->createAutomaticNotification(
            'alerta',
            'Candidatura Aprovada: ' . $vacancy['title'],
            "Parabéns! Sua candidatura para a vaga '{$vacancy['title']}' foi aprovada.",
            'vacancy_application',
            $applicationId,
            [$vigilante['id']]
        );
    }

    /**
     * Notify vigilante about rejected application
     */
    public function notifyApplicationRejected(int $applicationId, string $reason): void
    {
        $appModel = new VacancyApplication();
        $application = $appModel->find($applicationId);

        if (!$application)
            return;

        $userModel = new User();
        $vigilante = $userModel->find($application['vigilante_id']);

        $vacancyModel = new ExamVacancy();
        $vacancy = $vacancyModel->find($application['vacancy_id']);

        if (!$vigilante || !$vacancy)
            return;

        $notification = [
            'type' => 'urgente', // Red for rejection
            'subject' => 'Candidatura Não Aceite: ' . $vacancy['title'],
            'message' => "Olá, {$vigilante['name']}.\n\n" .
                "Informamos que sua candidatura para a vaga '{$vacancy['title']}' não foi aceita.\n\n" .
                "Motivo: {$reason}\n\n" .
                "Você pode verificar mais detalhes no portal."
        ];

        $this->send($vigilante, $notification);

        // Criar notificação no sistema
        $notificationService = new NotificationService();
        $notificationService->createAutomaticNotification(
            'urgente',
            'Candidatura Não Aceite: ' . $vacancy['title'],
            "Sua candidatura para a vaga '{$vacancy['title']}' não foi aceita. Motivo: {$reason}",
            'vacancy_application',
            $applicationId,
            [$vigilante['id']]
        );
    }

    /**
     * Send notification email
     */
    public function send(array $recipient, array $notification): bool
    {
        $to = $recipient['email'];
        $subject = $this->getEmailSubject($notification);
        $body = $this->buildEmailBody($recipient, $notification);
        $headers = $this->getHeaders();

        return mail($to, $subject, $body, $headers);
    }

    /**
     * Get email subject with type prefix
     */
    private function getEmailSubject(array $notification): string
    {
        $prefix = match ($notification['type']) {
            'urgente' => '🔴 URGENTE',
            'alerta' => '⚠️ ALERTA',
            default => 'ℹ️'
        };

        return "{$prefix} - {$notification['subject']} - Portal COMEXAMES";
    }

    /**
     * Build HTML email body
     */
    private function buildEmailBody(array $recipient, array $notification): string
    {
        $typeColor = match ($notification['type']) {
            'urgente' => '#DC2626',
            'alerta' => '#F59E0B',
            default => '#3B82F6'
        };

        $typeName = match ($notification['type']) {
            'urgente' => 'Urgente',
            'alerta' => 'Alerta',
            default => 'Informativa'
        };

        $systemUrl = url('/notifications');
        $name = htmlspecialchars($recipient['name']);
        $subject = htmlspecialchars($notification['subject']);
        $message = nl2br(htmlspecialchars($notification['message']));

        $html = <<<HTML
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subject}</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f3f4f6;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #1F2937; padding: 30px 40px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px;">Portal COMEXAMES</h1>
                            <p style="margin: 5px 0 0 0; color: #9CA3AF; font-size: 14px;">Comissão de Exames de Admissão</p>
                        </td>
                    </tr>

                    <!-- Type Badge -->
                    <tr>
                        <td style="padding: 20px 40px 0 40px;">
                            <div style="display: inline-block; background-color: {$typeColor}; color: white; padding: 8px 16px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase;">
                                {$typeName}
                            </div>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 20px 40px;">
                            <p style="margin: 0 0 10px 0; color: #374151; font-size: 14px;">Olá, <strong>{$name}</strong></p>
                            
                            <h2 style="margin: 20px 0 15px 0; color: #111827; font-size: 20px; line-height: 1.4;">
                                {$subject}
                            </h2>
                            
                            <div style="color: #4B5563; font-size: 14px; line-height: 1.6; margin-bottom: 25px;">
                                {$message}
                            </div>
                        </td>
                    </tr>

                    <!-- Call to Action -->
                    <tr>
                        <td style="padding: 0 40px 30px 40px;">
                            <a href="{$systemUrl}" style="display: inline-block; background-color: #4F46E5; color: white; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: bold; font-size: 14px;">
                                Aceder ao Sistema
                            </a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #F9FAFB; padding: 20px 40px; border-top: 1px solid #E5E7EB;">
                            <p style="margin: 0; color: #6B7280; font-size: 12px; line-height: 1.5;">
                                Esta é uma notificação oficial da Comissão de Exames de Admissão.<br>
                                Por favor, não responda a este email. Aceda ao sistema para mais informações.
                            </p>
                            <p style="margin: 10px 0 0 0; color: #9CA3AF; font-size: 11px;">
                                © 2026 Portal COMEXAMES. Todos os direitos reservados.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Get email headers
     */
    private function getHeaders(): string
    {
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = 'From: Portal COMEXAMES <noreply@comexamesul.ac.mz>';
        $headers[] = 'Reply-To: comissao@licungo.ac.mz';
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        return implode("\r\n", $headers);
    }
}
