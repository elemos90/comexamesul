<?php
require_once __DIR__ . '/bootstrap.php';

use App\Models\User;

echo "Listando usuários cadastrados:\n";
echo "---------------------------------------------------\n";
echo str_pad("ID", 5) . str_pad("Nome", 30) . str_pad("Email", 40) . str_pad("Role", 15) . "\n";
echo "---------------------------------------------------\n";

$db = \App\Database\Connection::getInstance();
$stmt = $db->query("SELECT id, name, email, role, password FROM users ORDER BY id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    echo str_pad($user['id'], 5) .
        str_pad(substr($user['name'], 0, 28), 30) .
        str_pad($user['email'], 40) .
        str_pad($user['role'], 15) . "\n";
}
echo "---------------------------------------------------\n";
