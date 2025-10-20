<?php

require __DIR__ . '/../bootstrap.php';

use App\Utils\Env;

$host = env('REMOTE_INSTALL_HOST');
$database = env('REMOTE_INSTALL_DB');
$user = env('REMOTE_INSTALL_USER');
$password = env('REMOTE_INSTALL_PASS');

if (!$host || !$database || !$user) {
    fwrite(STDERR, "Credenciais remotas não definidas no .env.\n");
    exit(1);
}

try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $database);
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => true,
    ]);
} catch (PDOException $exception) {
    fwrite(STDERR, "Falha na ligação: " . $exception->getMessage() . PHP_EOL);
    exit(1);
}

function runSqlFile(PDO $pdo, string $path): void
{
    if (!file_exists($path)) {
        throw new RuntimeException('Ficheiro não encontrado: ' . $path);
    }
    $sql = file_get_contents($path);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $statement) {
        if ($statement === '') {
            continue;
        }
        $pdo->exec($statement);
    }
}

try {
    runSqlFile($pdo, base_path('app/Database/migrations.sql'));
    echo "Tabelas criadas/atualizadas com sucesso." . PHP_EOL;
    runSqlFile($pdo, base_path('app/Database/seed.sql'));
    echo "Dados de exemplo inseridos." . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'Erro ao executar SQL: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

echo "Instalação concluída na base remota {$database}." . PHP_EOL;
