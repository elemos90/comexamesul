<?php
/**
 * Script de Reset da Base de Dados (Standalone)
 * 
 * Uso: php scripts/reset_transactional_data.php CONFIRMAR
 */

// Verificação de segurança
if (!isset($argv[1]) || $argv[1] !== 'CONFIRMAR') {
    echo "\n";
    echo "========================================================\n";
    echo "  SCRIPT DE RESET DA BASE DE DADOS\n";
    echo "========================================================\n\n";
    echo "Este script ira APAGAR:\n";
    echo "  - Vagas, Juris, Alocacoes, Relatorios\n";
    echo "  - Candidaturas, Pagamentos, Logs\n\n";
    echo "SERAO MANTIDOS:\n";
    echo "  + Utilizadores, Disciplinas, Locais, Salas\n\n";
    echo "Para executar:\n";
    echo "  php scripts/reset_transactional_data.php CONFIRMAR\n\n";
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

    $db->exec('SET FOREIGN_KEY_CHECKS = 0');

    $tables = [
        'application_status_history' => 'Historico de candidaturas',
        'availability_change_requests' => 'Pedidos de alteracao',
        'exam_reports' => 'Relatorios de exames',
        'jury_vigilantes' => 'Alocacoes de vigilantes',
        'payments' => 'Pagamentos',
        'payment_rates' => 'Taxas de pagamento',
        'vacancy_applications' => 'Candidaturas',
        'juries' => 'Juris',
        'exam_vacancies' => 'Vagas',
        'activity_log' => 'Logs de atividade',
    ];

    $total = 0;

    foreach ($tables as $table => $label) {
        $check = $db->query("SHOW TABLES LIKE '$table'")->rowCount();
        if ($check === 0) {
            echo "[SKIP] $table (nao existe)\n";
            continue;
        }

        $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        $db->exec("DELETE FROM $table");
        $db->exec("ALTER TABLE $table AUTO_INCREMENT = 1");

        echo "[OK] $label ($table): $count registos removidos\n";
        $total += $count;
    }

    $db->exec('SET FOREIGN_KEY_CHECKS = 1');

    // Reset disponibilidade
    $db->exec("UPDATE users SET available_for_vigilance = 0 WHERE role = 'vigilante'");
    echo "\n[OK] Campo available_for_vigilance resetado\n";

    echo "\n========================================================\n";
    echo "  RESET CONCLUIDO - Total: $total registos removidos\n";
    echo "========================================================\n\n";

} catch (PDOException $e) {
    echo "\n[ERRO] " . $e->getMessage() . "\n\n";
    exit(1);
}
