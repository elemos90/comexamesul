<?php
// Script de verificação manual V2
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Utils/helpers.php';
require_once __DIR__ . '/bootstrap.php';

// Mock de Sessão e Auth
$_SESSION = [];
class MockAuth
{
    public static function user()
    {
        return ['id' => 1, 'name' => 'Admin', 'role' => 'admin'];
    }
}
// Forçar carregamento da classe Mock se Auth ainda não foi carregada
if (!class_exists('App\Utils\Auth')) {
    class_alias('MockAuth', 'App\Utils\Auth');
}

try {
    echo "=== Iniciando Diagnóstico de Pagamentos V2 ===\n";

    // 1. Instanciar Controller
    echo "[1] Instanciando PaymentController...\n";
    $controller = new \App\Controllers\PaymentController();
    echo "OK.\n";

    // 2. Testar Renderização Rates
    echo "[2] Testando Controller::rates() (Renderização de View)...\n";
    ob_start();
    $controller->rates();
    ob_end_clean();
    echo "OK. View rates.php renderizada com sucesso.\n";

    // 3. Testar Renderização Preview
    echo "[3] Testando Controller::preview(8) (Renderização de View)...\n";
    // Mock de $_GET se necessário? Controller usa args ou $_GET? 
    // preview($vacancyId) usa argumento.
    ob_start();
    $controller->preview(8);
    ob_end_clean();
    echo "OK. View preview.php renderizada com sucesso.\n";

} catch (\Throwable $e) {
    echo "\n!!! ERRO FATAL !!!\n";
    echo "Mensagem: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
