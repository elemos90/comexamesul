<?php
require_once __DIR__ . '/../bootstrap.php';

// Override bootstrap settings for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use App\Database\Connection;

echo "Running Migration: Account Recovery...\n";

try {
    $db = Connection::getInstance();

    // REVISED STRATEGY: 
    // 1. Create tables directly.
    // 2. Try-catch ALTER TABLE.

    $statements = [
        "CREATE TABLE IF NOT EXISTS `security_questions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `question` varchar(255) NOT NULL,
            `is_active` tinyint(1) DEFAULT 1,
            `created_at` datetime DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "INSERT IGNORE INTO `security_questions` (`id`, `question`) VALUES 
        (1, 'Qual o nome do seu primeiro animal de estimação?'),
        (2, 'Qual o nome da sua escola primária?'),
        (3, 'Qual o apelido de solteira da sua mãe?'),
        (4, 'Em que cidade você nasceu?'),
        (5, 'Qual seu filme favorito?'),
        (6, 'Qual a marca do seu primeiro carro?');",

        "CREATE TABLE IF NOT EXISTS `user_security_answers` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `question_id` int(11) NOT NULL,
            `answer_hash` varchar(255) NOT NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `question_id` (`question_id`),
            CONSTRAINT `fk_usa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_usa_question` FOREIGN KEY (`question_id`) REFERENCES `security_questions` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ];

    foreach ($statements as $stmt) {
        $db->exec($stmt);
    }
    echo "Tables created/verified.\n";

    // Alter table safely
    try {
        $db->exec("ALTER TABLE `users` ADD COLUMN `recovery_keyword_hash` varchar(255) DEFAULT NULL AFTER `password_hash`");
        echo "Added recovery_keyword_hash.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false || strpos($e->getMessage(), '1060') !== false) {
            echo "Column recovery_keyword_hash already exists.\n";
        } else {
            throw $e;
        }
    }

    try {
        $db->exec("ALTER TABLE `users` ADD COLUMN `recovery_pin_hash` varchar(255) DEFAULT NULL AFTER `recovery_keyword_hash`");
        echo "Added recovery_pin_hash.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false || strpos($e->getMessage(), '1060') !== false) {
            echo "Column recovery_pin_hash already exists.\n";
        } else {
            throw $e;
        }
    }

    echo "\nMigration completed successfully!";

} catch (Exception $e) {
    echo "\nError: " . $e->getMessage();
}