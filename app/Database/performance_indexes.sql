-- =====================================================
-- ÍNDICES DE PERFORMANCE - Portal Comissão de Exames
-- =====================================================
-- Executar este script para melhorar performance das queries
-- Tempo estimado: 1-2 minutos
-- =====================================================

-- Júris: Busca por local e data (usado em visualizações)
CREATE INDEX IF NOT EXISTS idx_juries_location_date 
ON juries(location_id, exam_date);

-- Júris: Busca por disciplina
CREATE INDEX IF NOT EXISTS idx_juries_discipline 
ON juries(discipline_id);

-- Júris: Busca por status
CREATE INDEX IF NOT EXISTS idx_juries_status 
ON juries(status);

-- Usuários: Filtro por role e disponibilidade (listagens de vigilantes)
CREATE INDEX IF NOT EXISTS idx_users_role_available 
ON users(role, available_for_vigilance);

-- Usuários: Busca por email (login frequente)
CREATE INDEX IF NOT EXISTS idx_users_email 
ON users(email);

-- Alocações: Lookup por vigilante (ver alocações de um vigilante)
CREATE INDEX IF NOT EXISTS idx_jury_vigilantes_vigilante 
ON jury_vigilantes(vigilante_id);

-- Alocações: Lookup por júri (ver vigilantes de um júri)
CREATE INDEX IF NOT EXISTS idx_jury_vigilantes_jury 
ON jury_vigilantes(jury_id);

-- Candidaturas: Filtro por status (dashboard)
CREATE INDEX IF NOT EXISTS idx_applications_status 
ON vacancy_applications(status);

-- Candidaturas: Busca por vaga e status
CREATE INDEX IF NOT EXISTS idx_applications_vacancy_status 
ON vacancy_applications(vacancy_id, status);

-- Candidaturas: Busca por vigilante
CREATE INDEX IF NOT EXISTS idx_applications_vigilante 
ON vacancy_applications(vigilante_id);

-- Vagas: Busca por status e deadline (filtrar vagas abertas)
CREATE INDEX IF NOT EXISTS idx_vacancies_status_deadline 
ON exam_vacancies(status, deadline_at);

-- Logs de atividade: Busca por entidade (auditoria)
CREATE INDEX IF NOT EXISTS idx_activity_logs_entity 
ON activity_logs(entity_type, entity_id);

-- Logs de atividade: Busca por usuário e data
CREATE INDEX IF NOT EXISTS idx_activity_logs_user_created 
ON activity_logs(user_id, created_at);

-- Solicitações de mudança: Busca por vigilante e status
CREATE INDEX IF NOT EXISTS idx_availability_requests_vigilante_status 
ON availability_change_requests(vigilante_id, status);

-- Locais: Busca por status ativo
CREATE INDEX IF NOT EXISTS idx_locations_active 
ON exam_locations(is_active);

-- Salas: Busca por local
CREATE INDEX IF NOT EXISTS idx_rooms_location 
ON exam_rooms(location_id);

-- =====================================================
-- VERIFICAR ÍNDICES CRIADOS
-- =====================================================
-- Execute esta query para confirmar:
-- SHOW INDEX FROM juries;
-- SHOW INDEX FROM users;
-- SHOW INDEX FROM jury_vigilantes;
-- SHOW INDEX FROM vacancy_applications;
-- =====================================================

-- Mensagem de sucesso
SELECT 'Índices de performance criados com sucesso!' as status;
