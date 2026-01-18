<?php

namespace App\Models;

use PDO;

class NotificationRecipient extends BaseModel
{
    protected string $table = 'notification_recipients';

    protected array $selectColumns = [
        'id',
        'notification_id',
        'user_id',
        'read_at',
        'created_at'
    ];

    protected array $fillable = [
        'notification_id',
        'user_id',
        'read_at',
        'created_at'
    ];

    /**
     * Mark notification as read for user
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET read_at = NOW() 
                WHERE notification_id = ? AND user_id = ? AND read_at IS NULL";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$notificationId, $userId]);
    }

    /**
     * Check if user has read notification
     */
    public function hasRead(int $notificationId, int $userId): bool
    {
        $sql = "SELECT read_at FROM {$this->table} 
                WHERE notification_id = ? AND user_id = ? LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$notificationId, $userId]);
        $result = $stmt->fetch();

        return $result && $result['read_at'] !== null;
    }

    /**
     * Get recipients for a notification
     */
    public function getRecipients(int $notificationId): array
    {
        $sql = "SELECT nr.*, u.name, u.email 
                FROM {$this->table} nr
                INNER JOIN users u ON u.id = nr.user_id
                WHERE nr.notification_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$notificationId]);
        return $stmt->fetchAll();
    }

    /**
     * Batch insert recipients
     */
    public function batchInsert(int $notificationId, array $userIds): bool
    {
        if (empty($userIds)) {
            return false;
        }

        $values = [];
        $params = [];

        foreach ($userIds as $userId) {
            $values[] = '(?, ?)';
            $params[] = $notificationId;
            $params[] = $userId;
        }

        $sql = "INSERT INTO {$this->table} (notification_id, user_id) 
                VALUES " . implode(', ', $values);

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            // Handle duplicate entries silently
            return false;
        }
    }
}
