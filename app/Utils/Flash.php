<?php

namespace App\Utils;

class Flash
{
    private const KEY = 'app_flash';

    public static function add(string $type, string $message): void
    {
        $_SESSION[self::KEY][$type][] = $message;
    }

    public static function get(string $type): array
    {
        $messages = $_SESSION[self::KEY][$type] ?? [];
        unset($_SESSION[self::KEY][$type]);
        return $messages;
    }

    public static function all(): array
    {
        $messages = $_SESSION[self::KEY] ?? [];
        unset($_SESSION[self::KEY]);
        return $messages;
    }
    public static function has(string $type): bool
    {
        return !empty($_SESSION[self::KEY][$type]);
    }
}
