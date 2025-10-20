<?php

namespace App\Utils;

use App\Models\User;

class Auth
{
    private const SESSION_KEY = 'auth_user_id';

    public static function attempt(string $email, string $password): bool
    {
        $userModel = new User();
        $user = $userModel->findByEmail($email);
        if (!$user) {
            return false;
        }
        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }
        self::loginUser($user);
        return true;
    }

    public static function loginUser(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = $user['id'];
        $_SESSION['auth_user_role'] = $user['role'];
    }

    public static function user(): ?array
    {
        $userId = $_SESSION[self::SESSION_KEY] ?? null;
        if (!$userId) {
            return null;
        }
        $userModel = new User();
        return $userModel->find((int) $userId);
    }

    public static function id(): ?int
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]);
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY], $_SESSION['auth_user_role']);
        session_regenerate_id(true);
    }

    public static function hasRole(string $role): bool
    {
        $current = $_SESSION['auth_user_role'] ?? null;
        return $current === $role;
    }

    public static function hasAnyRole(array $roles): bool
    {
        $current = $_SESSION['auth_user_role'] ?? null;
        return $current && in_array($current, $roles, true);
    }
}