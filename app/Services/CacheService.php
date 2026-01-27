<?php

declare(strict_types=1);

namespace App\Services;

use Predis\Client;
use Predis\Connection\ConnectionException;

/**
 * Cache Service using Redis
 * 
 * Provides caching functionality with automatic fallback to file-based cache
 * if Redis is not available.
 * 
 * @package App\Services
 */
class CacheService
{
    private static ?Client $redis = null;
    private static bool $redisAvailable = true;
    private static string $fileCache = '';

    /**
     * Get Redis client instance
     * 
     * @return Client|null
     */
    public static function getRedis(): ?Client
    {
        if (!self::$redisAvailable) {
            return null;
        }

        if (self::$redis === null) {
            try {
                self::$redis = new Client([
                    'scheme' => env('REDIS_SCHEME', 'tcp'),
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'port' => (int) env('REDIS_PORT', 6379),
                    'password' => env('REDIS_PASSWORD', null),
                    'database' => (int) env('REDIS_DATABASE', 0),
                    'timeout' => 2.0,
                ]);

                // Test connection
                self::$redis->ping();

                Logger::debug('Redis connection established');
            } catch (ConnectionException $e) {
                self::$redisAvailable = false;
                Logger::warning('Redis not available, using file cache', [
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        }

        return self::$redis;
    }

    /**
     * Initialize file cache directory
     * 
     * @return string
     */
    private static function getFileCachePath(): string
    {
        if (self::$fileCache === '') {
            self::$fileCache = defined('BASE_PATH')
                ? BASE_PATH . '/storage/cache'
                : __DIR__ . '/../../storage/cache';

            if (!is_dir(self::$fileCache)) {
                @mkdir(self::$fileCache, 0755, true);
            }
        }

        return self::$fileCache;
    }

    /**
     * Get cached value or execute callback
     * 
     * @param string $key Cache key
     * @param int $ttl Time to live in seconds
     * @param callable $callback Function to execute if cache miss
     * @return mixed
     */
    public static function remember(string $key, int $ttl, callable $callback)
    {
        $value = self::get($key);

        if ($value !== null) {
            Logger::debug('Cache HIT', ['key' => $key]);
            return $value;
        }

        Logger::debug('Cache MISS', ['key' => $key]);
        $value = $callback();
        self::put($key, $value, $ttl);

        return $value;
    }

    /**
     * Get value from cache
     * 
     * @param string $key
     * @return mixed|null
     */
    public static function get(string $key)
    {
        $redis = self::getRedis();

        if ($redis !== null) {
            try {
                $value = $redis->get($key);
                return $value ? json_decode($value, true) : null;
            } catch (\Exception $e) {
                Logger::warning('Redis get failed', ['key' => $key, 'error' => $e->getMessage()]);
            }
        }

        // Fallback to file cache
        return self::getFromFile($key);
    }

    /**
     * Put value in cache
     * 
     * @param string $key
     * @param mixed $value
     * @param int $ttl Time to live in seconds
     * @return bool
     */
    public static function put(string $key, $value, int $ttl = 3600): bool
    {
        $redis = self::getRedis();

        if ($redis !== null) {
            try {
                $redis->setex($key, $ttl, json_encode($value));
                return true;
            } catch (\Exception $e) {
                Logger::warning('Redis put failed', ['key' => $key, 'error' => $e->getMessage()]);
            }
        }

        // Fallback to file cache
        return self::putToFile($key, $value, $ttl);
    }

    /**
     * Check if key exists in cache
     * 
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        $redis = self::getRedis();

        if ($redis !== null) {
            try {
                return (bool) $redis->exists($key);
            } catch (\Exception $e) {
                Logger::warning('Redis exists check failed', ['key' => $key]);
            }
        }

        return self::hasInFile($key);
    }

    /**
     * Delete key from cache
     * 
     * @param string $key
     * @return bool
     */
    public static function forget(string $key): bool
    {
        $redis = self::getRedis();

        if ($redis !== null) {
            try {
                $redis->del([$key]);
                return true;
            } catch (\Exception $e) {
                Logger::warning('Redis delete failed', ['key' => $key]);
            }
        }

        return self::deleteFromFile($key);
    }

    /**
     * Clear all cache
     * 
     * @return bool
     */
    public static function flush(): bool
    {
        $redis = self::getRedis();

        if ($redis !== null) {
            try {
                $redis->flushdb();
                Logger::info('Redis cache flushed');
                return true;
            } catch (\Exception $e) {
                Logger::warning('Redis flush failed');
            }
        }

        return self::flushFileCache();
    }

    /**
     * Increment counter
     * 
     * @param string $key
     * @param int $value
     * @return int New value
     */
    public static function increment(string $key, int $value = 1): int
    {
        $redis = self::getRedis();

        if ($redis !== null) {
            try {
                return (int) $redis->incrby($key, $value);
            } catch (\Exception $e) {
                Logger::warning('Redis increment failed', ['key' => $key]);
            }
        }

        // File-based increment
        $current = (int) self::getFromFile($key) ?: 0;
        $new = $current + $value;
        self::putToFile($key, $new, 3600);
        return $new;
    }

    // ==================== FILE CACHE FALLBACK ====================

    /**
     * Get from file cache
     * 
     * @param string $key
     * @return mixed|null
     */
    private static function getFromFile(string $key)
    {
        $path = self::getFileCachePath() . '/' . md5($key) . '.cache';

        if (!file_exists($path)) {
            return null;
        }

        $data = @file_get_contents($path);
        if ($data === false) {
            return null;
        }

        $cached = json_decode($data, true);
        if (!$cached || !isset($cached['expires'], $cached['value'])) {
            return null;
        }

        if ($cached['expires'] < time()) {
            @unlink($path);
            return null;
        }

        return $cached['value'];
    }

    /**
     * Put to file cache
     * 
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return bool
     */
    private static function putToFile(string $key, $value, int $ttl): bool
    {
        $path = self::getFileCachePath() . '/' . md5($key) . '.cache';

        $data = json_encode([
            'expires' => time() + $ttl,
            'value' => $value,
        ]);

        return @file_put_contents($path, $data, LOCK_EX) !== false;
    }

    /**
     * Check if exists in file cache
     * 
     * @param string $key
     * @return bool
     */
    private static function hasInFile(string $key): bool
    {
        return self::getFromFile($key) !== null;
    }

    /**
     * Delete from file cache
     * 
     * @param string $key
     * @return bool
     */
    private static function deleteFromFile(string $key): bool
    {
        $path = self::getFileCachePath() . '/' . md5($key) . '.cache';
        return file_exists($path) ? @unlink($path) : true;
    }

    /**
     * Flush file cache
     * 
     * @return bool
     */
    private static function flushFileCache(): bool
    {
        $files = glob(self::getFileCachePath() . '/*.cache');
        foreach ($files as $file) {
            @unlink($file);
        }
        Logger::info('File cache flushed');
        return true;
    }
}
