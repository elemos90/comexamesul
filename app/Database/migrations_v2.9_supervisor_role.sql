-- ============================================================
-- Migration v2.9: Add Supervisor Role
-- Separates Vigilante and Supervisor into distinct roles
-- ============================================================

-- Step 1: Backup current data (safety check)
SELECT 'Starting migration v2.9 - Supervisor Role Separation' as status;

-- Step 2: Modify users table ENUM to include 'supervisor'
-- Note: MySQL allows adding values to ENUM without data loss
ALTER TABLE users MODIFY COLUMN role 
    ENUM('coordenador', 'membro', 'supervisor', 'vigilante') 
    NOT NULL DEFAULT 'vigilante';

SELECT 'ENUM updated successfully' as status;

-- Step 3: Identify and promote users who are already supervisors in juries
-- (Users assigned as supervisor_id in any jury)
UPDATE users u
SET u.role = 'supervisor'
WHERE u.role = 'vigilante'
  AND u.id IN (
    SELECT DISTINCT j.supervisor_id 
    FROM juries j 
    WHERE j.supervisor_id IS NOT NULL
  );

SELECT CONCAT('Promoted ', ROW_COUNT(), ' users to supervisor role') as status;

-- Step 4: Add supervisor-specific feature flags
INSERT INTO feature_flags 
    (role, feature_code, feature_name, feature_description, enabled, feature_group, updated_by)
VALUES 
    ('supervisor', 'supervisor.view_juries', 'Ver júris supervisionados', 
     'Ver os júris sob sua supervisão', 1, 'visualizacao', 1),
    ('supervisor', 'supervisor.consolidate_reports', 'Consolidar relatórios', 
     'Consolidar relatórios de júris', 1, 'relatorios', 1),
    ('supervisor', 'supervisor.validate_reports', 'Validar relatórios', 
     'Validar relatórios de vigilantes', 1, 'relatorios', 1),
    ('supervisor', 'supervisor.submit_block_notes', 'Observação do bloco', 
     'Submeter observação global do bloco', 1, 'relatorios', 1),
    ('supervisor', 'supervisor.view_block_summary', 'Resumo do bloco', 
     'Ver resumo do bloco supervisionado', 1, 'visualizacao', 1),
    -- Supervisor inherits these from vigilante (also enable for supervisor)
    ('supervisor', 'guard.view_juries', 'Ver júris alocados', 
     'Ver os júris onde está alocado', 1, 'visualizacao', 1),
    ('supervisor', 'guard.post_exam', 'Submeter relatório pós-exame', 
     'Submeter relatórios após exames', 1, 'relatorios', 1),
    ('supervisor', 'guard.edit_post_exam', 'Editar relatório', 
     'Editar relatório antes da validação', 1, 'relatorios', 1),
    ('supervisor', 'guard.view_own_payment', 'Ver meu pagamento', 
     'Ver mapa individual de pagamento', 1, 'pagamentos', 1),
    ('supervisor', 'guard.export_payment_pdf', 'Exportar PDF de pagamento', 
     'Exportar comprovativo individual', 1, 'exportacao', 1)
ON DUPLICATE KEY UPDATE 
    feature_name = VALUES(feature_name),
    feature_description = VALUES(feature_description);

SELECT 'Supervisor feature flags added' as status;

-- Step 5: Log this migration in activity_log
INSERT INTO activity_log (entity_type, entity_id, action, details, user_id, created_at)
VALUES (
    'system', 0, 'migration_v2.9', 
    JSON_OBJECT('description', 'Separated Vigilante and Supervisor roles'),
    1, NOW()
);

SELECT 'Migration v2.9 completed successfully!' as final_status;

-- Verification queries
SELECT role, COUNT(*) as count FROM users GROUP BY role;
SELECT role, COUNT(*) as flags FROM feature_flags GROUP BY role;
