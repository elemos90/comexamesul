<?php
require_once __DIR__ . '/bootstrap.php';

use App\Models\User;

echo "Resetando senhas...\n";

$db = \App\Database\Connection::getInstance();
$passwordHash = password_hash('password', PASSWORD_DEFAULT);

// Update all users
$stmt = $db->prepare("UPDATE users SET password = :password");
$stmt->execute(['password' => $passwordHash]);

echo "Sucesso! Todas as senhas foram definidas para: 'password'\n\n";

echo "Usuários disponíveis:\n";
$stmt = $db->query("SELECT id, name, email, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    echo "Email: " . str_pad($user['email'], 40) . " | Role: " . $user['role'] . "\n";
}
