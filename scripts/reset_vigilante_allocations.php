<?php
/**
 * Script para Reset de Alocações de Vigilantes
 * 
 * Limpa APENAS as alocações, mantendo:
 * - Vagas
 * - Júris
 * - Candidaturas
 * - Utilizadores
 * 
 * Uso: php scripts/reset_vigilante_allocations.php CONFIRMAR
 */

// Verificação de segurança
if (!isset($argv[1]) || $argv[1] !== 'CONFIRMAR') {
    echo "\n";
    echo "========================================================\n";
    echo "  RESET DE ALOCAÇÕES DE VIGILANTES\n";
    echo "========================================================\n\n";
    echo "Este script irá APAGAR:\n";
    echo "  - Alocações de vigilantes (jury_vigilantes)\n";
    echo "  - Supervisores atribuídos aos júris (supervisor_id = NULL)\n\n";
    echo "SERÃO MANTIDOS:\n";
    echo "  + Vagas, Júris, Candidaturas, Utilizadores\n";
    echo "  + Pagamentos, Relatórios\n\n";
    echo "Para executar:\n";
    echo "  php scripts/reset_vigilante_allocations.php CONFIRMAR\n\n";
    exit(0);
}

// Carregar .env manualmente
$envFile = __DIR__ . '/../.env';
$env = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0)
            continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }
    }
}

// Conectar ao banco de dados
$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$dbname = $env['DB_DATABASE'] ?? 'comexamesul';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';

try {
    $db = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "\n[OK] Conectado ao banco de dados: $dbname\n\n";

    // Contar alocações atuais
    $vigilanteCount = $db->query("SELECT COUNT(*) FROM jury_vigilantes")->fetchColumn();
    $supervisorCount = $db->query("SELECT COUNT(*) FROM juries WHERE supervisor_id IS NOT NULL")->fetchColumn();
    $juriesCount = $db->query("SELECT COUNT(*) FROM juries")->fetchColumn();

    echo "Estado atual:\n";
    echo "  - Júris: $juriesCount\n";
    echo "  - Alocações de vigilantes: $vigilanteCount\n";
    echo "  - Júris com supervisor: $supervisorCount\n\n";

    // Limpar alocações de vigilantes
    $db->exec("DELETE FROM jury_vigilantes");
    echo "[OK] Alocações de vigilantes removidas: $vigilanteCount\n";

    // Limpar supervisores dos júris
    $db->exec("UPDATE juries SET supervisor_id = NULL");
    echo "[OK] Supervisores removidos dos júris: $supervisorCount\n";

    // Resetar auto-increment
    $db->exec("ALTER TABLE jury_vigilantes AUTO_INCREMENT = 1");
    echo "[OK] Auto-increment resetado\n";

    echo "\n========================================================\n";
    echo "  RESET CONCLUÍDO\n";
    echo "  Júris mantidos: $juriesCount\n";
    echo "  Alocações removidas: " . ($vigilanteCount + $supervisorCount) . "\n";
    echo "========================================================\n\n";
    echo "Agora pode testar o wizard de criação de júris!\n\n";

} catch (PDOException $e) {
    echo "\n[ERRO] " . $e->getMessage() . "\n\n";
    exit(1);
}
