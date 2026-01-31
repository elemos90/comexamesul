<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\NotificationChannel;
use App\Models\User;

/**
 * Core Notification Service
 * Handles creation and management of notifications
 */
class NotificationService
{
    private Notification $notificationModel;
    private NotificationRecipient $recipientModel;
    private NotificationChannel $channelModel;
    private User $userModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
        $this->recipientModel = new NotificationRecipient();
        $this->channelModel = new NotificationChannel();
        $this->userModel = new User();
    }

    /**
     * Create manual notification from wizard
     */
    public function createManualNotification(array $data, int $createdBy): array
    {
        try {
            // Validate required fields
            if (empty($data['type']) || empty($data['subject']) || empty($data['message'])) {
                return ['success' => false, 'message' => 'Campos obrigatórios em falta'];
            }

            if (empty($data['recipients']) || !is_array($data['recipients'])) {
                return ['success' => false, 'message' => 'Selecione pelo menos um destinatário'];
            }

            if (empty($data['channels']) || !is_array($data['channels'])) {
                return ['success' => false, 'message' => 'Selecione pelo menos um canal'];
            }

            // Create notification
            $notificationId = $this->notificationModel->create([
                'type' => $data['type'],
                'subject' => $data['subject'],
                'message' => $data['message'],
                'context_type' => $data['context_type'] ?? 'general',
                'context_id' => $data['context_id'] ?? null,
                'is_automatic' => false,
                'created_by' => $createdBy,
                'created_at' => now()
            ]);

            // Add recipients
            $this->recipientModel->batchInsert($notificationId, $data['recipients']);

            // Add channels
            $this->channelModel->addChannels($notificationId, $data['channels']);

            // Dispatch to channels
            $dispatcher = new NotificationDispatcherService();
            $dispatcher->dispatch($notificationId, $data['channels']);

            return [
                'success' => true,
                'message' => 'Notificação criada e enviada com sucesso',
                'notification_id' => $notificationId
            ];

        } catch (\Exception $e) {
            error_log('NotificationService::createManualNotification - ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao criar notificação: ' . $e->getMessage()];
        }
    }

    /**
     * Create automatic notification (system trigger)
     */
    public function createAutomaticNotification(
        string $type,
        string $subject,
        string $message,
        string $contextType,
        ?int $contextId,
        array $recipientIds
    ): array {
        try {
            if (empty($recipientIds)) {
                return ['success' => false, 'message' => 'No recipients specified'];
            }

            // Create notification
            $notificationId = $this->notificationModel->create([
                'type' => $type,
                'subject' => $subject,
                'message' => $message,
                'context_type' => $contextType,
                'context_id' => $contextId,
                'is_automatic' => true,
                'created_by' => null, // System-generated
                'created_at' => now()
            ]);

            // Add recipients
            $this->recipientModel->batchInsert($notificationId, $recipientIds);

            // Always use internal + email for automatic notifications
            $channels = ['internal', 'email'];
            $this->channelModel->addChannels($notificationId, $channels);

            // Dispatch
            $dispatcher = new NotificationDispatcherService();
            $dispatcher->dispatch($notificationId, $channels);

            return ['success' => true, 'notification_id' => $notificationId];

        } catch (\Exception $e) {
            error_log('NotificationService::createAutomaticNotification - ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get notifications for a specific user
     */
    public function getNotificationsForUser(int $userId, bool $onlyUnread = false, int $page = 1, int $perPage = 50): array
    {
        return $this->notificationModel->getForUser($userId, $onlyUnread, $page, $perPage);
    }

    /**
     * Get total count of user notifications
     */
    public function getTotalCount(int $userId, bool $onlyUnread = false): int
    {
        return $this->notificationModel->getUserNotificationCount($userId, $onlyUnread);
    }

    /**
     * Mark notification as read for a user
     */
    public function markAsRead(int $notificationId, int $userId): array
    {
        try {
            $result = $this->recipientModel->markAsRead($notificationId, $userId);

            if ($result) {
                return ['success' => true, 'message' => 'Marcada como lida'];
            }

            return ['success' => false, 'message' => 'Notificação já foi lida'];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Delete notifications for a user
     */
    public function deleteNotifications(int $userId, array $notificationIds): array
    {
        try {
            $result = $this->recipientModel->deleteForUser($userId, $notificationIds);

            if ($result) {
                return ['success' => true, 'message' => 'Notificações removidas'];
            }

            return ['success' => false, 'message' => 'Erro ao remover notificações'];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->notificationModel->getUnreadCount($userId);
    }

    /**
     * Get notification history with filters
     */
    public function getNotificationHistory(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        if (empty($filters)) {
            return $this->notificationModel->getAllWithStats($page, $perPage);
        }

        return $this->notificationModel->search($filters, $page, $perPage);
    }

    /**
     * Get total count for history/search
     */
    public function getHistoryCount(array $filters = []): int
    {
        if (empty($filters)) {
            return $this->notificationModel->getTotalCount();
        }

        return $this->notificationModel->getSearchCount($filters);
    }

    /**
     * Get recipients by group selection
     */
    public function getRecipientsByGroup(string $group, ?int $resourceId = null): array
    {
        $userIds = [];

        switch ($group) {
            case 'all_vigilantes':
                $users = $this->userModel->statement(
                    "SELECT DISTINCT u.id FROM users u 
                     INNER JOIN user_roles ur ON ur.user_id = u.id 
                     WHERE ur.role = 'vigilante' AND u.is_active = 1"
                );
                $userIds = array_column($users, 'id');
                break;

            case 'supervisors':
                $users = $this->userModel->statement(
                    "SELECT DISTINCT u.id FROM users u 
                     INNER JOIN user_roles ur ON ur.user_id = u.id 
                     WHERE ur.role = 'supervisor' AND u.is_active = 1"
                );
                $userIds = array_column($users, 'id');
                break;

            case 'committee_members':
                $users = $this->userModel->statement(
                    "SELECT DISTINCT u.id FROM users u 
                     INNER JOIN user_roles ur ON ur.user_id = u.id 
                     WHERE ur.role IN ('membro', 'coordenador') AND u.is_active = 1"
                );
                $userIds = array_column($users, 'id');
                break;

            case 'exam_vigilantes':
                if ($resourceId) {
                    // Get vigilantes assigned to juries of a specific exam/vacancy
                    $users = $this->userModel->statement(
                        "SELECT DISTINCT jv.user_id as id 
                         FROM jury_vigilantes jv
                         INNER JOIN juries j ON j.id = jv.jury_id
                         WHERE j.vacancy_id = ?",
                        [$resourceId]
                    );
                    $userIds = array_column($users, 'id');
                }
                break;
        }

        return $userIds;
    }

    /**
     * Get notification detail with all data
     */
    public function getNotificationDetail(int $notificationId): ?array
    {
        $notification = $this->notificationModel->findWithCreator($notificationId);

        if (!$notification) {
            return null;
        }

        // Add recipients
        $notification['recipients'] = $this->recipientModel->getRecipients($notificationId);

        // Add channels
        $notification['channels'] = $this->channelModel->getForNotification($notificationId);

        return $notification;
    }
}
