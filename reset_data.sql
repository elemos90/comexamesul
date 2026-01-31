-- ==================================================================================
-- SCRIPT DE RESET DE DADOS OPERACIONAIS (CLEAN SLATE)
-- ==================================================================================
-- ESTE SCRIPT REMOVE TODOS OS DADOS DE VIGILÂNCIA, JÚRIS, VAGAS E CANDIDATURAS.
-- DADOS MESTRE (USUÁRIOS, LOCAIS, SALAS, DISCIPLINAS) SÃO PRESERVADOS.
--
-- USE COM CAUTELA! NÃO HÁ COMO DESFAZER ESTA OPERAÇÃO.
-- ==================================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Remover Alocações e Vínculos
TRUNCATE TABLE jury_vigilantes;          -- Vínculo Vigilante <-> Júri
TRUNCATE TABLE vacancy_applications;     -- Inscrições de Vigilantes nas Vagas
TRUNCATE TABLE application_status_history; -- Histórico de Status das Inscrições
TRUNCATE TABLE availability_change_requests; -- Solicitações de Troca de Disponibilidade

-- 2. Remover Dados Core de Exames
TRUNCATE TABLE juries;                   -- Os Júris (Salas/Horários)
TRUNCATE TABLE exam_vacancies;           -- As Vagas (Evento Principal)
TRUNCATE TABLE exam_reports;             -- Relatórios de Exame

-- 3. Remover Dados Transacionais e Logs
TRUNCATE TABLE payments;                 -- Pagamentos
TRUNCATE TABLE notifications;            -- Notificações do Sistema
TRUNCATE TABLE notification_recipients;  -- Destinatários de Notificações
TRUNCATE TABLE email_notifications;      -- Fila de Emails
TRUNCATE TABLE activity_log;             -- Logs de Atividade (Opcional, mas recomendado para clean slate)
TRUNCATE TABLE location_stats;           -- Estatísticas Cacheadas (serão recalculadas)

-- 4. Tabelas Mestre PRESERVADAS (NÃO SÃO TOCADAS):
-- users
-- exam_locations
-- exam_rooms
-- disciplines
-- payment_rates
-- notification_channels
-- feature_flags
-- location_templates

SET FOREIGN_KEY_CHECKS = 1;

-- ==================================================================================
-- FIM DO SCRIPT
-- ==================================================================================
SELECT 'Limpeza Concluída com Sucesso!' as Status;
