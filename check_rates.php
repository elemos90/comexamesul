<?php
// Standalone script to check rates
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

    echo "Checking rates for vacancy ID: $vacancyId\n";

    // Check active rate
    $stmt = $db->prepare("SELECT * FROM payment_rates WHERE vacancy_id = ? AND ativo = 1 LIMIT 1");
    $stmt->execute([$vacancyId]);
    $active = $stmt->fetch();
    echo "Active Rate: " . ($active ? "Found (ID: {$active['id']})" : "Not Found") . "\n";
    if ($active)
        print_r($active);

    // Check all rates
    $stmt = $db->prepare("SELECT * FROM payment_rates WHERE vacancy_id = ?");
    $stmt->execute([$vacancyId]);
    $all = $stmt->fetchAll();
    echo "Total Rates for Vacancy: " . count($all) . "\n";
    if (count($all) > 0)
        print_r($all);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
