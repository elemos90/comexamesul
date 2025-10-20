<?php

require_once __DIR__ . '/../bootstrap.php';

echo "=======================================================\n";
echo "MIGRATION: Melhorias no Sistema de Alocação\n";
echo "=======================================================\n\n";

$dbHost = env('DB_HOST', '127.0.0.1');
$dbPort = env('DB_PORT', '3306');
$dbName = env('DB_DATABASE', 'comexamesul');
$dbUser = env('DB_USERNAME', 'root');
$dbPass = env('DB_PASSWORD', '');

try {
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "✓ Conectado ao banco: {$dbName}\n\n";
    
    // Ler arquivo de migration
    $migrationFile = __DIR__ . '/../app/Database/allocation_improvements_migration.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Arquivo de migration não encontrado: {$migrationFile}");
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Dividir em comandos individuais (separar por delimiter)
    $commands = [];
    $current = '';
    $inDelimiter = false;
    
    foreach (explode("\n", $sql) as $line) {
        $trimmed = trim($line);
        
        // Ignorar comentários
        if (empty($trimmed) || strpos($trimmed, '--') === 0) {
            continue;
        }
        
        if (stripos($trimmed, 'DELIMITER') === 0) {
            if ($inDelimiter) {
                // Fim do bloco delimiter
                if (!empty($current)) {
                    $commands[] = $current;
                    $current = '';
                }
                $inDelimiter = false;
            } else {
                // Início do bloco delimiter
                $inDelimiter = true;
            }
            continue;
        }
        
        if ($inDelimiter) {
            $current .= $line . "\n";
            if (strpos($trimmed, '$$') !== false) {
                // Fim de procedure/trigger
                $commands[] = $current;
                $current = '';
            }
        } else {
            $current .= $line . "\n";
            if (substr($trimmed, -1) === ';') {
                $commands[] = $current;
                $current = '';
            }
        }
    }
    
    if (!empty($current)) {
        $commands[] = $current;
    }
    
    echo "Total de comandos SQL a executar: " . count($commands) . "\n\n";
    
    $executed = 0;
    $failed = 0;
    
    foreach ($commands as $idx => $command) {
        $command = trim($command);
        if (empty($command)) continue;
        
        // Remover $$ do final
        $command = rtrim($command, '$;');
        
        try {
            $pdo->exec($command);
            $executed++;
            
            // Mostrar progresso
            if (stripos($command, 'ALTER TABLE') !== false) {
                preg_match('/ALTER TABLE\s+(\w+)/i', $command, $matches);
                echo "  ✓ Tabela alterada: " . ($matches[1] ?? 'unknown') . "\n";
            } elseif (stripos($command, 'CREATE TRIGGER') !== false) {
                preg_match('/CREATE TRIGGER\s+(\w+)/i', $command, $matches);
                echo "  ✓ Trigger criado: " . ($matches[1] ?? 'unknown') . "\n";
            } elseif (stripos($command, 'CREATE OR REPLACE VIEW') !== false) {
                preg_match('/CREATE OR REPLACE VIEW\s+(\w+)/i', $command, $matches);
                echo "  ✓ View criada: " . ($matches[1] ?? 'unknown') . "\n";
            } elseif (stripos($command, 'CREATE INDEX') !== false) {
                preg_match('/CREATE INDEX\s+(?:IF NOT EXISTS\s+)?(\w+)/i', $command, $matches);
                echo "  ✓ Índice criado: " . ($matches[1] ?? 'unknown') . "\n";
            } elseif (stripos($command, 'UPDATE') !== false) {
                echo "  ✓ Dados atualizados\n";
            }
            
        } catch (PDOException $e) {
            // Ignorar erros de "já existe"
            if (strpos($e->getMessage(), 'already exists') !== false || 
                strpos($e->getMessage(), 'Duplicate') !== false) {
                echo "  ⚠ Já existe (ignorado)\n";
            } else {
                $failed++;
                echo "  ✗ ERRO no comando #" . ($idx + 1) . ": " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n=======================================================\n";
    echo "RESULTADO\n";
    echo "=======================================================\n";
    echo "✓ Comandos executados com sucesso: {$executed}\n";
    if ($failed > 0) {
        echo "✗ Comandos com erro: {$failed}\n";
    }
    
    echo "\n✅ Migration concluída!\n";
    echo "\nVerificando views criadas...\n";
    
    $result = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW' AND Tables_in_{$dbName} LIKE 'vw_%'");
    $views = $result->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nViews disponíveis:\n";
    foreach ($views as $view) {
        echo "  • {$view}\n";
    }
    
    echo "\n🎉 Pronto! Sistema de alocação inteligente está ativo.\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
