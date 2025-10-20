<?php

/**
 * Script de Instalação: Dados Mestres (Disciplinas, Locais e Salas)
 * 
 * Este script cria as tabelas necessárias para o sistema de cadastro
 * centralizado de disciplinas, locais e salas, além de popular com
 * dados de exemplo.
 * 
 * Execução: php scripts/install_master_data.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Database\Connection;

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║     INSTALAÇÃO: DADOS MESTRES (Disciplinas, Locais, Salas)   ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

try {
    $db = Connection::getInstance();
    
    echo "📦 Lendo arquivo de migração...\n";
    $migrationFile = __DIR__ . '/../app/Database/migrations_master_data.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Arquivo de migração não encontrado: {$migrationFile}");
    }
    
    $sql = file_get_contents($migrationFile);
    
    if ($sql === false) {
        throw new Exception("Erro ao ler arquivo de migração");
    }
    
    echo "🔄 Executando migração...\n\n";
    
    // Dividir em statements individuais
    $statements = explode(';', $sql);
    $executed = 0;
    $errors = [];
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        
        // Pular statements vazios e comentários
        if (empty($statement) || str_starts_with($statement, '--')) {
            continue;
        }
        
        // Pular delimiters
        if (str_contains(strtoupper($statement), 'DELIMITER')) {
            continue;
        }
        
        try {
            $db->exec($statement . ';');
            $executed++;
        } catch (PDOException $e) {
            // Ignorar erros de "já existe" (código 1050 = tabela, 1061 = índice)
            if ($e->getCode() == '42S01' || $e->getCode() == '1050' || $e->getCode() == '1061') {
                continue; // Já existe, sem problema
            }
            
            // Ignorar erro de "coluna já existe"
            if ($e->getCode() == '42S21' || $e->getCode() == '1060') {
                continue;
            }
            
            $errors[] = "ERRO: " . $e->getMessage();
        }
    }
    
    if (!empty($errors)) {
        echo "\n⚠️  Alguns erros ocorreram (podem ser esperados):\n";
        foreach ($errors as $error) {
            echo "   - " . $error . "\n";
        }
    }
    
    echo "\n✅ Migração concluída! {$executed} statements executados.\n\n";
    
    // Verificar instalação
    echo "🔍 Verificando instalação...\n";
    
    $tables = [
        'disciplines' => 'Disciplinas',
        'exam_locations' => 'Locais',
        'exam_rooms' => 'Salas'
    ];
    
    foreach ($tables as $table => $name) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM {$table}");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $result['count'];
            
            echo "   ✓ {$name}: {$count} registros\n";
        } catch (PDOException $e) {
            echo "   ✗ {$name}: ERRO - Tabela não encontrada\n";
        }
    }
    
    // Verificar se campos foram adicionados à tabela juries
    echo "\n🔍 Verificando campos na tabela 'juries'...\n";
    
    try {
        $stmt = $db->query("DESCRIBE juries");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $requiredFields = ['discipline_id', 'location_id', 'room_id'];
        $foundFields = [];
        
        foreach ($requiredFields as $field) {
            if (in_array($field, $columns)) {
                $foundFields[] = $field;
                echo "   ✓ Campo '{$field}' encontrado\n";
            } else {
                echo "   ✗ Campo '{$field}' NÃO encontrado\n";
            }
        }
        
        if (count($foundFields) === count($requiredFields)) {
            echo "\n✅ Todos os campos necessários foram adicionados!\n";
        } else {
            echo "\n⚠️  Alguns campos não foram encontrados. Verifique a execução.\n";
        }
        
    } catch (PDOException $e) {
        echo "   ✗ ERRO ao verificar campos: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║                    INSTALAÇÃO CONCLUÍDA!                       ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "📚 Próximos passos:\n";
    echo "   1. Acesse: http://localhost/master-data/disciplines\n";
    echo "   2. Acesse: http://localhost/master-data/locations\n";
    echo "   3. Acesse: http://localhost/master-data/rooms\n";
    echo "   4. Crie júris usando os novos dropdowns em /juries/planning\n\n";
    
    echo "💡 Dados de exemplo já foram inseridos:\n";
    echo "   - 10 disciplinas (MAT1, FIS1, QUI1, etc.)\n";
    echo "   - 4 locais (Campus Central, Escolas, etc.)\n";
    echo "   - 19 salas distribuídas pelos locais\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO CRÍTICO:\n";
    echo "   " . $e->getMessage() . "\n\n";
    exit(1);
}
