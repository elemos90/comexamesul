<?php

use App\Utils\Env;
use App\Utils\Flash;

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);

        if ($path === '') {
            return $base;
        }

        return $base . '/' . ltrim($path, '/\\');
    }
}



if (!function_exists('app_path')) {
    function app_path(string $path = ''): string
    {
        return base_path('app' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('view_path')) {
    function view_path(string $path = ''): string
    {
        return app_path('Views' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return $_SESSION[env('CSRF_TOKEN_KEY', 'csrf_token')] ?? '';
    }
}

if (!function_exists('now')) {
    function now(): string
    {
        return (new \DateTime('now', new \DateTimeZone(env('APP_TIMEZONE', 'UTC'))))->format('Y-m-d H:i:s');
    }
}

if (!function_exists('old')) {
    function old(string $key, $default = '')
    {
        return $_SESSION['old'][$key] ?? $default;
    }
}

if (!function_exists('old_clear')) {
    function old_clear(): void
    {
        unset($_SESSION['old']);
    }
}

if (!function_exists('validation_errors')) {
    function validation_errors(string $field = null): array
    {
        $errors = $_SESSION['errors'] ?? [];
        if ($field === null) {
            return $errors;
        }
        return $errors[$field] ?? [];
    }
}

if (!function_exists('validation_errors_clear')) {
    function validation_errors_clear(): void
    {
        unset($_SESSION['errors']);
    }
}

if (!function_exists('flash_messages')) {
    function flash_messages(): array
    {
        return Flash::all();
    }
}

if (!function_exists('database')) {
    function database(): PDO
    {
        return \App\Database\Connection::getInstance();
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML para prevenir XSS
     * 
     * @param string|null $value
     * @return string
     */
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('e_nl')) {
    /**
     * Escape HTML e preserva quebras de linha
     * 
     * @param string|null $value
     * @return string
     */
    function e_nl(?string $value): string
    {
        return nl2br(e($value));
    }
}
