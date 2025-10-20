<?php

require_once __DIR__ . '/../bootstrap.php';

echo "=======================================================\n";
echo "VERIFICAÇÃO: Sistema de Alocação Drag-and-Drop\n";
echo "=======================================================\n\n";

$dbHost = env('DB_HOST', '127.0.0.1');
$dbPort = env('DB_PORT', '3306');
$dbName = env('DB_DATABASE', 'comexamesul');
$dbUser = env('DB_USERNAME', 'root');
$dbPass = env('DB_PASSWORD', '');

$errors = [];
$warnings = [];
$success = [];

try {
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "✓ Conexão com banco estabelecida\n\n";
    
    // 1. Verificar campos novos em juries
    echo "1. Verificando campos em 'juries'...\n";
    $result = $pdo->query("SHOW COLUMNS FROM juries LIKE 'vigilantes_capacity'");
    if ($result->rowCount() > 0) {
        $success[] = "Campo 'vigilantes_capacity' existe";
        echo "  ✓ vigilantes_capacity\n";
    } else {
        $errors[] = "Campo 'vigilantes_capacity' NÃO existe";
        echo "  ✗ vigilantes_capacity FALTA\n";
    }
    
    $result = $pdo->query("SHOW COLUMNS FROM juries LIKE 'requires_supervisor'");
    if ($result->rowCount() > 0) {
        $success[] = "Campo 'requires_supervisor' existe";
        echo "  ✓ requires_supervisor\n";
    } else {
        $warnings[] = "Campo 'requires_supervisor' NÃO existe (opcional)";
        echo "  ⚠ requires_supervisor FALTA (opcional)\n";
    }
    
    // 2. Verificar campos auxiliares em jury_vigilantes
    echo "\n2. Verificando campos em 'jury_vigilantes'...\n";
    $result = $pdo->query("SHOW COLUMNS FROM jury_vigilantes LIKE 'jury_exam_date'");
    if ($result->rowCount() > 0) {
        $success[] = "Campos auxiliares em jury_vigilantes existem";
        echo "  ✓ jury_exam_date, jury_start_time, jury_end_time\n";
    } else {
        $warnings[] = "Campos auxiliares em jury_vigilantes NÃO existem (performance reduzida)";
        echo "  ⚠ Campos auxiliares FALTAM (performance reduzida)\n";
    }
    
    // 3. Verificar triggers
    echo "\n3. Verificando triggers...\n";
    $expectedTriggers = [
        'trg_check_vigilantes_capacity',
        'trg_check_vigilante_conflicts',
        'trg_check_supervisor_conflicts'
    ];
    
    foreach ($expectedTriggers as $trigger) {
        $result = $pdo->query("SHOW TRIGGERS LIKE 'jury%'");
        $triggers = $result->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array($trigger, $triggers)) {
            $success[] = "Trigger '{$trigger}' existe";
            echo "  ✓ {$trigger}\n";
        } else {
            $errors[] = "Trigger '{$trigger}' NÃO existe";
            echo "  ✗ {$trigger} FALTA\n";
        }
    }
    
    // 4. Verificar views
    echo "\n4. Verificando views...\n";
    $expectedViews = [
        'vw_vigilante_workload',
        'vw_jury_slots',
        'vw_eligible_vigilantes',
        'vw_eligible_supervisors',
        'vw_allocation_stats'
    ];
    
    $result = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
    $existingViews = $result->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($expectedViews as $view) {
        if (in_array($view, $existingViews)) {
            $success[] = "View '{$view}' existe";
            echo "  ✓ {$view}\n";
        } else {
            $errors[] = "View '{$view}' NÃO existe";
            echo "  ✗ {$view} FALTA\n";
        }
    }
    
    // 5. Verificar índices
    echo "\n5. Verificando índices...\n";
    $result = $pdo->query("SHOW INDEX FROM jury_vigilantes WHERE Key_name = 'idx_jv_vigilante_datetime'");
    if ($result->rowCount() > 0) {
        $success[] = "Índices de performance existem";
        echo "  ✓ idx_jv_vigilante_datetime\n";
    } else {
        $warnings[] = "Índices de performance NÃO existem (queries podem ser lentas)";
        echo "  ⚠ idx_jv_vigilante_datetime FALTA (performance reduzida)\n";
    }
    
    // 6. Verificar arquivos PHP
    echo "\n6. Verificando arquivos do sistema...\n";
    $requiredFiles = [
        __DIR__ . '/../app/Controllers/JuryController.php' => 'JuryController',
        __DIR__ . '/../app/Services/AllocationService.php' => 'AllocationService',
        __DIR__ . '/../app/Views/juries/planning.php' => 'View de Planejamento',
        __DIR__ . '/../public/js/planning-dnd.js' => 'JavaScript DnD'
    ];
    
    foreach ($requiredFiles as $file => $name) {
        if (file_exists($file)) {
            $success[] = "Arquivo '{$name}' existe";
            echo "  ✓ {$name}\n";
        } else {
            $errors[] = "Arquivo '{$name}' NÃO existe";
            echo "  ✗ {$name} FALTA\n";
        }
    }
    
    // 7. Verificar rotas
    echo "\n7. Verificando rotas...\n";
    $webFile = __DIR__ . '/../app/Routes/web.php';
    if (file_exists($webFile)) {
        $webContent = file_get_contents($webFile);
        
        $requiredRoutes = [
            '/juries/planning' => 'Página de Planejamento',
            '/api/allocation/can-assign' => 'API: Validar Alocação',
            '/api/allocation/auto-allocate-jury' => 'API: Auto-Alocar Júri',
            '/api/allocation/metrics' => 'API: Métricas',
            '/api/allocation/swap' => 'API: Trocar Vigilantes'
        ];
        
        foreach ($requiredRoutes as $route => $name) {
            if (strpos($webContent, $route) !== false) {
                $success[] = "Rota '{$route}' configurada";
                echo "  ✓ {$name} ({$route})\n";
            } else {
                $errors[] = "Rota '{$route}' NÃO configurada";
                echo "  ✗ {$name} ({$route}) FALTA\n";
            }
        }
    } else {
        $errors[] = "Arquivo de rotas NÃO encontrado";
        echo "  ✗ web.php FALTA\n";
    }
    
    // 8. Testar queries das views (se existem dados)
    echo "\n8. Testando consultas das views...\n";
    try {
        $result = $pdo->query("SELECT COUNT(*) FROM vw_allocation_stats");
        echo "  ✓ vw_allocation_stats é consultável\n";
        $success[] = "Views são consultáveis";
    } catch (Exception $e) {
        $errors[] = "Erro ao consultar views: " . $e->getMessage();
        echo "  ✗ Erro ao consultar views\n";
    }
    
    // Resumo
    echo "\n=======================================================\n";
    echo "RESUMO DA VERIFICAÇÃO\n";
    echo "=======================================================\n";
    echo "✓ Sucessos: " . count($success) . "\n";
    echo "⚠ Avisos: " . count($warnings) . "\n";
    echo "✗ Erros: " . count($errors) . "\n\n";
    
    if (count($errors) > 0) {
        echo "❌ ERROS ENCONTRADOS:\n";
        foreach ($errors as $error) {
            echo "  • {$error}\n";
        }
        echo "\n⚠️  Execute a migration: php scripts/run_allocation_migration.php\n";
        exit(1);
    }
    
    if (count($warnings) > 0) {
        echo "⚠️  AVISOS:\n";
        foreach ($warnings as $warning) {
            echo "  • {$warning}\n";
        }
        echo "\n";
    }
    
    echo "✅ SISTEMA DE ALOCAÇÃO DRAG-AND-DROP ESTÁ PRONTO!\n\n";
    echo "Próximos passos:\n";
    echo "1. Acesse: http://localhost/juries/planning\n";
    echo "2. Ou clique em: Júris → Planejamento\n";
    echo "3. Arraste vigilantes para os júris\n";
    echo "4. Teste auto-alocação\n\n";
    echo "📚 Documentação: SISTEMA_ALOCACAO_DND.md\n";
    echo "🚀 Guia rápido: INSTALACAO_DND.md\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO CRÍTICO: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
