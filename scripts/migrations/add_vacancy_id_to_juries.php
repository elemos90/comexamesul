<?php
/**
 * Migration: Adicionar vacancy_id à tabela juries
 * Data: 2025-10-12
 * 
 * Vincula júris a vagas específicas para filtrar vigilantes elegíveis
 */

require_once __DIR__ . '/../../bootstrap.php';

try {
    $db = database();
    
    echo "==============================================\n";
    echo "  MIGRATION: Adicionar vacancy_id a juries\n";
    echo "==============================================\n\n";
    
    // 1. Adicionar coluna vacancy_id
    echo "1️⃣ Adicionando coluna vacancy_id...\n";
    $db->exec("
        ALTER TABLE juries 
        ADD COLUMN vacancy_id INT NULL 
        AFTER id
    ");
    echo "   ✅ Coluna adicionada\n\n";
    
    // 2. Adicionar foreign key
    echo "2️⃣ Criando foreign key...\n";
    $db->exec("
        ALTER TABLE juries 
        ADD CONSTRAINT fk_juries_vacancy 
        FOREIGN KEY (vacancy_id) 
        REFERENCES exam_vacancies(id) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE
    ");
    echo "   ✅ Foreign key criada\n\n";
    
    // 3. Adicionar índice para performance
    echo "3️⃣ Criando índice...\n";
    $db->exec("
        CREATE INDEX idx_juries_vacancy_date 
        ON juries(vacancy_id, exam_date)
    ");
    echo "   ✅ Índice criado\n\n";
    
    echo "==============================================\n";
    echo "✅ Migration concluída com sucesso!\n";
    echo "==============================================\n\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
    
    // Se erro for "coluna já existe", ignorar
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "\nℹ️  Coluna vacancy_id já existe. Migration já foi executada.\n\n";
        exit(0);
    }
    
    exit(1);
}
