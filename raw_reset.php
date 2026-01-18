<?php
try {
    $dsn = 'mysql:host=127.0.0.1;port=3306;dbname=comexamesul';
    $username = 'root';
    $password = '';

    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conectado ao banco.\n";

    // Hash password "password"
    $newHash = password_hash('password', PASSWORD_DEFAULT);

    // Update
    $sql = "UPDATE users SET password_hash = :p";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['p' => $newHash]);

    echo "Senhas resetadas com sucesso.\n";

    // List users
    $stmt = $pdo->query("SELECT email, role FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\nADMIN LOGIN:\n";
    foreach ($users as $u) {
        if (in_array($u['role'], ['admin', 'coordenador'])) {
            echo "Email: " . $u['email'] . "\n";
        }
    }

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
