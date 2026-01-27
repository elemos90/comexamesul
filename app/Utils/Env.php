<?php

namespace App\Utils;

class Env
{
    private static array $data = [];

    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }

            // Split only on first =
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = trim($parts[0]);
            $value = trim($parts[1]);

            // Remove quotes if present
            if (strlen($value) >= 2) {
                if (
                    ($value[0] === '"' && substr($value, -1) === '"') ||
                    ($value[0] === "'" && substr($value, -1) === "'")
                ) {
                    $value = substr($value, 1, -1);
                }
            }

            self::$data[$key] = $value;
            $_SERVER[$key] = $value;
            $_ENV[$key] = $value;
        }
    }

    public static function get(string $key, $default = null)
    {
        return self::$data[$key] ?? $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        self::$data[$key] = $value;
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    private static function sanitizeValue(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        if (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === '\'' && str_ends_with($value, '\''))) {
            $value = substr($value, 1, -1);
        }
        return $value;
    }
}

