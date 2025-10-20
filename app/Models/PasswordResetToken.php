<?php

namespace App\Models;

class PasswordResetToken extends BaseModel
{
    protected string $table = 'password_resets';
    
    protected array $selectColumns = [
        'id', 'email', 'token', 'expires_at', 'created_at'
    ];
    
    protected array $fillable = [
        'email',
        'token',
        'expires_at',
        'created_at',
    ];

    public function findValidToken(string $email, string $token): ?array
    {
        $columns = $this->getSelectColumns();
        $stmt = $this->db->prepare("SELECT {$columns} FROM {$this->table} WHERE email = :email AND token = :token AND expires_at > :now LIMIT 1");
        $stmt->execute([
            'email' => $email,
            'token' => $token,
            'now' => now(),
        ]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function deleteToken(string $email): void
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE email = :email");
        $stmt->execute(['email' => $email]);
    }
}
