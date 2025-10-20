<?php

namespace App\Utils;

class RateLimiter
{
    public static function hit(string $key, int $decaySeconds, int $maxAttempts): bool
    {
        $data = self::load($key);
        $now = time();
        if (!$data || $now > ($data['window'] ?? 0) + $decaySeconds) {
            $data = ['attempts' => 0, 'window' => $now];
        }
        $data['attempts'] = ($data['attempts'] ?? 0) + 1;
        $data['window'] = $data['window'] ?? $now;
        self::store($key, $data);
        return $data['attempts'] <= $maxAttempts;
    }

    public static function clear(string $key): void
    {
        $file = self::filePath($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    private static function load(string $key): array
    {
        $file = self::filePath($key);
        if (!file_exists($file)) {
            return [];
        }
        $content = file_get_contents($file);
        if (!$content) {
            return [];
        }
        $data = json_decode($content, true);
        return $data ?: [];
    }

    private static function store(string $key, array $data): void
    {
        $file = self::filePath($key);
        file_put_contents($file, json_encode($data));
    }

    private static function filePath(string $key): string
    {
        $hash = md5($key);
        return storage_path('cache/ratelimit_' . $hash . '.json');
    }
}
