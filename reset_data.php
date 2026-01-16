<?php

declare(strict_types=1);

// Bootstrapping
require_once __DIR__ . '/bootstrap.php';

// Force error display for CLI
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use App\Database\Connection;

echo "⚠️ INICIANDO LIMPEZA DE DADOS OPERACIONAIS...\n";
echo "⚠️ ESTA AÇÃO É IRREVERSÍVEL. CERTIFIQUE-SE DE QUE TEM UM BACKUP.\n\n";
echo "❌ OS SEGUINTES DADOS SERÃO MANTIDOS:\n";
echo "   - Usuários (Vigilantes, Coordenadores, etc.)\n";
echo "   - Disciplinas\n";
echo "   - Locais de Exame e Salas\n";
echo "   - Configurações (Taxas, Flags)\n\n";
echo "--------------------------------------------------\n";

try {
    $db = Connection::getInstance();

    // Obter tabelas existentes para evitar erros de tabela inexistente
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $existingTables = array_flip($tables);

    // Função auxiliar para limpar tabela com segurança
    $cleanTable = function ($tableName) use ($db, $existingTables) {
        if (isset($existingTables[$tableName])) {
            echo "🗑️  Limpar '{$tableName}'... ";
            try {
                // Tenta TRUNCATE primeiro se não houver FKs ativas, ou DELETE
                // Como desativamos FKs, DELETE é seguro. TRUNCATE reseta auto-increment.
                $stmt = $db->query("DELETE FROM {$tableName}");
                echo "OK (" . $stmt->rowCount() . ")\n";
            } catch (\Exception $e) {
                echo "ERRO: " . $e->getMessage() . "\n";
            }
        } else {
            echo "⚠️  Tabela '{$tableName}' não existe. Ignorada.\n";
        }
    };

    // Desabilitar verificação de chave estrangeira
    $db->exec('SET FOREIGN_KEY_CHECKS = 0');

    echo "\n--- GRUPO 1: DADOS FOLHA ---\n";
    $cleanTable('payments');
    $cleanTable('exam_reports');
    $cleanTable('activity_logs');
    $cleanTable('email_notifications');
    $cleanTable('location_stats');

    echo "\n--- GRUPO 2: DADOS INTERMÉDIOS ---\n";
    $cleanTable('application_status_history');
    $cleanTable('jury_vigilantes');
    $cleanTable('availability_change_requests');

    echo "\n--- GRUPO 3: DADOS CORE ---\n";
    $cleanTable('juries');
    $cleanTable('vacancy_applications');

    echo "\n--- GRUPO 4: RAÍZES OPERACIONAIS ---\n";
    $cleanTable('exam_vacancies');

    // Reabilitar FKs
    $db->exec('SET FOREIGN_KEY_CHECKS = 1');

    echo "--------------------------------------------------\n";
    echo "🎉 LIMPEZA CONCLUÍDA COM SUCESSO!\n";

} catch (\Exception $e) {
    echo "\n❌ ERRO FATAL: " . $e->getMessage() . "\n";
    try {
        $db->exec('SET FOREIGN_KEY_CHECKS = 1');
    } catch (\Exception $ex) {
    }
}
