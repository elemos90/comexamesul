<?php

namespace App\Models;

use PDO;

class Notification extends BaseModel
{
    protected string $table = 'notifications';

    protected array $selectColumns = [
        'id',
        'type',
        'subject',
        'message',
        'context_type',
        'context_id',
        'is_automatic',
        'created_by',
        'created_at'
    ];

    protected array $fillable = [
        'type',
        'subject',
        'message',
        'context_type',
        'context_id',
        'is_automatic',
        'created_by',
        'created_at'
    ];

    /**
     * Get notification with creator info
     */
    public function findWithCreator(int $id): ?array
    {
        $sql = "SELECT n.*, u.name as creator_name 
                FROM {$this->table} n
                LEFT JOIN users u ON u.id = n.created_by
                WHERE n.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Get all notifications with recipient count
     */
    public function getAllWithStats(): array
    {
        $sql = "SELECT n.*, 
                       u.name as creator_name,
                       COUNT(DISTINCT nr.user_id) as total_recipients,
                       COUNT(DISTINCT CASE WHEN nr.read_at IS NOT NULL THEN nr.user_id END) as read_count
                FROM {$this->table} n
                LEFT JOIN users u ON u.id = n.created_by
                LEFT JOIN notification_recipients nr ON nr.notification_id = n.id
                GROUP BY n.id
                ORDER BY n.created_at DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get notifications for a specific user
     */
    public function getForUser(int $userId, bool $onlyUnread = false): array
    {
        $unreadClause = $onlyUnread ? 'AND nr.read_at IS NULL' : '';

        $sql = "SELECT n.*, 
                       nr.read_at,
                       u.name as creator_name
                FROM {$this->table} n
                INNER JOIN notification_recipients nr ON nr.notification_id = n.id
                LEFT JOIN users u ON u.id = n.created_by
                WHERE nr.user_id = ? {$unreadClause}
                ORDER BY n.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) 
                FROM notification_recipients 
                WHERE user_id = ? AND read_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Search notifications with filters
     */
    public function search(array $filters): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['type'])) {
            $where[] = 'n.type = ?';
            $params[] = $filters['type'];
        }

        if (!empty($filters['context_type'])) {
            $where[] = 'n.context_type = ?';
            $params[] = $filters['context_type'];
        }

        if (!empty($filters['created_by'])) {
            $where[] = 'n.created_by = ?';
            $params[] = $filters['created_by'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'n.created_at >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'n.created_at <= ?';
            $params[] = $filters['date_to'];
        }

        $sql = "SELECT n.*, 
                       u.name as creator_name,
                       COUNT(DISTINCT nr.user_id) as total_recipients
                FROM {$this->table} n
                LEFT JOIN users u ON u.id = n.created_by
                LEFT JOIN notification_recipients nr ON nr.notification_id = n.id
                WHERE " . implode(' AND ', $where) . "
                GROUP BY n.id
                ORDER BY n.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
