-- 1. Tabela de Perguntas de Segurança
CREATE TABLE IF NOT EXISTS `security_questions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `question` varchar(255) NOT NULL,
    `is_active` tinyint(1) DEFAULT 1,
    `created_at` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed das perguntas (ignorando se já existirem para evitar duplicados em re-runs)
INSERT IGNORE INTO `security_questions` (`id`, `question`) VALUES 
(1, 'Qual o nome do seu primeiro animal de estimação?'),
(2, 'Qual o nome da sua escola primária?'),
(3, 'Qual o apelido de solteira da sua mãe?'),
(4, 'Em que cidade você nasceu?'),
(5, 'Qual seu filme favorito?'),
(6, 'Qual a marca do seu primeiro carro?');

-- 2. Respostas dos Utilizadores
CREATE TABLE IF NOT EXISTS `user_security_answers` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Alterar tabela users para adicionar métodos de recuperação
-- Usamos procedure para verificar se a coluna já existe antes de adicionar
DROP PROCEDURE IF EXISTS AddRecoveryColumns;
DELIMITER //
CREATE PROCEDURE AddRecoveryColumns()
BEGIN
    DECLARE col_exists INT;
    
    SELECT COUNT(*) INTO col_exists FROM information_schema.columns 
    WHERE table_name = 'users' AND column_name = 'recovery_keyword_hash' AND table_schema = DATABASE();
    
    IF col_exists = 0 THEN
        ALTER TABLE `users` ADD COLUMN `recovery_keyword_hash` varchar(255) DEFAULT NULL AFTER `password_hash`;
    END IF;

    SELECT COUNT(*) INTO col_exists FROM information_schema.columns 
    WHERE table_name = 'users' AND column_name = 'recovery_pin_hash' AND table_schema = DATABASE();
    
    IF col_exists = 0 THEN
        ALTER TABLE `users` ADD COLUMN `recovery_pin_hash` varchar(255) DEFAULT NULL AFTER `recovery_keyword_hash`;
    END IF;
END//
DELIMITER ;

CALL AddRecoveryColumns();
DROP PROCEDURE AddRecoveryColumns;
