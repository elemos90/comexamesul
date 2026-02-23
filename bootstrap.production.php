<?php

declare(strict_types=1);

// Configuração de erros para PRODUÇÃO
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
// Caminho correto para Linux/cPanel
ini_set('error_log', '/home/cycodene/logs/php_errors.log');
error_reporting(E_ALL);

define('BASE_PATH', __DIR__);
define('APP_START', microtime(true));

require_once BASE_PATH . '/app/Utils/Env.php';
require_once BASE_PATH . '/app/Utils/helpers.php';

$composerAutoload = BASE_PATH . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// Verificar se .env existe antes de carregar
$envPath = BASE_PATH . '/.env';
if (!file_exists($envPath)) {
    // Criar .env com configurações mínimas se não existir
    $defaultEnv = "APP_URL=https://jogos.cycode.net\n";
    $defaultEnv .= "APP_ENV=production\n";
    $defaultEnv .= "APP_DEBUG=false\n";
    file_put_contents($envPath, $defaultEnv);
}

App\Utils\Env::load($envPath);

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
        'cookie_secure' => filter_var(env('SESSION_SECURE', true), FILTER_VALIDATE_BOOL),
        'cookie_samesite' => 'Lax',
    ]);
}

if (!isset($_SESSION[env('CSRF_TOKEN_KEY', 'csrf_token')])) {
    $_SESSION[env('CSRF_TOKEN_KEY', 'csrf_token')] = bin2hex(random_bytes(32));
}
