<?php

namespace App\Services;

use App\Models\PasswordResetToken;

class PasswordResetService
{
    private PasswordResetToken $tokens;

    public function __construct()
    {
        $this->tokens = new PasswordResetToken();
    }

    public function createToken(string $email): string
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $this->tokens->deleteToken($email);
        $this->tokens->create([
            'email' => $email,
            'token' => $token,
            'expires_at' => $expires,
            'created_at' => now(),
        ]);
        return $token;
    }

    public function validateToken(string $email, string $token): bool
    {
        return (bool) $this->tokens->findValidToken($email, $token);
    }

    public function consumeToken(string $email): void
    {
        $this->tokens->deleteToken($email);
    }
}
