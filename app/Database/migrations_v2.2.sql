-- ========================================
-- MIGRATIONS V2.2 - Sistema de Vagas Vinculadas
-- Data: 11/10/2025
-- ========================================

-- 1. Adicionar vacancy_id na tabela juries
-- Júris agora são vinculados a uma vaga específica
ALTER TABLE juries 
ADD COLUMN vacancy_id INT NULL AFTER id,
ADD CONSTRAINT fk_juries_vacancy FOREIGN KEY (vacancy_id) REFERENCES exam_vacancies (id) ON DELETE SET NULL,
ADD INDEX idx_juries_vacancy (vacancy_id);

-- 2. Criar tabela de candidaturas a vagas
-- Substitui o campo genérico available_for_vigilance
CREATE TABLE IF NOT EXISTS vacancy_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vacancy_id INT NOT NULL,
    vigilante_id INT NOT NULL,
    status ENUM('pendente','aprovada','rejeitada','cancelada') NOT NULL DEFAULT 'pendente',
    notes TEXT NULL,
    applied_at DATETIME NOT NULL,
    reviewed_at DATETIME NULL,
    reviewed_by INT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_vacancy_application (vacancy_id, vigilante_id),
    CONSTRAINT fk_va_vacancy FOREIGN KEY (vacancy_id) REFERENCES exam_vacancies (id) ON DELETE CASCADE,
    CONSTRAINT fk_va_vigilante FOREIGN KEY (vigilante_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_va_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_va_status (status),
    INDEX idx_va_applied (applied_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Adicionar campos de validação de perfil completo
ALTER TABLE users
ADD COLUMN profile_completed TINYINT(1) NOT NULL DEFAULT 0 AFTER available_for_vigilance,
ADD COLUMN profile_completed_at DATETIME NULL AFTER profile_completed;

-- 4. Criar índice para buscar vigilantes disponíveis
CREATE INDEX idx_users_profile_vigilante ON users (role, profile_completed, available_for_vigilance);

-- ========================================
-- DADOS INICIAIS / MIGRAÇÃO
-- ========================================

-- Migrar disponibilidade genérica para candidaturas nas vagas abertas
-- (Executar apenas se houver vagas abertas e vigilantes disponíveis)
INSERT INTO vacancy_applications (vacancy_id, vigilante_id, status, applied_at, created_at, updated_at)
SELECT 
    v.id AS vacancy_id,
    u.id AS vigilante_id,
    'aprovada' AS status,
    NOW() AS applied_at,
    NOW() AS created_at,
    NOW() AS updated_at
FROM users u
CROSS JOIN exam_vacancies v
WHERE u.role = 'vigilante' 
  AND u.available_for_vigilance = 1
  AND v.status = 'aberta'
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Marcar perfis como completos se tiverem dados essenciais
UPDATE users 
SET 
    profile_completed = 1,
    profile_completed_at = NOW()
WHERE role = 'vigilante'
  AND phone IS NOT NULL 
  AND phone != ''
  AND nuit IS NOT NULL 
  AND nuit != ''
  AND nib IS NOT NULL 
  AND nib != ''
  AND bank_name IS NOT NULL 
  AND bank_name != '';

-- ========================================
-- COMENTÁRIOS
-- ========================================

-- NOTAS IMPORTANTES:
-- 1. O campo 'available_for_vigilance' pode ser mantido para compatibilidade
-- 2. vacancy_applications é a nova fonte de verdade para candidaturas
-- 3. profile_completed garante que vigilante completou dados obrigatórios
-- 4. vacancy_id em juries permite filtrar júris por vaga/concurso
