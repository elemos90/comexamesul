<?php

declare(strict_types=1);

namespace App\Services;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;

/**
 * Centralized Logging Service
 * 
 * Provides structured logging using Monolog with multiple handlers
 * for different log levels and rotation policies.
 * 
 * @package App\Services
 */
class Logger
{
    private static ?MonologLogger $instance = null;
    private static string $logPath = '';

    /**
     * Get Logger instance (Singleton)
     * 
     * @return MonologLogger
     */
    public static function get(): MonologLogger
    {
        if (self::$instance === null) {
            self::initialize();
        }

        return self::$instance;
    }

    /**
     * Initialize logger with handlers
     * 
     * @return void
     */
    private static function initialize(): void
    {
        self::$logPath = defined('BASE_PATH')
            ? BASE_PATH . '/storage/logs'
            : __DIR__ . '/../../storage/logs';

        // Ensure log directory exists
        if (!is_dir(self::$logPath)) {
            @mkdir(self::$logPath, 0755, true);
        }

        self::$instance = new MonologLogger('comexames');

        // Custom log format
        $dateFormat = "Y-m-d H:i:s";
        $outputFormat = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
        $formatter = new LineFormatter($outputFormat, $dateFormat, true, true);

        // Handler 1: Rotating file handler for all logs (7 days retention)
        $generalHandler = new RotatingFileHandler(
            self::$logPath . '/app.log',
            7, // days
            Level::Debug
        );
        $generalHandler->setFormatter($formatter);
        self::$instance->pushHandler($generalHandler);

        // Handler 2: Separate error log
        $errorHandler = new StreamHandler(
            self::$logPath . '/errors.log',
            Level::Error
        );
        $errorHandler->setFormatter($formatter);
        self::$instance->pushHandler($errorHandler);

        // Handler 3: Critical errors in separate file
        $criticalHandler = new StreamHandler(
            self::$logPath . '/critical.log',
            Level::Critical
        );
        $criticalHandler->setFormatter($formatter);
        self::$instance->pushHandler($criticalHandler);

        // Handler 4: SQL queries log (if debug mode)
        if (self::isDebugMode()) {
            $sqlHandler = new RotatingFileHandler(
                self::$logPath . '/sql.log',
                3, // days
                Level::Debug
            );
            $sqlHandler->setFormatter($formatter);
            self::$instance->pushHandler($sqlHandler);
        }
    }

    /**
     * Check if application is in debug mode
     * 
     * @return bool
     */
    private static function isDebugMode(): bool
    {
        return isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true';
    }

    /**
     * Log info message
     * 
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public static function info(string $message, array $context = []): void
    {
        self::get()->info($message, $context);
    }

    /**
     * Log warning message
     * 
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public static function warning(string $message, array $context = []): void
    {
        self::get()->warning($message, $context);
    }

    /**
     * Log error message
     * 
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public static function error(string $message, array $context = []): void
    {
        self::get()->error($message, $context);
    }

    /**
     * Log critical message
     * 
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public static function critical(string $message, array $context = []): void
    {
        self::get()->critical($message, $context);
    }

    /**
     * Log debug message
     * 
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public static function debug(string $message, array $context = []): void
    {
        self::get()->debug($message, $context);
    }

    /**
     * Log SQL query
     * 
     * @param string $query
     * @param array<mixed> $params
     * @param float $executionTime
     * @return void
     */
    public static function sql(string $query, array $params = [], float $executionTime = 0.0): void
    {
        if (!self::isDebugMode()) {
            return;
        }

        self::debug('SQL Query', [
            'query' => $query,
            'params' => $params,
            'execution_time_ms' => round($executionTime * 1000, 2),
        ]);
    }

    /**
     * Log authentication event
     * 
     * @param string $event
     * @param int|null $userId
     * @param array<string, mixed> $context
     * @return void
     */
    public static function auth(string $event, ?int $userId = null, array $context = []): void
    {
        $context['user_id'] = $userId;
        $context['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        self::info("Auth: {$event}", $context);
    }

    /**
     * Log security event
     * 
     * @param string $event
     * @param array<string, mixed> $context
     * @return void
     */
    public static function security(string $event, array $context = []): void
    {
        $context['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $context['request_uri'] = $_SERVER['REQUEST_URI'] ?? 'unknown';

        self::warning("Security: {$event}", $context);
    }

    /**
     * Log exception
     * 
     * @param \Throwable $exception
     * @param array<string, mixed> $context
     * @return void
     */
    public static function exception(\Throwable $exception, array $context = []): void
    {
        $context['exception'] = get_class($exception);
        $context['message'] = $exception->getMessage();
        $context['file'] = $exception->getFile();
        $context['line'] = $exception->getLine();
        $context['trace'] = $exception->getTraceAsString();

        self::error('Exception occurred', $context);
    }

    /**
     * Log performance metric
     * 
     * @param string $operation
     * @param float $duration Duration in seconds
     * @param array<string, mixed> $context
     * @return void
     */
    public static function performance(string $operation, float $duration, array $context = []): void
    {
        $context['operation'] = $operation;
        $context['duration_ms'] = round($duration * 1000, 2);

        if ($duration > 1.0) {
            self::warning('Slow operation detected', $context);
        } else {
            self::debug('Performance metric', $context);
        }
    }

    /**
     * Clear old log files (maintenance)
     * 
     * @param int $daysToKeep
     * @return int Number of files deleted
     */
    public static function cleanup(int $daysToKeep = 30): int
    {
        if (!is_dir(self::$logPath)) {
            return 0;
        }

        $deleted = 0;
        $cutoffTime = time() - ($daysToKeep * 86400);

        $files = glob(self::$logPath . '/*.log*');
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoffTime) {
                if (@unlink($file)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }
}
