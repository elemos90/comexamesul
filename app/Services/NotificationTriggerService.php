<?php

namespace App\Services;

/**
 * Notification Trigger Service
 * Automatic notifications for system events
 */
class NotificationTriggerService
{
    private static NotificationService $notificationService;

    private static function getService(): NotificationService
    {
        if (!isset(self::$notificationService)) {
            self::$notificationService = new NotificationService();
        }
        return self::$notificationService;
    }

    /**
     * Trigger: Jury assigned to vigilante
     */
    public static function juryAssigned(int $juryId, int $vigilanteId): void
    {
        try {
            $db = database();

            // Get jury details
            $stmt = $db->prepare("
                SELECT j.*, er.discipline, er.exam_date, er.exam_time, l.name as location_name
                FROM juries j
                LEFT JOIN exam_reports er ON er.id = j.exam_report_id
                LEFT JOIN locations l ON l.id = j.location_id
                WHERE j.id = ?
            ");
            $stmt->execute([$juryId]);
            $jury = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$jury) {
                return;
            }

            $date = date('d/m/Y', strtotime($jury['exam_date']));
            $time = substr($jury['exam_time'], 0, 5);

            $subject = "Júri de Exame Atribuído";
            $message = "Foi-lhe atribuído um júri de fiscalização.\n\n";
            $message .= "Disciplina: {$jury['discipline']}\n";
            $message .= "Data: {$date}\n";
            $message .= "Hora: {$time}\n";
            $message .= "Local: {$jury['location_name']}\n";
            $message .= "Sala: {$jury['room']}\n\n";
            $message .= "Por favor, aceda ao sistema para mais detalhes.";

            self::getService()->createAutomaticNotification(
                'informativa',
                $subject,
                $message,
                'jury',
                $juryId,
                [$vigilanteId]
            );

        } catch (\Exception $e) {
            error_log('NotificationTrigger::juryAssigned - ' . $e->getMessage());
        }
    }

    /**
     * Trigger: User promoted to new role
     */
    public static function userPromoted(int $userId, string $newRole): void
    {
        try {
            $roleNames = [
                'supervisor' => 'Supervisor',
                'membro' => 'Membro da Comissão',
                'coordenador' => 'Coordenador'
            ];

            $roleName = $roleNames[$newRole] ?? $newRole;

            $subject = "Promoção de Papel";
            $message = "Parabéns! Foi promovido ao papel de {$roleName}.\n\n";
            $message .= "Esta promoção concede-lhe novas responsabilidades e permissões no sistema.\n\n";
            $message .= "Aceda ao sistema para explorar as novas funcionalidades disponíveis.";

            self::getService()->createAutomaticNotification(
                'informativa',
                $subject,
                $message,
                'user',
                $userId,
                [$userId]
            );

        } catch (\Exception $e) {
            error_log('NotificationTrigger::userPromoted - ' . $e->getMessage());
        }
    }

    /**
     * Trigger: Account deactivated
     */
    public static function accountDeactivated(int $userId, ?string $reason = null): void
    {
        try {
            $subject = "Conta Desativada";
            $message = "A sua conta no Portal COMEXAMES foi desativada.\n\n";

            if ($reason) {
                $message .= "Motivo: {$reason}\n\n";
            }

            $message .= "Se tiver dúvidas, entre em contacto com a Comissão de Exames.";

            self::getService()->createAutomaticNotification(
                'alerta',
                $subject,
                $message,
                'user',
                $userId,
                [$userId]
            );

        } catch (\Exception $e) {
            error_log('NotificationTrigger::accountDeactivated - ' . $e->getMessage());
        }
    }

    /**
     * Trigger: Account reactivated
     */
    public static function accountReactivated(int $userId): void
    {
        try {
            $subject = "Conta Reativada";
            $message = "A sua conta no Portal COMEXAMES foi reativada.\n\n";
            $message .= "Já pode aceder ao sistema normalmente.";

            self::getService()->createAutomaticNotification(
                'informativa',
                $subject,
                $message,
                'user',
                $userId,
                [$userId]
            );

        } catch (\Exception $e) {
            error_log('NotificationTrigger::accountReactivated - ' . $e->getMessage());
        }
    }

    /**
     * Trigger: Report available for submission
     */
    public static function reportAvailable(int $juryId, int $vigilanteId): void
    {
        try {
            $db = database();

            $stmt = $db->prepare("
                SELECT j.*, er.discipline 
                FROM juries j
                LEFT JOIN exam_reports er ON er.id = j.exam_report_id
                WHERE j.id = ?
            ");
            $stmt->execute([$juryId]);
            $jury = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$jury) {
                return;
            }

            $subject = "Relatório Disponível para Submissão";
            $message = "O relatório do júri de {$jury['discipline']} está disponível para submissão.\n\n";
            $message .= "Por favor, preencha e submeta o relatório o mais breve possível.";

            self::getService()->createAutomaticNotification(
                'alerta',
                $subject,
                $message,
                'jury',
                $juryId,
                [$vigilanteId]
            );

        } catch (\Exception $e) {
            error_log('NotificationTrigger::reportAvailable - ' . $e->getMessage());
        }
    }

    /**
     * Trigger: Report rejected with feedback
     */
    public static function reportRejected(int $reportId, int $vigilanteId, string $feedback): void
    {
        try {
            $subject = "Relatório Rejeitado";
            $message = "O seu relatório foi rejeitado e necessita de correções.\n\n";
            $message .= "Feedback:\n{$feedback}\n\n";
            $message .= "Por favor, corrija e resubmeta o relatório.";

            self::getService()->createAutomaticNotification(
                'urgente',
                $subject,
                $message,
                'report',
                $reportId,
                [$vigilanteId]
            );

        } catch (\Exception $e) {
            error_log('NotificationTrigger::reportRejected - ' . $e->getMessage());
        }
    }

    /**
     * Trigger: Planning validated
     */
    public static function planningValidated(int $vacancyId): void
    {
        try {
            $db = database();

            // Get all vigilantes and supervisors assigned to this vacancy
            $stmt = $db->prepare("
                SELECT DISTINCT jv.user_id 
                FROM jury_vigilantes jv
                INNER JOIN juries j ON j.id = jv.jury_id
                WHERE j.vacancy_id = ?
            ");
            $stmt->execute([$vacancyId]);
            $vigilantes = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (empty($vigilantes)) {
                return;
            }

            $subject = "Planeamento de Júris Validado";
            $message = "O planeamento de júris para a próxima vaga foi validado e publicado.\n\n";
            $message .= "As suas atribuições já estão disponíveis no sistema.\n\n";
            $message .= "Aceda ao sistema para consultar os seus júris.";

            self::getService()->createAutomaticNotification(
                'informativa',
                $subject,
                $message,
                'exam',
                $vacancyId,
                $vigilantes
            );

        } catch (\Exception $e) {
            error_log('NotificationTrigger::planningValidated - ' . $e->getMessage());
        }
    }

    /**
     * Trigger: Payment map generated
     */
    public static function paymentMapGenerated(int $vacancyId): void
    {
        try {
            $db = database();

            // Get vigilantes with payments
            $stmt = $db->prepare("
                SELECT DISTINCT p.user_id 
                FROM payments p
                WHERE p.vacancy_id = ? AND p.validated = 1
            ");
            $stmt->execute([$vacancyId]);
            $recipients = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (empty($recipients)) {
                return;
            }

            $subject = "Mapa de Pagamento Disponível";
            $message = "O mapa de pagamento da vaga foi gerado e está disponível para consulta.\n\n";
            $message .= "Aceda ao sistema para visualizar o seu mapa de pagamento individual.";

            self::getService()->createAutomaticNotification(
                'informativa',
                $subject,
                $message,
                'payment',
                $vacancyId,
                $recipients
            );

        } catch (\Exception $e) {
            error_log('NotificationTrigger::paymentMapGenerated - ' . $e->getMessage());
        }
    }
}
