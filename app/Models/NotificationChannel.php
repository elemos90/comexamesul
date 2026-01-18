<?php

namespace App\Models;

use PDO;

class NotificationChannel extends BaseModel
{
    protected string $table = 'notification_channels';

    protected array $selectColumns = [
        'id',
        'notification_id',
        'channel',
        'status',
        'sent_at',
        'error_message'
    ];

    protected array $fillable = [
        'notification_id',
        'channel',
        'status',
        'sent_at',
        'error_message'
    ];

    /**
     * Add channels for a notification
     */
    public function addChannels(int $notificationId, array $channels): bool
    {
        if (empty($channels)) {
            return false;
        }

        $values = [];
        $params = [];

        foreach ($channels as $channel) {
            $values[] = '(?, ?)';
            $params[] = $notificationId;
            $params[] = $channel;
        }

        $sql = "INSERT INTO {$this->table} (notification_id, channel) 
                VALUES " . implode(', ', $values);

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Mark channel as sent
     */
    public function markAsSent(int $channelId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'sent', sent_at = NOW() 
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$channelId]);
    }

    /**
     * Mark channel as failed
     */
    public function markAsFailed(int $channelId, string $errorMessage): bool
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'failed', error_message = ? 
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$errorMessage, $channelId]);
    }

    /**
     * Get pending channels
     */
    public function getPending(int $limit = 10): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'pending' 
                ORDER BY id ASC 
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get channels for notification
     */
    public function getForNotification(int $notificationId): array
    {
        return $this->all(['notification_id' => $notificationId]);
    }
}
