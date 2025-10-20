<?php

/**
 * Script para adicionar índices críticos ao banco de dados
 * 
 * Melhora significativamente a performance de queries frequentes
 * Execução: php scripts/add_critical_indexes.php
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Database\Connection;

echo "🔧 Adicionando índices críticos ao banco de dados...\n\n";

$db = Connection::getInstance();

$indexes = [
    [
        'name' => 'idx_juries_location_date',
        'table' => 'juries',
        'sql' => 'CREATE INDEX idx_juries_location_date ON juries(location_id, exam_date)',
        'description' => 'Otimiza queries de júris por local e data'
    ],
    [
        'name' => 'idx_juries_exam_lookup',
        'table' => 'juries',
        'sql' => 'CREATE INDEX idx_juries_exam_lookup ON juries(exam_date, start_time, subject)',
        'description' => 'Otimiza agrupamento por exame'
    ],
    [
        'name' => 'idx_users_role_available',
        'table' => 'users',
        'sql' => 'CREATE INDEX idx_users_role_available ON users(role, available_for_vigilance)',
        'description' => 'Otimiza busca de vigilantes disponíveis'
    ],
    [
        'name' => 'idx_jury_vigilantes_jury',
        'table' => 'jury_vigilantes',
        'sql' => 'CREATE INDEX idx_jury_vigilantes_jury ON jury_vigilantes(jury_id)',
        'description' => 'Otimiza lookup de vigilantes por júri'
    ],
    [
        'name' => 'idx_jury_vigilantes_vigilante',
        'table' => 'jury_vigilantes',
        'sql' => 'CREATE INDEX idx_jury_vigilantes_vigilante ON jury_vigilantes(vigilante_id)',
        'description' => 'Otimiza lookup de júris por vigilante'
    ],
    [
        'name' => 'idx_applications_status_vacancy',
        'table' => 'vacancy_applications',
        'sql' => 'CREATE INDEX idx_applications_status_vacancy ON vacancy_applications(status, vacancy_id)',
        'description' => 'Otimiza filtros de candidaturas'
    ],
    [
        'name' => 'idx_applications_user',
        'table' => 'vacancy_applications',
        'sql' => 'CREATE INDEX idx_applications_user ON vacancy_applications(user_id, status)',
        'description' => 'Otimiza candidaturas por usuário'
    ],
];

$added = 0;
$skipped = 0;
$errors = 0;

foreach ($indexes as $index) {
    try {
        // Verificar se índice já existe
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = DATABASE() 
            AND table_name = :table 
            AND index_name = :index
        ");
        $stmt->execute([
            'table' => $index['table'],
            'index' => $index['name']
        ]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
        
        if ($exists) {
            echo "⏭️  {$index['name']}: Já existe\n";
            $skipped++;
            continue;
        }
        
        // Criar índice
        $db->exec($index['sql']);
        echo "✅ {$index['name']}: Criado com sucesso\n";
        echo "   → {$index['description']}\n";
        $added++;
        
    } catch (PDOException $e) {
        echo "❌ {$index['name']}: Erro - {$e->getMessage()}\n";
        $errors++;
    }
    
    echo "\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "📊 Resumo:\n";
echo "   ✅ Índices adicionados: {$added}\n";
echo "   ⏭️  Índices já existentes: {$skipped}\n";
echo "   ❌ Erros: {$errors}\n";
echo str_repeat('=', 60) . "\n";

if ($added > 0) {
    echo "\n🎉 Performance melhorada! Execute ANALYZE TABLE para otimizar.\n";
}

echo "\n✨ Concluído!\n";
