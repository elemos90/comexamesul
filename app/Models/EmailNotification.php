<?php

namespace App\Models;

class EmailNotification extends BaseModel
{
    protected string $table = 'email_notifications';
    
    protected array $fillable = [
        'user_id',
        'type',
        'subject',
        'body',
        'status',
        'sent_at',
        'error_message',
        'retry_count',
        'created_at',
    ];

    /**
     * Criar nova notificação de email
     */
    public function queue(int $userId, string $type, string $subject, string $body): int
    {
        $data = [
            'user_id' => $userId,
            'type' => $type,
            'subject' => $subject,
            'body' => $body,
            'status' => 'pending',
            'created_at' => now(),
        ];
        
        return $this->create($data);
    }

    /**
     * Buscar emails pendentes de envio
     */
    public function getPending(int $limit = 50): array
    {
        $sql = "SELECT e.*, u.email as user_email, u.name as user_name
                FROM {$this->table} e
                INNER JOIN users u ON e.user_id = u.id
                WHERE e.status = 'pending'
                  AND e.retry_count < 3
                ORDER BY e.created_at ASC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Marcar email como enviado
     */
    public function markAsSent(int $id): bool
    {
        return $this->update($id, [
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Marcar email como falho
     */
    public function markAsFailed(int $id, string $errorMessage): bool
    {
        $email = $this->find($id);
        if (!$email) {
            return false;
        }

        return $this->update($id, [
            'status' => 'failed',
            'error_message' => $errorMessage,
            'retry_count' => ($email['retry_count'] ?? 0) + 1,
        ]);
    }

    /**
     * Retentar envio de emails falhados
     */
    public function retryFailed(int $limit = 10): array
    {
        // Buscar emails que falharam mas ainda podem ser retentados
        $sql = "SELECT e.*, u.email as user_email, u.name as user_name
                FROM {$this->table} e
                INNER JOIN users u ON e.user_id = u.id
                WHERE e.status = 'failed'
                  AND e.retry_count < 3
                  AND e.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ORDER BY e.created_at ASC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        $emails = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Resetar status para pending para retentar
        foreach ($emails as $email) {
            $this->update($email['id'], ['status' => 'pending']);
        }
        
        return $emails;
    }

    /**
     * Buscar emails por tipo
     */
    public function getByType(string $type, int $limit = 100): array
    {
        $sql = "SELECT e.*, u.email as user_email, u.name as user_name
                FROM {$this->table} e
                INNER JOIN users u ON e.user_id = u.id
                WHERE e.type = :type
                ORDER BY e.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':type', $type, \PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Estatísticas de emails
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                    ROUND(
                        SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) * 100.0 / 
                        NULLIF(COUNT(*), 0), 
                        2
                    ) as success_rate
                FROM {$this->table}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Limpar emails antigos já enviados (mais de 30 dias)
     */
    public function cleanOldSent(): int
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE status = 'sent' 
                  AND sent_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
