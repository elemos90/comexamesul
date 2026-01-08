<?php
// Standalone script to debug payment calculation
$dsn = 'mysql:host=127.0.0.1;port=3306;dbname=comexamesul;charset=utf8mb4';
$username = 'root';
$password = '';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $db = new PDO($dsn, $username, $password, $options);
    $vacancyId = 10;

    echo "Debugging payment calculation for vacancy ID: $vacancyId\n";

    $sql = "SELECT 
                u.id as user_id,
                u.name as nome_completo,
                u.nuit,
                u.banco,
                u.numero_conta,
                u.email,
                SUM(p.is_vigilante) as nr_vigias,
                SUM(p.is_supervisor) as nr_supervisoes
            FROM (
                -- Vigilantes
                SELECT 
                    jv.vigilante_id as user_id,
                    1 as is_vigilante,
                    0 as is_supervisor
                FROM jury_vigilantes jv
                INNER JOIN juries j ON j.id = jv.jury_id
                WHERE j.vacancy_id = :vacancy_id
                
                UNION ALL
                
                -- Supervisores
                SELECT 
                    j.supervisor_id as user_id,
                    0 as is_vigilante,
                    1 as is_supervisor
                FROM juries j
                WHERE j.vacancy_id = :vacancy_id AND j.supervisor_id IS NOT NULL
            ) as p
            INNER JOIN users u ON u.id = p.user_id
            GROUP BY u.id, u.name, u.nuit, u.banco, u.numero_conta, u.email
            ORDER BY u.name";

    $stmt = $db->prepare($sql);
    $stmt->execute(['vacancy_id' => $vacancyId]);
    $results = $stmt->fetchAll();

    echo "Query executed successfully. Rows: " . count($results) . "\n";

    // Simulate loop
    $rates = [
        'valor_por_vigia' => 750.00,
        'valor_por_supervisao' => 1500.00
    ];
    $valorVigia = (float) ($rates['valor_por_vigia'] ?? 0);
    $valorSupervisao = (float) ($rates['valor_por_supervisao'] ?? 0);

    foreach ($results as &$row) {
        echo "Processing user {$row['user_id']} ({$row['nome_completo']})...\n";
        echo "  Vigias: " . var_export($row['nr_vigias'], true) . "\n";
        echo "  Supervisoes: " . var_export($row['nr_supervisoes'], true) . "\n";

        $row['valor_vigias'] = $row['nr_vigias'] * $valorVigia;
        $row['valor_supervisoes'] = $row['nr_supervisoes'] * $valorSupervisao;
        $row['total'] = $row['valor_vigias'] + $row['valor_supervisoes'];

        echo "  Total: {$row['total']}\n";
    }

    echo "Calculation completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
