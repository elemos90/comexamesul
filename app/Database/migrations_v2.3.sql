-- ========================================
-- MIGRATIONS V2.3 - Sistema de Mudança de Disponibilidade
-- Data: 11/10/2025
-- ========================================

-- Criar tabela de solicitações de mudança de disponibilidade
CREATE TABLE IF NOT EXISTS availability_change_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vigilante_id INT NOT NULL,
    application_id INT NOT NULL,
    request_type ENUM('cancelamento','alteracao') NOT NULL DEFAULT 'cancelamento',
    reason TEXT NOT NULL,
    attachment_path VARCHAR(255) NULL,
    attachment_original_name VARCHAR(255) NULL,
    has_allocation TINYINT(1) NOT NULL DEFAULT 0,
    jury_details TEXT NULL,
    status ENUM('pendente','aprovada','rejeitada') NOT NULL DEFAULT 'pendente',
    reviewed_at DATETIME NULL,
    reviewed_by INT NULL,
    reviewer_notes TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_acr_vigilante FOREIGN KEY (vigilante_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_acr_application FOREIGN KEY (application_id) REFERENCES vacancy_applications (id) ON DELETE CASCADE,
    CONSTRAINT fk_acr_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_acr_status (status),
    INDEX idx_acr_vigilante (vigilante_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar diretório para uploads (comentário SQL)
-- Criar manualmente: storage/uploads/justifications/

-- ========================================
-- COMENTÁRIOS
-- ========================================

-- CAMPOS IMPORTANTES:
-- request_type: 'cancelamento' ou 'alteracao'
-- reason: Justificativa escrita obrigatória
-- attachment_path: Caminho do documento anexado (opcional)
-- has_allocation: Se vigilante já está alocado a júris (1 = sim, 0 = não)
-- jury_details: JSON com detalhes dos júris em que está alocado
-- status: pendente/aprovada/rejeitada
