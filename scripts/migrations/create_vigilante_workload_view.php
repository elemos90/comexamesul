<?php
/**
 * Migration: Criar view vw_vigilante_workload
 * Data: 2025-10-12
 *
 * View para calcular carga de trabalho dos vigilantes
 */

require_once __DIR__ . '/../../bootstrap.php';

try {
    $db = database();

    echo "==============================================\n";
    echo "  MIGRATION: Criar vw_vigilante_workload\n";
    echo "==============================================\n\n";

    // 1. Dropar view se existir
    echo "1️⃣ Removendo view antiga (se existir)...\n";
    $db->exec("DROP VIEW IF EXISTS vw_vigilante_workload");
    echo "   ✅ View antiga removida\n\n";

    // 2. Criar view de workload
    echo "2️⃣ Criando view vw_vigilante_workload...\n";
    $db->exec("
        CREATE VIEW vw_vigilante_workload AS
        SELECT 
            u.id as user_id,
            u.name,
            u.email,
            COUNT(DISTINCT jv.jury_id) as workload_count,
            COUNT(DISTINCT js.id) as supervision_count,
            SUM(
                TIMESTAMPDIFF(MINUTE, j.start_time, j.end_time)
            ) as workload_minutes,
            SUM(
                TIMESTAMPDIFF(MINUTE, j.start_time, j.end_time) / 60.0
            ) + COUNT(DISTINCT js.id) * 2 as workload_score
        FROM users u
        LEFT JOIN jury_vigilantes jv ON jv.vigilante_id = u.id
        LEFT JOIN juries j ON j.id = jv.jury_id
        LEFT JOIN juries js ON js.supervisor_id = u.id
        WHERE (u.role = 'vigilante' AND u.available_for_vigilance = 1)
           OR u.supervisor_eligible = 1
        GROUP BY u.id, u.name, u.email
    ");
    echo "   ✅ View criada com sucesso\n\n";

    // 3. Testar view
    echo "3️⃣ Testando view...\n";
    $stmt = $db->query("SELECT COUNT(*) as total FROM vw_vigilante_workload");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ View funcional - {$result['total']} vigilantes encontrados\n\n";

    echo "==============================================\n";
    echo "✅ Migration concluída com sucesso!\n";
    echo "==============================================\n\n";

} catch (PDOException $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n\n";
    exit(1);
}
