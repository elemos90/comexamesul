<?php

declare(strict_types=1);

// Configuração de erros
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

define('BASE_PATH', __DIR__);
define('APP_START', microtime(true));

// Configuração portátil de logs
$logDir = BASE_PATH . '/storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/app.log';
ini_set('error_log', $logFile);

require_once BASE_PATH . '/app/Utils/Env.php';
require_once BASE_PATH . '/app/Utils/helpers.php';

$composerAutoload = BASE_PATH . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

App\Utils\Env::load(BASE_PATH . '/.env');

// Validar variáveis de ambiente obrigatórias
try {
    if (class_exists('App\\Config\\EnvValidator')) {
        App\Config\EnvValidator::validate();
    }
} catch (\RuntimeException $e) {
    error_log('[BOOTSTRAP] Environment validation failed: ' . $e->getMessage());

    // Em produção, falhar imediatamente
    if (env('APP_ENV') === 'production') {
        die('Configuration Error: Please contact system administrator.');
    }

    // Em desenvolvimento, mostrar erro detalhado
    die($e->getMessage());
}

// Definir nome padrão da aplicação se não estiver configurado
if (!env('APP_NAME')) {
    App\Utils\Env::set('APP_NAME', 'Portal da Comissão de Exames de Admissão');
}

date_default_timezone_set(env('APP_TIMEZONE', 'Africa/Maputo'));

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name(env('SESSION_NAME', 'exam_portal_session'));
    session_start([
        'cookie_lifetime' => (int) env('SESSION_LIFETIME', 7200),
        'cookie_httponly' => true,
        'cookie_secure' => filter_var(env('SESSION_SECURE', false), FILTER_VALIDATE_BOOL),
        'cookie_samesite' => 'Lax',
        'cookie_path' => dirname($_SERVER['SCRIPT_NAME']),
    ]);
}

if (!isset($_SESSION[env('CSRF_TOKEN_KEY', 'csrf_token')])) {
    $_SESSION[env('CSRF_TOKEN_KEY', 'csrf_token')] = bin2hex(random_bytes(32));
}

