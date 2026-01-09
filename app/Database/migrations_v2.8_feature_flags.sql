-- ================================================
-- Migration v2.8: Feature Flags Module
-- Controlo fino de funcionalidades por perfil
-- ================================================

-- Tabela principal de Feature Flags
CREATE TABLE IF NOT EXISTS feature_flags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(20) NOT NULL COMMENT 'membro, vigilante',
    feature_code VARCHAR(50) NOT NULL COMMENT 'Código único da funcionalidade',
    feature_name VARCHAR(100) NOT NULL COMMENT 'Nome legível da funcionalidade',
    feature_description VARCHAR(255) DEFAULT NULL COMMENT 'Descrição para o admin',
    feature_group VARCHAR(50) DEFAULT 'geral' COMMENT 'Agrupamento no UI',
    enabled TINYINT(1) DEFAULT 1 COMMENT '1=ativo, 0=desativado',
    updated_by INT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_role_feature (role, feature_code),
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_role (role),
    INDEX idx_feature_code (feature_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Inserir flags padrão para MEMBRO DA COMISSÃO
-- ================================================
INSERT INTO feature_flags (role, feature_code, feature_name, feature_description, feature_group, enabled) VALUES
-- Gestão de Júris
('membro', 'commission.create_jury', 'Criar Júris', 'Permite criar novos júris para vagas', 'juris', 1),
('membro', 'commission.edit_jury', 'Editar Júris', 'Permite modificar júris existentes', 'juris', 1),
('membro', 'commission.delete_jury', 'Eliminar Júris', 'Permite eliminar júris', 'juris', 1),

-- Alocação
('membro', 'commission.auto_vigilantes', 'Auto-distribuir Vigilantes', 'Distribuição automática de vigilantes', 'alocacao', 1),
('membro', 'commission.auto_supervisors', 'Auto-distribuir Supervisores', 'Distribuição automática de supervisores', 'alocacao', 1),
('membro', 'commission.manual_allocation', 'Alocação Manual', 'Alocar vigilantes/supervisores manualmente', 'alocacao', 1),

-- Relatórios e Pagamentos
('membro', 'commission.post_exam', 'Submeter Relatório Pós-Exame', 'Submeter relatórios após exames', 'relatorios', 1),
('membro', 'commission.view_payments', 'Ver Mapa de Pagamentos', 'Acesso ao mapa geral de pagamentos', 'pagamentos', 1),
('membro', 'commission.export_reports', 'Exportar Relatórios', 'Exportar dados em PDF/Excel', 'exportacao', 1)

ON DUPLICATE KEY UPDATE feature_name = VALUES(feature_name);

-- ================================================
-- Inserir flags padrão para VIGILANTE
-- ================================================
INSERT INTO feature_flags (role, feature_code, feature_name, feature_description, feature_group, enabled) VALUES
-- Visualização
('vigilante', 'guard.view_juries', 'Ver Júris Alocados', 'Ver os júris onde está alocado', 'visualizacao', 1),
('vigilante', 'guard.view_calendar', 'Ver Calendário', 'Acesso ao calendário de exames', 'visualizacao', 1),

-- Relatórios
('vigilante', 'guard.post_exam', 'Submeter Relatório Pós-Exame', 'Submeter relatórios após exames', 'relatorios', 1),
('vigilante', 'guard.edit_post_exam', 'Editar Relatório', 'Editar relatório antes da validação', 'relatorios', 0),

-- Pagamentos
('vigilante', 'guard.view_own_payment', 'Ver Meu Pagamento', 'Ver mapa individual de pagamento', 'pagamentos', 1),
('vigilante', 'guard.export_payment_pdf', 'Exportar PDF de Pagamento', 'Exportar comprovativo individual', 'pagamentos', 0)

ON DUPLICATE KEY UPDATE feature_name = VALUES(feature_name);

-- ================================================
-- Verificação
-- ================================================
SELECT 'Feature Flags inseridas:' as status;
SELECT role, COUNT(*) as total, SUM(enabled) as ativas FROM feature_flags GROUP BY role;
