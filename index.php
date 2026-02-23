<?php
/**
 * Redirecionador para public/index.php
 * Este arquivo redireciona todas as requisições para a pasta public/
 */

$uri = $_SERVER['REQUEST_URI'];

// Remover o caminho base /comexamesul/ se existir (ambiente local XAMPP)
$basePath = '/comexamesul';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Se a URI estiver vazia ou for apenas /, redirecionar para public/
if (empty($uri) || $uri === '/') {
    // Detectar base path automaticamente
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $publicPath = ($scriptDir === '/' || $scriptDir === '.') ? '/public/' : $scriptDir . '/public/';
    header('Location: ' . $publicPath);
    exit;
}

// Para outras rotas, incluir o index.php da pasta public
chdir(__DIR__ . '/public');
require __DIR__ . '/public/index.php';
