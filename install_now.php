<?php
/**
 * Instalação Rápida v2.5 - Apenas Colunas Essenciais
 */

echo "==============================================\n";
echo "INSTALAÇÃO RÁPIDA v2.5 - Colunas Essenciais\n";
echo "==============================================\n\n";

// Ler .env
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    die("Arquivo .env não encontrado!\n");
}

$env = parse_ini_file($envFile);
$dbHost = $env['DB_HOST'] ?? '127.0.0.1';
$dbName = $env['DB_DATABASE'] ?? 'comexamesul';
$dbUser = $env['DB_USERNAME'] ?? 'root';
$dbPass = $env['DB_PASSWORD'] ?? '';

echo "Conectando ao banco: $dbHost / $dbName\n\n";

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Conectado ao banco de dados\n\n";
    
    // 1. Adicionar coluna rejection_reason
    echo "Adicionando coluna rejection_reason...\n";
    try {
        $pdo->exec("ALTER TABLE vacancy_applications ADD COLUMN rejection_reason TEXT NULL AFTER reviewed_by");
        echo "✅ Coluna rejection_reason adicionada\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "⚠️  Coluna rejection_reason já existe\n";
        } else {
            throw $e;
        }
    }
    
    // 2. Adicionar coluna reapply_count
    echo "Adicionando coluna reapply_count...\n";
    try {
        $pdo->exec("ALTER TABLE vacancy_applications ADD COLUMN reapply_count INT DEFAULT 0 AFTER updated_at");
        echo "✅ Coluna reapply_count adicionada\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "⚠️  Coluna reapply_count já existe\n";
        } else {
            throw $e;
        }
    }
    
    // 3. Adicionar índice
    echo "Adicionando índice...\n";
    try {
        $pdo->exec("ALTER TABLE vacancy_applications ADD INDEX idx_reapply_count (reapply_count)");
        echo "✅ Índice adicionado\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key') !== false) {
            echo "⚠️  Índice já existe\n";
        } else {
            echo "⚠️  " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n==============================================\n";
    echo "✅ INSTALAÇÃO CONCLUÍDA!\n";
    echo "==============================================\n\n";
    echo "Agora você pode:\n";
    echo "1. Atualizar a página de candidaturas\n";
    echo "2. Executar instalação completa depois: php scripts/install_v2.5_improvements.php\n\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n\n";
    exit(1);
}
