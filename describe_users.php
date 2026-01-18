<?php
try {
    $dsn = 'mysql:host=127.0.0.1;port=3306;dbname=comexamesul';
    $pdo = new PDO($dsn, 'root', '');
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
