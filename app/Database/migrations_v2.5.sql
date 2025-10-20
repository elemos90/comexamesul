-- ==========================================
-- MIGRATIONS v2.5 - Melhorias de Candidaturas
-- Data: 11/10/2025
-- ==========================================

-- 1. HISTÓRICO DE STATUS DAS CANDIDATURAS
-- Rastreamento completo de todas as mudanças de status
CREATE TABLE IF NOT EXISTS application_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    old_status ENUM('pendente', 'aprovada', 'rejeitada', 'cancelada') NULL,
    new_status ENUM('pendente', 'aprovada', 'rejeitada', 'cancelada') NOT NULL,
    changed_by INT NULL COMMENT 'ID do usuário que fez a mudança',
    changed_at DATETIME NOT NULL,
    reason TEXT NULL COMMENT 'Motivo da mudança (opcional)',
    metadata JSON NULL COMMENT 'Dados adicionais da mudança',
    FOREIGN KEY (application_id) REFERENCES vacancy_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_application (application_id),
    INDEX idx_date (changed_at),
    INDEX idx_changed_by (changed_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. ADICIONAR CAMPOS À TABELA DE CANDIDATURAS
-- Motivo de rejeição e contador de recandidaturas
ALTER TABLE vacancy_applications 
ADD COLUMN IF NOT EXISTS rejection_reason TEXT NULL COMMENT 'Motivo da rejeição (visível para o vigilante)' AFTER reviewed_by,
ADD COLUMN IF NOT EXISTS reapply_count INT DEFAULT 0 COMMENT 'Número de vezes que se recandidatou' AFTER updated_at,
ADD INDEX IF NOT EXISTS idx_reapply_count (reapply_count);

-- 3. NOTIFICAÇÕES POR EMAIL
-- Sistema de fila de emails
CREATE TABLE IF NOT EXISTS email_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL COMMENT 'Tipo da notificação (ex: application_approved)',
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    sent_at DATETIME NULL,
    error_message TEXT NULL COMMENT 'Mensagem de erro se falhar',
    retry_count INT DEFAULT 0 COMMENT 'Número de tentativas de envio',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TRIGGER: Registrar mudanças de status automaticamente
DROP TRIGGER IF EXISTS trg_application_status_history;

DELIMITER $$
CREATE TRIGGER trg_application_status_history
AFTER UPDATE ON vacancy_applications
FOR EACH ROW
BEGIN
    -- Registrar apenas quando o status mudar
    IF OLD.status != NEW.status THEN
        INSERT INTO application_status_history 
            (application_id, old_status, new_status, changed_by, changed_at, reason)
        VALUES 
            (NEW.id, OLD.status, NEW.status, NEW.reviewed_by, NOW(), NEW.rejection_reason);
    END IF;
END$$
DELIMITER ;

-- 5. TRIGGER: Registrar criação inicial no histórico
DROP TRIGGER IF EXISTS trg_application_status_history_insert;

DELIMITER $$
CREATE TRIGGER trg_application_status_history_insert
AFTER INSERT ON vacancy_applications
FOR EACH ROW
BEGIN
    -- Registrar criação da candidatura
    INSERT INTO application_status_history 
        (application_id, old_status, new_status, changed_by, changed_at, reason)
    VALUES 
        (NEW.id, NULL, NEW.status, NEW.vigilante_id, NEW.applied_at, 'Candidatura inicial');
END$$
DELIMITER ;

-- 6. VIEW: Estatísticas de candidaturas para dashboard
CREATE OR REPLACE VIEW v_application_stats AS
SELECT 
    COUNT(*) as total_applications,
    SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN status = 'aprovada' THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN status = 'rejeitada' THEN 1 ELSE 0 END) as rejected_count,
    SUM(CASE WHEN status = 'cancelada' THEN 1 ELSE 0 END) as cancelled_count,
    -- Taxa de aprovação (aprovadas / total revisadas)
    ROUND(
        SUM(CASE WHEN status = 'aprovada' THEN 1 ELSE 0 END) * 100.0 / 
        NULLIF(SUM(CASE WHEN status IN ('aprovada', 'rejeitada') THEN 1 ELSE 0 END), 0),
        2
    ) as approval_rate,
    -- Tempo médio de revisão em horas
    ROUND(
        AVG(CASE 
            WHEN reviewed_at IS NOT NULL 
            THEN TIMESTAMPDIFF(HOUR, applied_at, reviewed_at) 
            ELSE NULL 
        END),
        1
    ) as avg_review_hours,
    -- Total de recandidaturas
    SUM(reapply_count) as total_reapplies
FROM vacancy_applications;

-- 7. VIEW: Vigilantes mais ativos (por número de candidaturas)
CREATE OR REPLACE VIEW v_top_vigilantes AS
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
LIMIT 10;

-- 8. VIEW: Candidaturas por dia (últimos 30 dias)
CREATE OR REPLACE VIEW v_applications_by_day AS
SELECT 
    DATE(applied_at) as date,
    COUNT(*) as count,
    SUM(CASE WHEN status = 'aprovada' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'rejeitada' THEN 1 ELSE 0 END) as rejected
FROM vacancy_applications
WHERE applied_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY DATE(applied_at)
ORDER BY date DESC;

-- 9. Inserir dados de exemplo no histórico para candidaturas existentes
-- (Apenas se houver candidaturas sem histórico)
INSERT INTO application_status_history (application_id, old_status, new_status, changed_by, changed_at, reason)
SELECT 
    id,
    NULL,
    status,
    vigilante_id,
    applied_at,
    'Migração v2.5 - Registro retroativo'
FROM vacancy_applications
WHERE id NOT IN (SELECT DISTINCT application_id FROM application_status_history)
ORDER BY id;

-- ==========================================
-- FIM DAS MIGRATIONS v2.5
-- ==========================================

-- Verificação final
SELECT 'Migrations v2.5 aplicadas com sucesso!' as status;
SELECT COUNT(*) as total_history FROM application_status_history;
SELECT * FROM v_application_stats;
