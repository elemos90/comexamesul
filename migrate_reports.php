<?php

// Standalone migration script with hardcoded credentials (dev environment defaults)
$dsn = 'mysql:host=127.0.0.1;port=3306;dbname=comexamesul;charset=utf8mb4';
$username = 'root';
$password = '';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    echo "Connecting to database...\n";
    $db = new PDO($dsn, $username, $password, $options);

    echo "Checking exam_reports table...\n";

    // Check if columns exist
    $stmt = $db->query("SHOW COLUMNS FROM exam_reports");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('fraudes_m', $columns)) {
        echo "Adding fraudes_m column...\n";
        $db->exec("ALTER TABLE exam_reports ADD COLUMN fraudes_m INT DEFAULT 0 AFTER absent_f");
    } else {
        echo "Column fraudes_m already exists.\n";
    }

    if (!in_array('fraudes_f', $columns)) {
        echo "Adding fraudes_f column...\n";
        $db->exec("ALTER TABLE exam_reports ADD COLUMN fraudes_f INT DEFAULT 0 AFTER fraudes_m");
    } else {
        echo "Column fraudes_f already exists.\n";
    }

    if (!in_array('role', $columns)) {
        echo "Adding role column...\n";
        $db->exec("ALTER TABLE exam_reports ADD COLUMN role VARCHAR(50) DEFAULT 'supervisor' AFTER supervisor_id");
    } else {
        echo "Column role already exists.\n";
    }

    echo "Migration completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}