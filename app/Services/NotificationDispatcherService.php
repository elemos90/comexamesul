<?php

namespace App\Services;

use App\Models\NotificationChannel;
use App\Models\NotificationRecipient;
use App\Models\Notification;

/**
 * Notification Dispatcher Service
 * Handles async sending through multiple channels
 */
class NotificationDispatcherService
{
    private NotificationChannel $channelModel;
    private EmailNotificationService $emailService;

    public function __construct()
    {
        $this->channelModel = new NotificationChannel();
        $this->emailService = new EmailNotificationService();
    }

    /**
     * Dispatch notification to selected channels
     */
    public function dispatch(int $notificationId, array $channels): void
    {
        foreach ($channels as $channel) {
            switch ($channel) {
                case 'internal':
                    $this->markInternalAsSent($notificationId);
                    break;

                case 'email':
                    $this->sendEmail($notificationId);
                    break;

                case 'sms':
                    $this->sendSMS($notificationId);
                    break;
            }
        }
    }

    /**
     * Mark internal notification as sent (instant)
     */
    private function markInternalAsSent(int $notificationId): void
    {
        try {
            $channels = $this->channelModel->all([
                'notification_id' => $notificationId,
                'channel' => 'internal'
            ]);

            foreach ($channels as $channel) {
                $this->channelModel->markAsSent($channel['id']);
            }
        } catch (\Exception $e) {
            error_log('Failed to mark internal as sent: ' . $e->getMessage());
        }
    }

    /**
     * Send email notifications
     */
    public function sendEmail(int $notificationId): void
    {
        try {
            $recipients = $this->getEmailRecipients($notificationId);

            if (empty($recipients)) {
                return;
            }

            $notification = $this->getNotificationData($notificationId);

            if (!$notification) {
                return;
            }

            // Get channel ID for status update
            $channels = $this->channelModel->all([
                'notification_id' => $notificationId,
                'channel' => 'email'
            ]);

            foreach ($recipients as $recipient) {
                try {
                    $this->emailService->send($recipient, $notification);
                } catch (\Exception $e) {
                    error_log("Failed to send email to {$recipient['email']}: " . $e->getMessage());
                }
            }

            // Mark as sent
            foreach ($channels as $channel) {
                $this->channelModel->markAsSent($channel['id']);
            }

        } catch (\Exception $e) {
            error_log('Email dispatch failed: ' . $e->getMessage());

            // Mark as failed
            $channels = $this->channelModel->all([
                'notification_id' => $notificationId,
                'channel' => 'email'
            ]);

            foreach ($channels as $channel) {
                $this->channelModel->markAsFailed($channel['id'], $e->getMessage());
            }
        }
    }

    /**
     * Send SMS (structure ready, not implemented)
     */
    public function sendSMS(int $notificationId): void
    {
        // TODO: Implement SMS sending via provider (Twilio, Vonage, etc)
        // For now, mark as failed

        try {
            $channels = $this->channelModel->all([
                'notification_id' => $notificationId,
                'channel' => 'sms'
            ]);

            foreach ($channels as $channel) {
                $this->channelModel->markAsFailed($channel['id'], 'SMS provider not configured');
            }
        } catch (\Exception $e) {
            error_log('SMS marking failed: ' . $e->getMessage());
        }
    }

    /**
     * Process pending notifications in queue
     */
    public function processQueue(int $limit = 10): void
    {
        $pending = $this->channelModel->getPending($limit);

        foreach ($pending as $channel) {
            switch ($channel['channel']) {
                case 'email':
                    $this->sendEmail($channel['notification_id']);
                    break;

                case 'sms':
                    $this->sendSMS($channel['notification_id']);
                    break;
            }
        }
    }

    /**
     * Get email recipients for notification
     */
    private function getEmailRecipients(int $notificationId): array
    {
        $db = database();
        $stmt = $db->prepare("
            SELECT u.id, u.name, u.email 
            FROM notification_recipients nr
            INNER JOIN users u ON u.id = nr.user_id
            WHERE nr.notification_id = ? AND u.email IS NOT NULL
        ");
        $stmt->execute([$notificationId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get notification data
     */
    private function getNotificationData(int $notificationId): ?array
    {
        $notificationModel = new Notification();
        return $notificationModel->find($notificationId);
    }
}
