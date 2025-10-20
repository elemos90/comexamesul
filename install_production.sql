-- ============================================
-- INSTALAÇÃO COMPLETA - PRODUÇÃO
-- admissao.cycode.net
-- ============================================
-- 
-- INSTRUÇÕES:
-- 1. Criar banco: cycodene_comexames
-- 2. Importar este arquivo via phpMyAdmin
-- 3. Alterar senha do coordenador após primeiro login
--
-- ============================================

-- Desabilitar verificação de foreign keys temporariamente
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- CRIAR USUÁRIO ADMINISTRADOR
-- ============================================

INSERT INTO users (
    name, 
    email, 
    phone, 
    role, 
    password_hash,
    email_verified_at,
    available_for_vigilance,
    supervisor_eligible,
    profile_completed,
    created_at,
    updated_at
) VALUES (
    'Coordenador Principal',
    'coordenador@admissao.cycode.net',
    '+258840000000',
    'coordenador',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Senha: password
    NOW(),
    0,
    1,
    1,
    NOW(),
    NOW()
);

-- ============================================
-- ADICIONAR ÍNDICES DE PERFORMANCE
-- ============================================

-- Júris: busca por local e data (se location_id existir)
CREATE INDEX IF NOT EXISTS idx_juries_exam_schedule ON juries(exam_date, start_time, end_time);

-- Usuários: filtro por papel e disponibilidade  
CREATE INDEX IF NOT EXISTS idx_users_role_available ON users(role, available_for_vigilance);

-- Alocações: lookup reverso
CREATE INDEX IF NOT EXISTS idx_jury_vigilantes_vigilante ON jury_vigilantes(vigilante_id);
CREATE INDEX IF NOT EXISTS idx_jury_vigilantes_jury ON jury_vigilantes(jury_id);

-- Candidaturas: filtro por status (se tabela existir)
CREATE INDEX IF NOT EXISTS idx_applications_status ON vacancy_applications(status) IF EXISTS (SELECT * FROM information_schema.tables WHERE table_name = 'vacancy_applications');

-- Activity log: busca por entidade
CREATE INDEX IF NOT EXISTS idx_activity_entity ON activity_log(entity, entity_id);

-- Reabilitar verificação de foreign keys
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- VERIFICAÇÃO FINAL
-- ============================================

SELECT 'Instalação concluída com sucesso!' AS status;
SELECT COUNT(*) AS total_users FROM users;
SELECT COUNT(*) AS total_indexes FROM information_schema.statistics WHERE table_schema = DATABASE();

-- ============================================
-- ⚠️ IMPORTANTE - PRÓXIMOS PASSOS
-- ============================================
-- 
-- 1. ALTERAR SENHA DO COORDENADOR imediatamente:
--    UPDATE users SET password_hash = PASSWORD_HASH_NOVA WHERE email = 'coordenador@admissao.cycode.net';
--
-- 2. Gerar nova senha em: https://bcrypt-generator.com/
--    Custo: 10
--
-- 3. Primeiro login:
--    Email: coordenador@admissao.cycode.net
--    Senha: password (TROCAR IMEDIATAMENTE!)
--
-- ============================================
