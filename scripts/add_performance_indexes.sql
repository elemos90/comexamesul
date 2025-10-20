-- ========================================
-- ÍNDICES DE PERFORMANCE
-- Portal Comexamesul
-- Outubro 2025
-- ========================================

-- Executar: mysql -u root -p comexamesul < scripts/add_performance_indexes.sql

USE comexamesul;

-- Júris: filtro por local e data (usado em visualizações e relatórios)
CREATE INDEX IF NOT EXISTS idx_juries_location_date 
ON juries(location_id, exam_date, start_time);

-- Júris: filtro por vaga
CREATE INDEX IF NOT EXISTS idx_juries_vacancy 
ON juries(vacancy_id);

-- Júris: lookup por disciplina
CREATE INDEX IF NOT EXISTS idx_juries_subject 
ON juries(subject, exam_date);

-- Usuários: filtro por disponibilidade para vigilância
CREATE INDEX IF NOT EXISTS idx_users_available 
ON users(available_for_vigilance, role);

-- Usuários: elegibilidade para supervisão
CREATE INDEX IF NOT EXISTS idx_users_supervisor 
ON users(supervisor_eligible, role);

-- Alocações: lookup por júri (query mais frequente)
CREATE INDEX IF NOT EXISTS idx_jury_vigilantes_jury 
ON jury_vigilantes(jury_id, vigilante_id);

-- Alocações: lookup por vigilante
CREATE INDEX IF NOT EXISTS idx_jury_vigilantes_vigilante 
ON jury_vigilantes(vigilante_id, jury_id);

-- Candidaturas: filtro por status e vaga
CREATE INDEX IF NOT EXISTS idx_applications_status 
ON vacancy_applications(status, vacancy_id);

-- Candidaturas: por usuário
CREATE INDEX IF NOT EXISTS idx_applications_user 
ON vacancy_applications(user_id, status);

-- Vagas: filtro por status e deadline (para cron de fecho automático)
CREATE INDEX IF NOT EXISTS idx_vacancies_status 
ON exam_vacancies(status, deadline);

-- Relatórios de supervisores
CREATE INDEX IF NOT EXISTS idx_reports_jury 
ON exam_reports(jury_id);

-- Logs de atividade: por usuário e data (tabela: activity_log)
CREATE INDEX IF NOT EXISTS idx_activity_user_date 
ON activity_log(user_id, created_at);

-- Locais: busca por nome
CREATE INDEX IF NOT EXISTS idx_locations_name 
ON exam_locations(name);

-- Salas: por local
CREATE INDEX IF NOT EXISTS idx_rooms_location 
ON exam_rooms(location_id, capacity);

-- Histórico de status: por candidatura
CREATE INDEX IF NOT EXISTS idx_status_history_application 
ON application_status_history(application_id, created_at);

-- Solicitações de mudança de disponibilidade
CREATE INDEX IF NOT EXISTS idx_availability_requests_user 
ON availability_change_requests(vigilante_id, status);

-- Notificações de email (verificar se coluna existe)
-- CREATE INDEX IF NOT EXISTS idx_notifications_user 
-- ON email_notifications(user_id, is_read);

SELECT '✅ Índices de performance criados com sucesso!' AS status;

-- Verificar índices criados
SHOW INDEX FROM juries;
SHOW INDEX FROM users;
SHOW INDEX FROM jury_vigilantes;
SHOW INDEX FROM vacancy_applications;
