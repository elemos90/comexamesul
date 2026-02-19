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

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        static $basePath = null;
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';

        if ($basePath === null) {
            $basePath = str_replace('\\', '/', dirname($scriptName));

            // Fix for when script is running from root or perceived root
            if ($basePath === '/' || $basePath === '.') {
                $basePath = '';
            }

            // Robust XAMPP/Windows detection due to frequent SCRIPT_NAME issues
            // If base path is empty but we suspect we are in a subfolder
            if ($basePath === '') {
                $docRoot = str_replace(['/', '\\'], '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
                $scriptDir = str_replace(['/', '\\'], '/', dirname($_SERVER['SCRIPT_FILENAME'] ?? ''));

                // If script is inside document root
                if ($docRoot && strpos($scriptDir, $docRoot) === 0) {
                    $relPath = substr($scriptDir, strlen($docRoot));
                    // Should be something like /comexamesul/public
                    $basePath = '/' . ltrim($relPath, '/');
                }

                // Fallback: Check REQUEST_URI for known project folder
                if (($basePath === '' || $basePath === '/') && strpos($_SERVER['REQUEST_URI'] ?? '', '/comexamesul/public') !== false) {
                    $basePath = '/comexamesul/public';
                }
            }
        }

        if ($basePath === '/' || $basePath === '.') {
            $basePath = '';
        }

        // Detect if we are serving from the public directory
        $docRoot = str_replace(['/', '\\'], '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
        $servingFromPublic = str_ends_with($docRoot, '/public');

        // Se o script está na raiz (não na pasta public) e NÃO estamos servindo da public, adicionar /public para recursos estáticos
        if (!$servingFromPublic && !str_contains($scriptName, '/public/')) {
            // Para rotas/páginas, mantemos o basePath normal
            // Para recursos estáticos (css, js, assets, images), adicionamos /public
            $staticExtensions = ['.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.webp', '.ico', '.woff', '.woff2', '.ttf'];
            $isStaticResource = false;
            foreach ($staticExtensions as $ext) {
                if (str_ends_with($path, $ext)) {
                    $isStaticResource = true;
                    break;
                }
            }
            $staticPaths = ['/css/', '/js/', '/assets/', '/images/'];
            foreach ($staticPaths as $staticPath) {
                if (str_starts_with($path, $staticPath) || str_starts_with('/' . ltrim($path, '/'), $staticPath)) {
                    $isStaticResource = true;
                    break;
                }
            }

            if ($isStaticResource) {
                return $basePath . '/public/' . ltrim($path, '/');
            }
        }

        return $basePath . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void
    {
        if (!preg_match('~^https?://~', $url)) {
            $url = url($url);
        }

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
