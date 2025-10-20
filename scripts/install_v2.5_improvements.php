#!/usr/bin/env php
<?php

/**
 * Script de Instalação - Melhorias v2.5
 * Instala todas as melhorias do sistema de candidaturas
 */

require_once __DIR__ . '/../bootstrap.php';

echo "=========================================\n";
echo "INSTALAÇÃO - Melhorias v2.5\n";
echo "Sistema de Candidaturas de Vigilantes\n";
echo "=========================================\n\n";

// Verificar se .env existe
if (!file_exists(__DIR__ . '/../.env')) {
    echo "❌ ERRO: Arquivo .env não encontrado!\n";
    echo "Por favor, copie .env.example para .env e configure as credenciais.\n";
    exit(1);
}

// Carregar configurações
$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbName = $_ENV['DB_NAME'] ?? 'comexamesul';
$dbUser = $_ENV['DB_USER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? '';

echo "📋 Configurações:\n";
echo "   Host: $dbHost\n";
echo "   Database: $dbName\n";
echo "   User: $dbUser\n\n";

try {
    // Conectar ao banco
    echo "🔌 Conectando ao banco de dados...\n";
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "✅ Conexão estabelecida!\n\n";

    // Ler arquivo de migrations
    $migrationsFile = __DIR__ . '/../app/Database/migrations_v2.5.sql';
    
    if (!file_exists($migrationsFile)) {
        echo "❌ ERRO: Arquivo migrations_v2.5.sql não encontrado!\n";
        exit(1);
    }

    echo "📂 Lendo migrations v2.5...\n";
    $sql = file_get_contents($migrationsFile);

    // Dividir em statements individuais
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^--/', $stmt) && 
                   !preg_match('/^\/\*/', $stmt);
        }
    );

    echo "🔧 Executando migrations...\n\n";

    $successCount = 0;
    $errorCount = 0;

    foreach ($statements as $statement) {
        // Ignorar linhas de comentário e delimiters
        if (preg_match('/^(DELIMITER|--)/i', trim($statement))) {
            continue;
        }

        try {
            $pdo->exec($statement . ';');
            $successCount++;
            
            // Mostrar progresso
            if (preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/i', $statement, $matches)) {
                echo "  ✅ Tabela criada: {$matches[1]}\n";
            } elseif (preg_match('/ALTER TABLE (\w+)/i', $statement, $matches)) {
                echo "  ✅ Tabela alterada: {$matches[1]}\n";
            } elseif (preg_match('/CREATE TRIGGER (\w+)/i', $statement, $matches)) {
                echo "  ✅ Trigger criado: {$matches[1]}\n";
            } elseif (preg_match('/CREATE OR REPLACE VIEW (\w+)/i', $statement, $matches)) {
                echo "  ✅ View criada: {$matches[1]}\n";
            }
        } catch (PDOException $e) {
            // Ignorar erros de "já existe" mas reportar outros
            if (strpos($e->getMessage(), 'already exists') === false &&
                strpos($e->getMessage(), 'Duplicate column') === false) {
                echo "  ⚠️  Aviso: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }
    }

    echo "\n";
    echo "📊 Resultados:\n";
    echo "   ✅ Statements executados: $successCount\n";
    if ($errorCount > 0) {
        echo "   ⚠️  Avisos: $errorCount\n";
    }

    // Verificar instalação
    echo "\n🔍 Verificando instalação...\n";

    // Verificar tabelas
    $tables = ['application_status_history', 'email_notifications'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "  ✅ Tabela $table: OK\n";
        } else {
            echo "  ❌ Tabela $table: NÃO ENCONTRADA\n";
        }
    }

    // Verificar colunas novas
    $stmt = $pdo->query("SHOW COLUMNS FROM vacancy_applications LIKE 'rejection_reason'");
    if ($stmt->rowCount() > 0) {
        echo "  ✅ Coluna rejection_reason: OK\n";
    } else {
        echo "  ❌ Coluna rejection_reason: NÃO ENCONTRADA\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM vacancy_applications LIKE 'reapply_count'");
    if ($stmt->rowCount() > 0) {
        echo "  ✅ Coluna reapply_count: OK\n";
    } else {
        echo "  ❌ Coluna reapply_count: NÃO ENCONTRADA\n";
    }

    // Verificar triggers
    $stmt = $pdo->query("SHOW TRIGGERS WHERE `Trigger` = 'trg_application_status_history'");
    if ($stmt->rowCount() > 0) {
        echo "  ✅ Trigger trg_application_status_history: OK\n";
    } else {
        echo "  ⚠️  Trigger trg_application_status_history: NÃO ENCONTRADO\n";
    }

    // Verificar views
    $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_Type = 'VIEW' AND Tables_in_$dbName = 'v_application_stats'");
    if ($stmt->rowCount() > 0) {
        echo "  ✅ View v_application_stats: OK\n";
    } else {
        echo "  ⚠️  View v_application_stats: NÃO ENCONTRADA\n";
    }

    echo "\n";
    echo "=========================================\n";
    echo "✅ INSTALAÇÃO CONCLUÍDA COM SUCESSO!\n";
    echo "=========================================\n\n";

    echo "📚 Próximos Passos:\n";
    echo "1. Configure o cron job de emails:\n";
    echo "   */5 * * * * php " . __DIR__ . "/send_emails_cron.php\n\n";
    echo "2. Teste o sistema:\n";
    echo "   php " . __DIR__ . "/verify_v2.5_system.php\n\n";
    echo "3. Acesse o dashboard:\n";
    echo "   {$_ENV['APP_URL']}/applications/dashboard\n\n";

} catch (PDOException $e) {
    echo "\n❌ ERRO DE BANCO DE DADOS:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ ERRO:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
}
