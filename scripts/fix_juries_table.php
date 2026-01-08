<?php
/**
 * CORREÇÃO: Adicionar colunas à tabela juries
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Database\Connection;

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>";
echo "<style>body{font-family:sans-serif;padding:20px;background:#f5f5f5;}";
echo ".box{background:white;padding:20px;margin:10px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}";
echo ".success{color:#10b981;font-weight:bold;}";
echo ".error{color:#ef4444;font-weight:bold;}";
echo "pre{background:#f9fafb;padding:10px;border-radius:4px;overflow-x:auto;}";
echo "</style></head><body>";

echo "<h1>🔧 Correção da Tabela Juries</h1>";

try {
    $db = Connection::getInstance();
    
    echo "<div class='box'>";
    echo "<h2>📋 Verificando colunas existentes...</h2>";
    
    // Verificar colunas atuais
    $columns = $db->query("DESCRIBE juries")->fetchAll(PDO::FETCH_ASSOC);
    $existingColumns = array_column($columns, 'Field');
    
    echo "<p>Colunas atuais: " . implode(', ', $existingColumns) . "</p>";
    
    $columnsToAdd = ['discipline_id', 'location_id', 'room_id'];
    $columnsAdded = [];
    $errors = [];
    
    foreach ($columnsToAdd as $column) {
        if (!in_array($column, $existingColumns)) {
            echo "<p>➕ Adicionando coluna: <strong>{$column}</strong>...</p>";
            
            try {
                $sql = "ALTER TABLE juries ADD COLUMN {$column} INT NULL";
                $db->exec($sql);
                $columnsAdded[] = $column;
                echo "<p class='success'>✅ Coluna {$column} adicionada com sucesso!</p>";
            } catch (PDOException $e) {
                $errors[] = "Erro ao adicionar {$column}: " . $e->getMessage();
                echo "<p class='error'>❌ Erro ao adicionar {$column}: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>✓ Coluna <strong>{$column}</strong> já existe.</p>";
        }
    }
    
    echo "</div>";
    
    // Tentar adicionar foreign keys (opcional)
    echo "<div class='box'>";
    echo "<h2>🔗 Adicionando Foreign Keys (opcional)...</h2>";
    
    try {
        $db->exec("ALTER TABLE juries ADD CONSTRAINT fk_juries_discipline FOREIGN KEY (discipline_id) REFERENCES disciplines (id) ON DELETE SET NULL");
        echo "<p class='success'>✅ FK discipline_id adicionada</p>";
    } catch (PDOException $e) {
        echo "<p style='color:#f59e0b;'>⚠️ FK discipline_id: " . $e->getMessage() . "</p>";
    }
    
    try {
        $db->exec("ALTER TABLE juries ADD CONSTRAINT fk_juries_location FOREIGN KEY (location_id) REFERENCES exam_locations (id) ON DELETE SET NULL");
        echo "<p class='success'>✅ FK location_id adicionada</p>";
    } catch (PDOException $e) {
        echo "<p style='color:#f59e0b;'>⚠️ FK location_id: " . $e->getMessage() . "</p>";
    }
    
    try {
        $db->exec("ALTER TABLE juries ADD CONSTRAINT fk_juries_room FOREIGN KEY (room_id) REFERENCES exam_rooms (id) ON DELETE SET NULL");
        echo "<p class='success'>✅ FK room_id adicionada</p>";
    } catch (PDOException $e) {
        echo "<p style='color:#f59e0b;'>⚠️ FK room_id: " . $e->getMessage() . "</p>";
    }
    
    echo "</div>";
    
    // Verificação final
    echo "<div class='box'>";
    echo "<h2>✅ Verificação Final</h2>";
    
    $columns = $db->query("DESCRIBE juries")->fetchAll(PDO::FETCH_ASSOC);
    $existingColumns = array_column($columns, 'Field');
    
    $allOk = true;
    foreach ($columnsToAdd as $column) {
        if (in_array($column, $existingColumns)) {
            echo "<p class='success'>✅ {$column} - OK</p>";
        } else {
            echo "<p class='error'>❌ {$column} - FALTANDO</p>";
            $allOk = false;
        }
    }
    
    if ($allOk) {
        echo "<div style='background:#d1fae5;padding:15px;border-radius:8px;margin-top:20px;'>";
        echo "<h3 style='color:#065f46;margin:0 0 10px 0;'>🎉 Correção Concluída!</h3>";
        echo "<p style='color:#065f46;margin:0;'>Todas as colunas necessárias foram adicionadas.</p>";
        echo "</div>";
        
        echo "<div style='margin-top:20px;'>";
        echo "<a href='ver_disciplinas.php' style='display:inline-block;padding:12px 24px;background:#667eea;color:white;text-decoration:none;border-radius:8px;font-weight:bold;'>📚 Acessar Disciplinas</a> ";
        echo "<a href='dashboard_direto.php' style='display:inline-block;padding:12px 24px;background:#10b981;color:white;text-decoration:none;border-radius:8px;font-weight:bold;'>🏠 Voltar ao Dashboard</a>";
        echo "</div>";
    } else {
        echo "<div style='background:#fee2e2;padding:15px;border-radius:8px;margin-top:20px;'>";
        echo "<h3 style='color:#991b1b;margin:0 0 10px 0;'>❌ Erro na Correção</h3>";
        echo "<p style='color:#991b1b;margin:0;'>Algumas colunas não foram adicionadas. Verifique as permissões do banco de dados.</p>";
        echo "</div>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='box'>";
    echo "<h2 class='error'>❌ Erro Crítico</h2>";
    echo "<p>Não foi possível conectar ao banco de dados ou executar as alterações.</p>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "</div>";
}

echo "</body></html>";
?>
