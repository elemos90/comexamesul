<?php
/**
 * Script Completo de Reset para Testes
 * 
 * Opções:
 *   php scripts/reset_test_data.php all       - Limpa TUDO (vagas, júris, candidaturas, etc.)
 *   php scripts/reset_test_data.php juries    - Limpa apenas júris e alocações
 *   php scripts/reset_test_data.php allocations - Limpa apenas alocações (vigilantes/supervisores)
 *   php scripts/reset_test_data.php applications - Limpa candidaturas
 *   php scripts/reset_test_data.php flags     - Reset feature flags para padrão
 */

$mode = $argv[1] ?? null;

if (!$mode || !in_array($mode, ['all', 'juries', 'allocations', 'applications', 'flags'])) {
    echo "\n";
    echo "========================================================\n";
    echo "  SCRIPT DE RESET PARA TESTES\n";
    echo "========================================================\n\n";
    echo "Uso: php scripts/reset_test_data.php [OPÇÃO]\n\n";
    echo "Opções disponíveis:\n";
    echo "  all          - Limpa TUDO (vagas, júris, candidaturas, pagamentos, logs)\n";
    echo "  juries       - Limpa júris e alocações (mantém vagas e candidaturas)\n";
    echo "  allocations  - Limpa apenas alocações (mantém júris)\n";
    echo "  applications - Limpa candidaturas (mantém vagas)\n";
    echo "  flags        - Reset feature flags para valores padrão\n\n";
    exit(0);
}

// Carregar .env
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

// Conectar
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

    echo "\n[OK] Conectado: $dbname\n";
    echo "[MODE] $mode\n\n";

    $db->exec('SET FOREIGN_KEY_CHECKS = 0');
    $total = 0;

    switch ($mode) {
        case 'all':
            $tables = [
                'application_status_history',
                'availability_change_requests',
                'exam_reports',
                'jury_vigilantes',
                'payments',
                'payment_rates',
                'vacancy_applications',
                'juries',
                'exam_vacancies',
                'activity_log',
            ];
            foreach ($tables as $table) {
                $count = deleteTable($db, $table);
                $total += $count;
            }
            $db->exec("UPDATE users SET available_for_vigilance = 0 WHERE role = 'vigilante'");
            echo "[OK] Campo available_for_vigilance resetado\n";
            break;

        case 'juries':
            $total += deleteTable($db, 'exam_reports');
            $total += deleteTable($db, 'jury_vigilantes');
            $total += deleteTable($db, 'juries');
            break;

        case 'allocations':
            $count = $db->query("SELECT COUNT(*) FROM jury_vigilantes")->fetchColumn();
            $db->exec("DELETE FROM jury_vigilantes");
            echo "[OK] jury_vigilantes: $count registos removidos\n";
            $total += $count;

            $count = $db->query("SELECT COUNT(*) FROM juries WHERE supervisor_id IS NOT NULL")->fetchColumn();
            $db->exec("UPDATE juries SET supervisor_id = NULL");
            echo "[OK] Supervisores removidos: $count júris\n";
            $total += $count;
            break;

        case 'applications':
            $total += deleteTable($db, 'application_status_history');
            $total += deleteTable($db, 'availability_change_requests');
            $total += deleteTable($db, 'vacancy_applications');
            $db->exec("UPDATE users SET available_for_vigilance = 0 WHERE role = 'vigilante'");
            echo "[OK] Campo available_for_vigilance resetado\n";
            break;

        case 'flags':
            // Reset feature flags para padrão
            $db->exec("UPDATE feature_flags SET enabled = 1 WHERE role = 'membro'");
            $db->exec("UPDATE feature_flags SET enabled = 1 WHERE role = 'vigilante'");
            $db->exec("UPDATE feature_flags SET enabled = 0 WHERE role = 'vigilante' AND feature_code IN ('guard.edit_post_exam', 'guard.export_payment_pdf')");
            echo "[OK] Feature flags resetadas para padrão\n";
            break;
    }

    $db->exec('SET FOREIGN_KEY_CHECKS = 1');

    echo "\n========================================================\n";
    echo "  RESET CONCLUÍDO [$mode]\n";
    echo "  Total: $total registos afetados\n";
    echo "========================================================\n\n";

} catch (PDOException $e) {
    echo "\n[ERRO] " . $e->getMessage() . "\n\n";
    exit(1);
}

function deleteTable(PDO $db, string $table): int
{
    $check = $db->query("SHOW TABLES LIKE '$table'")->rowCount();
    if ($check === 0) {
        echo "[SKIP] $table (não existe)\n";
        return 0;
    }

    $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    $db->exec("DELETE FROM $table");
    $db->exec("ALTER TABLE $table AUTO_INCREMENT = 1");
    echo "[OK] $table: $count registos removidos\n";
    return $count;
}
