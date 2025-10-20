<?php
/**
 * Instalação Completa v2.5 - Manual
 */

echo "==============================================\n";
echo "INSTALAÇÃO COMPLETA v2.5\n";
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
    
    // 1. Criar tabela application_status_history
    echo "1. Criando tabela application_status_history...\n";
    try {
        $sql = "CREATE TABLE IF NOT EXISTS application_status_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            application_id INT NOT NULL,
            old_status ENUM('pendente', 'aprovada', 'rejeitada', 'cancelada') NULL,
            new_status ENUM('pendente', 'aprovada', 'rejeitada', 'cancelada') NOT NULL,
            changed_by INT NULL,
            changed_at DATETIME NOT NULL,
            reason TEXT NULL,
            metadata JSON NULL,
            FOREIGN KEY (application_id) REFERENCES vacancy_applications(id) ON DELETE CASCADE,
            FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_application (application_id),
            INDEX idx_date (changed_at),
            INDEX idx_changed_by (changed_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($sql);
        echo "✅ Tabela application_status_history criada\n\n";
    } catch (PDOException $e) {
        echo "⚠️  " . $e->getMessage() . "\n\n";
    }
    
    // 2. Criar tabela email_notifications
    echo "2. Criando tabela email_notifications...\n";
    try {
        $sql = "CREATE TABLE IF NOT EXISTS email_notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            body TEXT NOT NULL,
            status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
            sent_at DATETIME NULL,
            error_message TEXT NULL,
            retry_count INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_status (status),
            INDEX idx_type (type),
            INDEX idx_user (user_id),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($sql);
        echo "✅ Tabela email_notifications criada\n\n";
    } catch (PDOException $e) {
        echo "⚠️  " . $e->getMessage() . "\n\n";
    }
    
    // 3. Criar view v_application_stats
    echo "3. Criando view v_application_stats...\n";
    try {
        $sql = "CREATE OR REPLACE VIEW v_application_stats AS
        SELECT 
            COUNT(*) as total_applications,
            SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN status = 'aprovada' THEN 1 ELSE 0 END) as approved_count,
            SUM(CASE WHEN status = 'rejeitada' THEN 1 ELSE 0 END) as rejected_count,
            SUM(CASE WHEN status = 'cancelada' THEN 1 ELSE 0 END) as cancelled_count,
            ROUND(
                SUM(CASE WHEN status = 'aprovada' THEN 1 ELSE 0 END) * 100.0 / 
                NULLIF(SUM(CASE WHEN status IN ('aprovada', 'rejeitada') THEN 1 ELSE 0 END), 0),
                2
            ) as approval_rate,
            ROUND(
                AVG(CASE 
                    WHEN reviewed_at IS NOT NULL 
                    THEN TIMESTAMPDIFF(HOUR, applied_at, reviewed_at) 
                    ELSE NULL 
                END),
                1
            ) as avg_review_hours,
            SUM(reapply_count) as total_reapplies
        FROM vacancy_applications";
        $pdo->exec($sql);
        echo "✅ View v_application_stats criada\n\n";
    } catch (PDOException $e) {
        echo "⚠️  " . $e->getMessage() . "\n\n";
    }
    
    // 4. Criar view v_top_vigilantes
    echo "4. Criando view v_top_vigilantes...\n";
    try {
        $sql = "CREATE OR REPLACE VIEW v_top_vigilantes AS
        SELECT 
            u.id,
            u.name,
            u.email,
            COUNT(va.id) as total_applications,
            SUM(CASE WHEN va.status = 'aprovada' THEN 1 ELSE 0 END) as approved_count,
            SUM(CASE WHEN va.status = 'rejeitada' THEN 1 ELSE 0 END) as rejected_count,
            SUM(CASE WHEN va.status = 'cancelada' THEN 1 ELSE 0 END) as cancelled_count,
            SUM(va.reapply_count) as total_reapplies
        FROM users u
        INNER JOIN vacancy_applications va ON u.id = va.vigilante_id
        WHERE u.role = 'vigilante'
        GROUP BY u.id, u.name, u.email
        ORDER BY total_applications DESC
        LIMIT 10";
        $pdo->exec($sql);
        echo "✅ View v_top_vigilantes criada\n\n";
    } catch (PDOException $e) {
        echo "⚠️  " . $e->getMessage() . "\n\n";
    }
    
    // 5. Criar view v_applications_by_day
    echo "5. Criando view v_applications_by_day...\n";
    try {
        $sql = "CREATE OR REPLACE VIEW v_applications_by_day AS
        SELECT 
            DATE(applied_at) as date,
            COUNT(*) as count,
            SUM(CASE WHEN status = 'aprovada' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejeitada' THEN 1 ELSE 0 END) as rejected
        FROM vacancy_applications
        WHERE applied_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(applied_at)
        ORDER BY date DESC";
        $pdo->exec($sql);
        echo "✅ View v_applications_by_day criada\n\n";
    } catch (PDOException $e) {
        echo "⚠️  " . $e->getMessage() . "\n\n";
    }
    
    // 6. Popular histórico para candidaturas existentes
    echo "6. Populando histórico retroativo...\n";
    try {
        $sql = "INSERT INTO application_status_history (application_id, old_status, new_status, changed_by, changed_at, reason)
        SELECT 
            id,
            NULL,
            status,
            vigilante_id,
            applied_at,
            'Migração v2.5 - Registro retroativo'
        FROM vacancy_applications
        WHERE id NOT IN (SELECT DISTINCT application_id FROM application_status_history)";
        $pdo->exec($sql);
        $count = $pdo->query("SELECT ROW_COUNT()")->fetchColumn();
        echo "✅ $count registros de histórico criados\n\n";
    } catch (PDOException $e) {
        echo "⚠️  " . $e->getMessage() . "\n\n";
    }
    
    echo "==============================================\n";
    echo "✅ INSTALAÇÃO COMPLETA CONCLUÍDA!\n";
    echo "==============================================\n\n";
    
    // Verificação
    echo "Verificando instalação:\n";
    $tables = ['application_status_history', 'email_notifications'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        echo ($stmt->rowCount() > 0 ? "✅" : "❌") . " Tabela $table\n";
    }
    
    $views = ['v_application_stats', 'v_top_vigilantes', 'v_applications_by_day'];
    foreach ($views as $view) {
        $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_Type = 'VIEW' AND Tables_in_$dbName = '$view'");
        echo ($stmt->rowCount() > 0 ? "✅" : "❌") . " View $view\n";
    }
    
    echo "\n✅ Dashboard pronto para usar: http://localhost/applications/dashboard\n\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n\n";
    exit(1);
}
