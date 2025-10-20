<?php

/**
 * Bootstrap para Testes PHPUnit
 * 
 * Carrega dependências e configurações necessárias para executar os testes
 */

// Definir ambiente de teste
define('BASE_PATH', dirname(__DIR__));
define('APP_START', microtime(true));

// Carregar helpers e autoload
require_once BASE_PATH . '/app/Utils/Env.php';
require_once BASE_PATH . '/app/Utils/helpers.php';
require_once BASE_PATH . '/vendor/autoload.php';

// Carregar variáveis de ambiente
App\Utils\Env::load(BASE_PATH . '/.env');

// Configurar timezone
date_default_timezone_set(env('APP_TIMEZONE', 'Africa/Maputo'));

// Configurar sessão mock para testes (evitar warnings)
if (session_status() === PHP_SESSION_NONE) {
    $_SESSION = [];
}

// Mockar CSRF token para testes
$_SESSION[env('CSRF_TOKEN_KEY', 'csrf_token')] = 'test_csrf_token_' . bin2hex(random_bytes(16));

// Configurações específicas para ambiente de teste
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "\n";
echo "========================================\n";
echo "  Portal Comexamesul - Test Suite\n";
echo "========================================\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Environment: " . env('APP_ENV', 'local') . "\n";
echo "Timezone: " . date_default_timezone_get() . "\n";
echo "========================================\n\n";
