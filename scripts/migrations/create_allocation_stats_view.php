<?php
/**
 * Migration: Criar view vw_allocation_stats
 * Data: 2025-10-12
 *
 * View para calcular estatísticas gerais de alocação
 */

require_once __DIR__ . '/../../bootstrap.php';

try {
    $db = database();

    echo "==============================================\n";
    echo "  MIGRATION: Criar vw_allocation_stats\n";
    echo "==============================================\n\n";

    // 1. Dropar view se existir
    echo "1️⃣ Removendo view antiga (se existir)...\n";
    $db->exec("DROP VIEW IF EXISTS vw_allocation_stats");
    echo "   ✅ View antiga removida\n\n";

    // 2. Criar view de estatísticas de alocação
    echo "2️⃣ Criando view vw_allocation_stats...\n";
    $db->exec("
        CREATE VIEW vw_allocation_stats AS
        SELECT 
            COUNT(DISTINCT j.id) as total_juries,
            SUM(CEILING(j.candidates_quota / 30)) as total_capacity,
            COUNT(DISTINCT jv.jury_id) as juries_with_vigilantes,
            SUM(CEILING(j.candidates_quota / 30)) - COUNT(jv.id) as slots_available,
            COUNT(DISTINCT CASE WHEN j.supervisor_id IS NOT NULL THEN j.id END) as juries_with_supervisor,
            COUNT(DISTINCT CASE WHEN j.supervisor_id IS NULL THEN j.id END) as juries_without_supervisor,
            IFNULL(AVG(vw.workload_score), 0) as avg_workload_score,
            IFNULL(STDDEV(vw.workload_score), 0) as workload_std_deviation,
            COUNT(DISTINCT u.id) - COUNT(DISTINCT jv.vigilante_id) as vigilantes_without_allocation,
            COUNT(jv.id) as total_allocated
        FROM juries j
        LEFT JOIN jury_vigilantes jv ON jv.jury_id = j.id
        LEFT JOIN vw_vigilante_workload vw ON vw.user_id = jv.vigilante_id
        LEFT JOIN users u ON u.role = 'vigilante' AND u.available_for_vigilance = 1
    ");
    echo "   ✅ View criada com sucesso\n\n";

    // 3. Testar view
    echo "3️⃣ Testando view...\n";
    $stmt = $db->query("SELECT * FROM vw_allocation_stats");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ View funcional:\n";
    echo "      - Total de júris: {$result['total_juries']}\n";
    echo "      - Capacidade total: {$result['total_capacity']}\n";
    echo "      - Vigilantes alocados: {$result['total_allocated']}\n";
    echo "      - Júris com supervisor: {$result['juries_with_supervisor']}\n\n";

    echo "==============================================\n";
    echo "✅ Migration concluída com sucesso!\n";
    echo "==============================================\n\n";

} catch (PDOException $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n\n";
    exit(1);
}
