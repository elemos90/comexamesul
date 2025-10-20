<?php
require_once __DIR__ . '/../bootstrap.php';

try {
    $db = database();
    
    echo "=== VIEWS CRIADAS ===\n";
    $result = $db->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
    $views = $result->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($views as $view) {
        echo "✓ " . $view . "\n";
    }
    
    echo "\nTotal: " . count($views) . " views\n";
    
    echo "\n=== TRIGGERS CRIADOS ===\n";
    $result = $db->query("SHOW TRIGGERS");
    $triggers = $result->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($triggers as $trigger) {
        echo "✓ " . $trigger['Trigger'] . " (tabela: " . $trigger['Table'] . ")\n";
    }
    
    echo "\nTotal: " . count($triggers) . " triggers\n";
    
    echo "\n=== CAMPOS NOVOS EM JURIES ===\n";
    $result = $db->query("SHOW COLUMNS FROM juries LIKE 'vigilantes_capacity'");
    if ($result->rowCount() > 0) {
        echo "✓ vigilantes_capacity\n";
    } else {
        echo "✗ vigilantes_capacity (FALTA)\n";
    }
    
    $result = $db->query("SHOW COLUMNS FROM juries LIKE 'requires_supervisor'");
    if ($result->rowCount() > 0) {
        echo "✓ requires_supervisor\n";
    } else {
        echo "✗ requires_supervisor (FALTA)\n";
    }
    
    echo "\n✅ VERIFICAÇÃO CONCLUÍDA!\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
