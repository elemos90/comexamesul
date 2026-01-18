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
        $userId = self::id();
        if (!$userId) {
            return false;
        }

        // Check in user_roles table for multi-role support
        try {
            $db = database();
            $stmt = $db->prepare("SELECT COUNT(*) FROM user_roles WHERE user_id = ? AND role = ?");
            $stmt->execute([$userId, $role]);
            if ($stmt->fetchColumn() > 0) {
                return true;
            }
        } catch (\Exception $e) {
            // Fallback if table doesn't exist
        }

        // Fallback to session check
        $current = $_SESSION['auth_user_role'] ?? null;
        return $current === $role;
    }

    public static function hasAnyRole(array $roles): bool
    {
        $userId = self::id();
        if (!$userId) {
            return false;
        }

        // Check in user_roles table for multi-role support
        try {
            $db = database();
            $placeholders = implode(',', array_fill(0, count($roles), '?'));
            $stmt = $db->prepare("SELECT COUNT(*) FROM user_roles WHERE user_id = ? AND role IN ($placeholders)");
            $stmt->execute(array_merge([$userId], $roles));
            if ($stmt->fetchColumn() > 0) {
                return true;
            }
        } catch (\Exception $e) {
            // Fallback if table doesn't exist
        }

        // Fallback to session check
        $current = $_SESSION['auth_user_role'] ?? null;
        return $current && in_array($current, $roles, true);
    }
}