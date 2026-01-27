<?php

declare(strict_types=1);

namespace App\Config;

use RuntimeException;

/**
 * Environment Variables Validator
 * 
 * Validates that all required environment variables are set
 * to prevent cryptic runtime errors in production.
 * 
 * @package App\Config
 */
class EnvValidator
{
    /**
     * Required environment variables
     * @var array<string>
     */
    private const REQUIRED_VARS = [
        // Database
        'DB_HOST',
        'DB_DATABASE',
        'DB_USERNAME',

        // Application
        'APP_NAME',
        'APP_ENV',
        'APP_URL',

        // Security
        'SESSION_NAME',
        'CSRF_TOKEN_KEY',
    ];

    /**
     * Optional but recommended variables
     * @var array<string>
     */
    private const RECOMMENDED_VARS = [
        'APP_TIMEZONE',
        'SESSION_LIFETIME',
        'RATE_LIMIT_MAX_ATTEMPTS',
        'MAIL_FROM_ADDRESS',
    ];

    /**
     * Production-specific required variables
     * @var array<string>
     */
    private const PRODUCTION_VARS = [
        'MAIL_SMTP_HOST',
        'MAIL_SMTP_USER',
        'MAIL_SMTP_PASS',
    ];

    /**
     * Validate environment configuration
     * 
     * @throws RuntimeException if required variables are missing
     * @return void
     */
    public static function validate(): void
    {
        $missing = self::getMissingRequired();

        if (!empty($missing)) {
            throw new RuntimeException(
                'Missing required environment variables: ' . implode(', ', $missing) . "\n" .
                'Please check your .env file and ensure all required variables are set.'
            );
        }

        // Check production-specific requirements
        if (self::isProduction()) {
            $missingProd = self::getMissingProduction();
            if (!empty($missingProd)) {
                throw new RuntimeException(
                    'Missing production environment variables: ' . implode(', ', $missingProd) . "\n" .
                    'These are required when APP_ENV=production'
                );
            }
        }

        // Warn about missing recommended variables (non-blocking)
        self::checkRecommended();
    }

    /**
     * Get list of missing required variables
     * 
     * @return array<string>
     */
    private static function getMissingRequired(): array
    {
        $missing = [];

        foreach (self::REQUIRED_VARS as $var) {
            if (!self::hasEnvVar($var)) {
                $missing[] = $var;
            }
        }

        return $missing;
    }

    /**
     * Get list of missing production variables
     * 
     * @return array<string>
     */
    private static function getMissingProduction(): array
    {
        $missing = [];

        foreach (self::PRODUCTION_VARS as $var) {
            if (!self::hasEnvVar($var)) {
                $missing[] = $var;
            }
        }

        return $missing;
    }

    /**
     * Check for recommended variables and log warnings
     * 
     * @return void
     */
    private static function checkRecommended(): void
    {
        $missing = [];

        foreach (self::RECOMMENDED_VARS as $var) {
            if (!self::hasEnvVar($var)) {
                $missing[] = $var;
            }
        }

        if (!empty($missing) && self::shouldWarn()) {
            error_log(
                '[EnvValidator] Recommended environment variables not set: ' .
                implode(', ', $missing)
            );
        }
    }

    /**
     * Check if environment variable exists and is not empty
     * 
     * @param string $var Variable name
     * @return bool
     */
    private static function hasEnvVar(string $var): bool
    {
        // Check $_ENV first (preferred)
        if (isset($_ENV[$var]) && $_ENV[$var] !== '') {
            return true;
        }

        // Fallback to getenv()
        $value = getenv($var);
        return $value !== false && $value !== '';
    }

    /**
     * Check if running in production environment
     * 
     * @return bool
     */
    private static function isProduction(): bool
    {
        $env = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production';
        return in_array($env, ['production', 'prod'], true);
    }

    /**
     * Check if warnings should be displayed
     * 
     * @return bool
     */
    private static function shouldWarn(): bool
    {
        return !self::isProduction() || (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true');
    }

    /**
     * Get validation report (for debugging)
     * 
     * @return array<string, mixed>
     */
    public static function getReport(): array
    {
        return [
            'required' => [
                'total' => count(self::REQUIRED_VARS),
                'missing' => self::getMissingRequired(),
                'valid' => empty(self::getMissingRequired()),
            ],
            'production' => [
                'total' => count(self::PRODUCTION_VARS),
                'missing' => self::isProduction() ? self::getMissingProduction() : [],
                'required' => self::isProduction(),
            ],
            'recommended' => [
                'total' => count(self::RECOMMENDED_VARS),
                'missing' => array_filter(
                    self::RECOMMENDED_VARS,
                    fn($var) => !self::hasEnvVar($var)
                ),
            ],
            'environment' => [
                'current' => $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'unknown',
                'debug' => $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: 'false',
            ],
        ];
    }
}
