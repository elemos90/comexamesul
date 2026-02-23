<?php
/**
 * Diagnóstico de PHP - APAGAR APÓS VERIFICAÇÃO
 */
echo "<h1>Diagnóstico PHP - jogos.cycode.net</h1>";
echo "<h2>PHP Version: " . phpversion() . "</h2>";
echo "<h3>Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "</h3>";
echo "<h3>Server API: " . php_sapi_name() . "</h3>";
echo "<h3>Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "</h3>";
echo "<h3>Script Filename: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "</h3>";

// Verificar módulos carregados
echo "<h3>Loaded Modules:</h3><pre>";
if (function_exists('apache_get_modules')) {
    print_r(apache_get_modules());
} else {
    echo "apache_get_modules() não disponível (provavelmente LiteSpeed ou CGI/FPM)";
}
echo "</pre>";

// Verificar handlers PHP disponíveis
echo "<h3>PHP Handler Info:</h3><pre>";
echo "PHP Binary: " . PHP_BINARY . "\n";
echo "PHP OS: " . PHP_OS . "\n";
echo "PHP INT SIZE: " . PHP_INT_SIZE . "\n";
echo "PHP SAPI: " . php_sapi_name() . "\n";

// Verificar se existe Composer
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
echo "\nComposer autoload exists: " . (file_exists($composerAutoload) ? 'YES' : 'NO') . "\n";

// Verificar bootstrap
$bootstrap = __DIR__ . '/../bootstrap.php';
echo "Bootstrap exists: " . (file_exists($bootstrap) ? 'YES' : 'NO') . "\n";

// Verificar .env
$envFile = __DIR__ . '/../.env';
echo ".env exists: " . (file_exists($envFile) ? 'YES' : 'NO') . "\n";

echo "</pre>";

// PHP Info Resumido
echo "<h3>PHP Info (resumido):</h3>";
ob_start();
phpinfo(INFO_GENERAL | INFO_CONFIGURATION);
$phpinfo = ob_get_clean();
echo $phpinfo;
