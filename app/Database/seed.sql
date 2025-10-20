-- Desabilita verificações de chaves estrangeiras temporariamente
SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO users (id, name, email, phone, gender, university, nuit, degree, major_area, bank_name, nib, role, password_hash, email_verified_at, verification_token, supervisor_eligible, available_for_vigilance, created_by, created_at, updated_at)
VALUES
    (1, 'Coordenador Geral', 'coordenador@unilicungo.ac.mz', '+258840000001', 'M', 'UniLicungo', '123456789', 'Mestre', 'Gestao Educacional', 'Millennium BIM', '000000000000000000001', 'coordenador', '$2y$10$mIWRlUQCztZGc8zjOP53D.fuQibiYzLUbaazCz5nwhBL2oWoO4gjm', NOW(), NULL, NULL, 0, NULL, NOW(), NOW()),
    (2, 'Membro Comissao', 'membro@unilicungo.ac.mz', '+258840000002', 'F', 'UniLicungo', '223456789', 'Licenciado', 'Administracao', 'BCI', '000000000000000000002', 'membro', '$2y$10$mIWRlUQCztZGc8zjOP53D.fuQibiYzLUbaazCz5nwhBL2oWoO4gjm', NOW(), NULL, NULL, 1, 1, NOW(), NOW()),
    (3, 'Vigilante Joao', 'vigilante1@unilicungo.ac.mz', '+258840000003', 'M', 'UniLicungo', '323456789', 'Licenciado', 'Matematica', 'Standard Bank', '000000000000000000003', 'vigilante', '$2y$10$mIWRlUQCztZGc8zjOP53D.fuQibiYzLUbaazCz5nwhBL2oWoO4gjm', NOW(), NULL, 1, 1, NULL, NOW(), NOW()),
    (4, 'Vigilante Maria', 'vigilante2@unilicungo.ac.mz', '+258840000004', 'F', 'UniLicungo', '423456789', 'Doutor', 'Biologia', 'Millennium BIM', '000000000000000000004', 'vigilante', '$2y$10$mIWRlUQCztZGc8zjOP53D.fuQibiYzLUbaazCz5nwhBL2oWoO4gjm', NOW(), NULL, 1, 1, NULL, NOW(), NOW());

INSERT INTO exam_vacancies (id, title, description, deadline_at, status, created_by, created_at, updated_at)
VALUES
    (1, 'Vigilancia Exame Matematica', 'Necessidade de vigilantes para exame de Matematica I.', DATE_ADD(NOW(), INTERVAL 7 DAY), 'aberta', 1, NOW(), NOW());

INSERT INTO juries (id, subject, exam_date, start_time, end_time, location, room, candidates_quota, notes, supervisor_id, approved_by, created_by, created_at, updated_at)
VALUES
    (1, 'Matematica I', DATE_ADD(CURDATE(), INTERVAL 10 DAY), '08:00:00', '11:00:00', 'Campus Central', 'Sala 101', 120, 'Chegada 30 minutos antes.', 3, 1, 2, NOW(), NOW());

INSERT INTO jury_vigilantes (id, jury_id, vigilante_id, assigned_by, created_at)
VALUES
    (1, 1, 3, 2, NOW());

INSERT INTO exam_reports (id, jury_id, supervisor_id, present_m, present_f, absent_m, absent_f, total, occurrences, submitted_at, created_at, updated_at)
VALUES
    (1, 1, 3, 45, 60, 5, 10, 120, 'Sem ocorrencias.', NOW(), NOW(), NOW());

INSERT INTO activity_log (user_id, entity, entity_id, action, metadata, ip, created_at)
VALUES
    (1, 'system', NULL, 'seed_loaded', JSON_OBJECT('version', '1.0'), '127.0.0.1', NOW());

-- Reabilita verificações de chaves estrangeiras
SET FOREIGN_KEY_CHECKS = 1;