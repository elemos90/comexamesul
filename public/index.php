<?php

declare(strict_types=1);

// Iniciar output buffering para capturar qualquer saída acidental
ob_start();

require_once __DIR__ . '/../bootstrap.php';

use App\Routes\Router;
use App\Http\Request;

// Processar requisições JSON
$body = $_POST;
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($contentType, 'application/json') !== false) {
    $rawBody = file_get_contents('php://input');
    $jsonData = json_decode($rawBody, true);
    
    if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
        $body = $jsonData;
    }
}

$request = new Request($_SERVER, $_GET, $body, $_FILES);
$router = new Router($request);

require base_path('app/Routes/web.php');

$router->dispatch();

