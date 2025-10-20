#!/usr/bin/env php
<?php

/**
 * Script de Verificação - Sistema v2.5
 * Verifica se todas as funcionalidades estão funcionando
 */

require_once __DIR__ . '/../bootstrap.php';

echo "=========================================\n";
echo "VERIFICAÇÃO - Sistema v2.5\n";
echo "=========================================\n\n";

$errors = [];
$warnings = [];
$passed = 0;

// 1. Verificar Models
echo "📦 Verificando Models...\n";
$models = [
    'ApplicationStatusHistory' => 'App\\Models\\ApplicationStatusHistory',
    'EmailNotification' => 'App\\Models\\EmailNotification',
];

foreach ($models as $name => $class) {
    if (class_exists($class)) {
        echo "  ✅ $name\n";
        $passed++;
    } else {
        echo "  ❌ $name - NÃO ENCONTRADO\n";
        $errors[] = "Model $name não encontrado";
    }
}

// 2. Verificar Services
echo "\n🔧 Verificando Services...\n";
$services = [
    'EmailNotificationService' => 'App\\Services\\EmailNotificationService',
    'ApplicationStatsService' => 'App\\Services\\ApplicationStatsService',
];

foreach ($services as $name => $class) {
    if (class_exists($class)) {
        echo "  ✅ $name\n";
        $passed++;
    } else {
        echo "  ❌ $name - NÃO ENCONTRADO\n";
        $errors[] = "Service $name não encontrado";
    }
}

// 3. Verificar Controllers
echo "\n🎮 Verificando Controllers...\n";
$controllers = [
    'ApplicationDashboardController' => 'App\\Controllers\\ApplicationDashboardController',
];

foreach ($controllers as $name => $class) {
    if (class_exists($class)) {
        echo "  ✅ $name\n";
        $passed++;
    } else {
        echo "  ❌ $name - NÃO ENCONTRADO\n";
        $errors[] = "Controller $name não encontrado";
    }
}

// 4. Verificar Banco de Dados
echo "\n💾 Verificando Banco de Dados...\n";
try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Verificar tabelas
    $tables = ['application_status_history', 'email_notifications'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "  ✅ Tabela $table\n";
            $passed++;
        } else {
            echo "  ❌ Tabela $table - NÃO ENCONTRADA\n";
            $errors[] = "Tabela $table não encontrada";
        }
    }

    // Verificar colunas
    $columns = [
        'vacancy_applications.rejection_reason',
        'vacancy_applications.reapply_count',
    ];
    foreach ($columns as $column) {
        list($table, $col) = explode('.', $column);
        $stmt = $pdo->query("SHOW COLUMNS FROM $table LIKE '$col'");
        if ($stmt->rowCount() > 0) {
            echo "  ✅ Coluna $column\n";
            $passed++;
        } else {
            echo "  ❌ Coluna $column - NÃO ENCONTRADA\n";
            $errors[] = "Coluna $column não encontrada";
        }
    }

    // Verificar views
    $views = ['v_application_stats', 'v_top_vigilantes', 'v_applications_by_day'];
    foreach ($views as $view) {
        $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_Type = 'VIEW' AND Tables_in_{$_ENV['DB_NAME']} = '$view'");
        if ($stmt->rowCount() > 0) {
            echo "  ✅ View $view\n";
            $passed++;
        } else {
            echo "  ⚠️  View $view - NÃO ENCONTRADA\n";
            $warnings[] = "View $view não encontrada";
        }
    }

} catch (PDOException $e) {
    echo "  ❌ Erro de conexão: " . $e->getMessage() . "\n";
    $errors[] = "Erro de banco de dados";
}

// 5. Verificar Arquivos de Script
echo "\n📜 Verificando Scripts...\n";
$scripts = [
    'send_emails_cron.php' => __DIR__ . '/send_emails_cron.php',
    'check_deadlines_cron.php' => __DIR__ . '/check_deadlines_cron.php',
];

foreach ($scripts as $name => $path) {
    if (file_exists($path)) {
        echo "  ✅ $name\n";
        $passed++;
    } else {
        echo "  ⚠️  $name - NÃO ENCONTRADO\n";
        $warnings[] = "Script $name não encontrado";
    }
}

// 6. Teste de Funcionalidade
echo "\n🧪 Testando Funcionalidades...\n";

try {
    // Testar EmailNotificationService
    $emailService = new \App\Services\EmailNotificationService();
    echo "  ✅ EmailNotificationService instanciado\n";
    $passed++;

    // Testar ApplicationStatsService
    $statsService = new \App\Services\ApplicationStatsService();
    $stats = $statsService->getGeneralStats();
    echo "  ✅ ApplicationStatsService funcionando (Total: {$stats['total_applications']} candidaturas)\n";
    $passed++;

    // Testar Models
    $historyModel = new \App\Models\ApplicationStatusHistory();
    echo "  ✅ ApplicationStatusHistory funcionando\n";
    $passed++;

    $emailModel = new \App\Models\EmailNotification();
    $emailStats = $emailModel->getStats();
    echo "  ✅ EmailNotification funcionando (Total: {$emailStats['total']} emails)\n";
    $passed++;

} catch (Exception $e) {
    echo "  ❌ Erro nos testes: " . $e->getMessage() . "\n";
    $errors[] = "Erro ao testar funcionalidades";
}

// Resultados
echo "\n=========================================\n";
echo "📊 RESULTADOS DA VERIFICAÇÃO\n";
echo "=========================================\n\n";

echo "✅ Verificações Passadas: $passed\n";
echo "❌ Erros: " . count($errors) . "\n";
echo "⚠️  Avisos: " . count($warnings) . "\n\n";

if (!empty($errors)) {
    echo "❌ ERROS ENCONTRADOS:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  AVISOS:\n";
    foreach ($warnings as $warning) {
        echo "  - $warning\n";
    }
    echo "\n";
}

if (empty($errors)) {
    echo "=========================================\n";
    echo "✅ SISTEMA v2.5 FUNCIONANDO CORRETAMENTE!\n";
    echo "=========================================\n\n";
    exit(0);
} else {
    echo "=========================================\n";
    echo "❌ SISTEMA COM ERROS - CORRIJA E REEXECUTE\n";
    echo "=========================================\n\n";
    exit(1);
}
