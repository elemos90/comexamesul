<?php

require_once __DIR__ . '/../bootstrap.php';

echo "Criando tabelas de locais...\n\n";

$dbHost = env('DB_HOST', '127.0.0.1');
$dbPort = env('DB_PORT', '3306');
$dbName = env('DB_DATABASE', 'comexamesul');
$dbUser = env('DB_USERNAME', 'root');
$dbPass = env('DB_PASSWORD', '');

try {
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "Conectado ao banco: {$dbName}\n\n";
    
    // Criar location_templates
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS location_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            location VARCHAR(150) NOT NULL,
            description TEXT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_by INT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT fk_location_templates_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL,
            INDEX idx_location_templates_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Tabela location_templates criada\n";
    
    // Criar location_template_disciplines
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS location_template_disciplines (
            id INT AUTO_INCREMENT PRIMARY KEY,
            template_id INT NOT NULL,
            subject VARCHAR(180) NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            display_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            CONSTRAINT fk_template_disciplines_template FOREIGN KEY (template_id) REFERENCES location_templates (id) ON DELETE CASCADE,
            INDEX idx_template_disciplines_template (template_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Tabela location_template_disciplines criada\n";
    
    // Criar location_template_rooms
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS location_template_rooms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            discipline_id INT NOT NULL,
            room VARCHAR(60) NOT NULL,
            candidates_quota INT NOT NULL,
            created_at DATETIME NOT NULL,
            CONSTRAINT fk_template_rooms_discipline FOREIGN KEY (discipline_id) REFERENCES location_template_disciplines (id) ON DELETE CASCADE,
            INDEX idx_template_rooms_discipline (discipline_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Tabela location_template_rooms criada\n";
    
    // Criar location_stats
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS location_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            location VARCHAR(150) NOT NULL,
            exam_date DATE NOT NULL,
            total_juries INT NOT NULL DEFAULT 0,
            total_disciplines INT NOT NULL DEFAULT 0,
            total_candidates INT NOT NULL DEFAULT 0,
            total_vigilantes INT NOT NULL DEFAULT 0,
            total_supervisors INT NOT NULL DEFAULT 0,
            updated_at DATETIME NOT NULL,
            UNIQUE KEY uq_location_stats (location, exam_date),
            INDEX idx_location_stats_date (exam_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Tabela location_stats criada\n";
    
    echo "\n✅ SUCESSO! Todas as tabelas foram criadas.\n";
    echo "\nVerificando tabelas...\n";
    
    $result = $pdo->query("SHOW TABLES LIKE 'location%'");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nTabelas encontradas:\n";
    foreach ($tables as $table) {
        echo "  • {$table}\n";
    }
    
    echo "\n🎉 Pronto! Acesse o sistema e teste novamente.\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
