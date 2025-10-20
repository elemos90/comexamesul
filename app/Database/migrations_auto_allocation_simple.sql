-- ============================================
-- MIGRATIONS: Sistema de Alocação Automática (SIMPLIFICADO)
-- Versão sem triggers complexos para teste inicial
-- ============================================

-- 1. Adicionar colunas aos júris
ALTER TABLE juries
  ADD COLUMN IF NOT EXISTS inicio DATETIME NULL,
  ADD COLUMN IF NOT EXISTS fim DATETIME NULL,
  ADD COLUMN IF NOT EXISTS vigilantes_capacidade INT NOT NULL DEFAULT 2;

-- 2. Adicionar colunas às alocações
ALTER TABLE jury_vigilantes
  ADD COLUMN IF NOT EXISTS papel ENUM('vigilante','supervisor') NOT NULL DEFAULT 'vigilante',
  ADD COLUMN IF NOT EXISTS juri_inicio DATETIME NULL,
  ADD COLUMN IF NOT EXISTS juri_fim DATETIME NULL;

-- 3. Índices
CREATE INDEX IF NOT EXISTS idx_jv_docente_intervalo
  ON jury_vigilantes (vigilante_id, juri_inicio, juri_fim);

CREATE INDEX IF NOT EXISTS idx_jv_papel
  ON jury_vigilantes (jury_id, papel);

CREATE INDEX IF NOT EXISTS idx_juries_local_date
  ON juries (location, exam_date);

CREATE INDEX IF NOT EXISTS idx_juries_intervalo
  ON juries (inicio, fim);

-- 4. View de scores
DROP VIEW IF EXISTS vw_docente_score;

CREATE VIEW vw_docente_score AS
SELECT
  jv.vigilante_id AS docente_id,
  SUM(CASE WHEN jv.papel = 'vigilante' THEN 1 ELSE 0 END) AS n_vigias,
  SUM(CASE WHEN jv.papel = 'supervisor' THEN 1 ELSE 0 END) AS n_supervisoes,
  SUM(CASE 
    WHEN jv.papel = 'vigilante' THEN 1
    WHEN jv.papel = 'supervisor' THEN 2
    ELSE 0
  END) AS score
FROM jury_vigilantes jv
GROUP BY jv.vigilante_id;

-- 5. Popular dados existentes
UPDATE juries
SET 
  inicio = CONCAT(exam_date, ' ', start_time),
  fim = CONCAT(exam_date, ' ', end_time)
WHERE inicio IS NULL OR fim IS NULL;

UPDATE jury_vigilantes jv
INNER JOIN juries j ON jv.jury_id = j.id
SET 
  jv.juri_inicio = j.inicio,
  jv.juri_fim = j.fim
WHERE jv.juri_inicio IS NULL OR jv.juri_fim IS NULL;

-- 6. Verificação
SELECT 
  'juries' AS tabela,
  COUNT(*) AS total,
  SUM(CASE WHEN inicio IS NULL OR fim IS NULL THEN 1 ELSE 0 END) AS sem_janela
FROM juries

UNION ALL

SELECT 
  'jury_vigilantes' AS tabela,
  COUNT(*) AS total,
  SUM(CASE WHEN juri_inicio IS NULL OR juri_fim IS NULL THEN 1 ELSE 0 END) AS sem_janela
FROM jury_vigilantes;

SELECT '✅ Migrations básicas concluídas!' AS status;
