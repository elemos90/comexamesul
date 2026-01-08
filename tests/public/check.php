<?php
// ARQUIVO DE DIAGNÓSTICO - APAGAR APÓS RESOLVER O ERRO 503

header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNÓSTICO DO SERVIDOR ===\n\n";

// 1. Versão do PHP
echo "✓ Versão PHP: " . phpversion() . "\n";
echo "✓ SAPI: " . php_sapi_name() . "\n\n";

// 2. Extensões necessárias
echo "=== EXTENSÕES PHP ===\n";
$extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'xml', 'zip', 'gd'];
foreach ($extensions as $ext) {
    echo ($extension_loaded($ext) ? '✓' : '✗') . " $ext\n";
}
echo "\n";

// 3. Permissões de diretórios
echo "=== PERMISSÕES ===\n";
$dirs = [
    __DIR__ . '/../storage' => 'storage/',
    __DIR__ . '/../storage/logs' => 'storage/logs/',
    __DIR__ . '/../storage/cache' => 'storage/cache/',
    __DIR__ . '/uploads' => 'uploads/',
];

foreach ($dirs as $path => $label) {
    $exists = is_dir($path);
    $writable = $exists && is_writable($path);
    echo ($writable ? '✓' : '✗') . " $label";
    if (!$exists) echo " [NÃO EXISTE]";
    elseif (!$writable) echo " [SEM PERMISSÃO DE ESCRITA]";
    echo "\n";
}
echo "\n";

// 4. Arquivos críticos
echo "=== ARQUIVOS CRÍTICOS ===\n";
$files = [
    __DIR__ . '/../.env' => '.env',
    __DIR__ . '/../bootstrap.php' => 'bootstrap.php',
    __DIR__ . '/../vendor/autoload.php' => 'vendor/autoload.php',
    __DIR__ . '/../app/Config/Database.php' => 'app/Config/Database.php',
];

foreach ($files as $path => $label) {
    echo (file_exists($path) ? '✓' : '✗') . " $label\n";
}
echo "\n";

// 5. Configurações PHP
echo "=== CONFIGURAÇÕES PHP ===\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "error_reporting: " . error_reporting() . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n\n";

// 6. Testar autoload
echo "=== TESTE DE AUTOLOAD ===\n";
try {
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
        echo "✓ Autoload funcionando\n";
    } else {
        echo "✗ vendor/autoload.php NÃO ENCONTRADO\n";
        echo "EXECUTE: composer install --no-dev\n";
    }
} catch (Exception $e) {
    echo "✗ Erro no autoload: " . $e->getMessage() . "\n";
}
echo "\n";

// 7. Testar conexão com banco
echo "=== TESTE DE BANCO DE DADOS ===\n";
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    $host = $env['DB_HOST'] ?? 'localhost';
    $db = $env['DB_DATABASE'] ?? '';
    $user = $env['DB_USERNAME'] ?? '';
    $pass = $env['DB_PASSWORD'] ?? '';
    
    if ($db && $user) {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
            echo "✓ Conexão com banco OK\n";
            echo "✓ Banco: $db\n";
        } catch (PDOException $e) {
            echo "✗ Erro de conexão: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✗ Credenciais de banco não configuradas no .env\n";
    }
} else {
    echo "✗ Arquivo .env não encontrado\n";
}
echo "\n";

echo "=== FIM DO DIAGNÓSTICO ===\n";
echo "\nSe tudo estiver OK (✓), o erro 503 pode ser causado por:\n";
echo "1. .htaccess com regras incompatíveis\n";
echo "2. PHP-FPM com problemas no servidor\n";
echo "3. Limites de recursos do servidor\n";
echo "4. Document Root incorreto no cPanel\n";
echo "\n⚠️ APAGUE ESTE ARQUIVO APÓS RESOLVER O PROBLEMA!\n";
