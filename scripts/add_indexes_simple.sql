-- ÍNDICES ESSENCIAIS - Versão Simplificada
-- Apenas as tabelas principais

USE comexamesul;

-- JÚRIS (tabela principal)
CREATE INDEX IF NOT EXISTS idx_juries_location_date ON juries(location_id, exam_date, start_time);
CREATE INDEX IF NOT EXISTS idx_juries_vacancy ON juries(vacancy_id);
CREATE INDEX IF NOT EXISTS idx_juries_subject ON juries(subject, exam_date);

-- USUÁRIOS (vigilantes e supervisores)
CREATE INDEX IF NOT EXISTS idx_users_available ON users(available_for_vigilance, role);
CREATE INDEX IF NOT EXISTS idx_users_supervisor ON users(supervisor_eligible, role);

-- ALOCAÇÕES (júri + vigilantes)
CREATE INDEX IF NOT EXISTS idx_jury_vigilantes_jury ON jury_vigilantes(jury_id, vigilante_id);
CREATE INDEX IF NOT EXISTS idx_jury_vigilantes_vigilante ON jury_vigilantes(vigilante_id, jury_id);

-- CANDIDATURAS
CREATE INDEX IF NOT EXISTS idx_applications_status ON vacancy_applications(status, vacancy_id);
CREATE INDEX IF NOT EXISTS idx_applications_user ON vacancy_applications(vigilante_id, status);

-- VAGAS (já tem idx_vacancies_deadline, adicionar status)
CREATE INDEX IF NOT EXISTS idx_vacancies_status ON exam_vacancies(status);

SELECT '✅ Índices essenciais criados!' AS resultado;
