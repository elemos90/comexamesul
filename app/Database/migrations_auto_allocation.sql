-- ============================================
-- MIGRATIONS: Sistema de Alocação Automática
-- "Auto → Revisão Humana" por Local/Data
-- Data: 2025-10-10
-- ============================================

-- ============================================
-- 1. AJUSTES DE COLUNAS E ÍNDICES
-- ============================================

-- Adicionar colunas de janela temporal aos júris
ALTER TABLE juries
  ADD COLUMN IF NOT EXISTS inicio DATETIME NULL COMMENT 'Data/hora início do júri (formato YYYY-MM-DD HH:MM:SS)',
  ADD COLUMN IF NOT EXISTS fim DATETIME NULL COMMENT 'Data/hora fim do júri',
  ADD COLUMN IF NOT EXISTS vigilantes_capacidade INT NOT NULL DEFAULT 2 COMMENT 'Capacidade máxima de vigilantes';

-- Adicionar colunas para controle de alocações
ALTER TABLE jury_vigilantes
  ADD COLUMN IF NOT EXISTS papel ENUM('vigilante','supervisor') NOT NULL DEFAULT 'vigilante' COMMENT 'Papel do docente no júri',
  ADD COLUMN IF NOT EXISTS juri_inicio DATETIME NULL COMMENT 'Janela temporal materializada (desnormalização para performance)',
  ADD COLUMN IF NOT EXISTS juri_fim DATETIME NULL COMMENT 'Janela temporal materializada';

-- Índice composto para verificação rápida de conflitos de horário
CREATE INDEX IF NOT EXISTS idx_jv_docente_intervalo
  ON jury_vigilantes (vigilante_id, juri_inicio, juri_fim);

-- Índice para buscar rapidamente vigilantes de um júri por papel
CREATE INDEX IF NOT EXISTS idx_jv_papel
  ON jury_vigilantes (jury_id, papel);

-- Índice único para garantir supervisor único por júri
-- Nota: MySQL não suporta índice parcial com WHERE, então usaremos trigger
DROP INDEX IF EXISTS uq_supervisor_juri ON jury_vigilantes;

-- ============================================
-- 2. VIEW: SCORE DE DOCENTES
-- ============================================
-- Calcula score agregado por docente
-- Score = (1 × nº vigilâncias) + (2 × nº supervisões)

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

-- ============================================
-- 3. TRIGGERS: VALIDAÇÕES E AUTOMAÇÕES
-- ============================================

DELIMITER //

-- ---------------------------------------------
-- Trigger: Materializar janela temporal no INSERT
-- ---------------------------------------------
DROP TRIGGER IF EXISTS trg_jv_set_interval_bi//

CREATE TRIGGER trg_jv_set_interval_bi
BEFORE INSERT ON jury_vigilantes
FOR EACH ROW
BEGIN
  -- Buscar janela temporal do júri e materializar
  SELECT inicio, fim INTO NEW.juri_inicio, NEW.juri_fim
  FROM juries
  WHERE id = NEW.jury_id;
END//

-- ---------------------------------------------
-- Trigger: Materializar janela temporal no UPDATE
-- ---------------------------------------------
DROP TRIGGER IF EXISTS trg_jv_set_interval_bu//

CREATE TRIGGER trg_jv_set_interval_bu
BEFORE UPDATE ON jury_vigilantes
FOR EACH ROW
BEGIN
  -- Atualizar janela temporal se júri mudou
  IF NEW.jury_id != OLD.jury_id THEN
    SELECT inicio, fim INTO NEW.juri_inicio, NEW.juri_fim
    FROM juries
    WHERE id = NEW.jury_id;
  END IF;
END//

-- ---------------------------------------------
-- Trigger: Validar capacidade de vigilantes
-- ---------------------------------------------
DROP TRIGGER IF EXISTS trg_jv_check_cap//

CREATE TRIGGER trg_jv_check_cap
BEFORE INSERT ON jury_vigilantes
FOR EACH ROW
BEGIN
  DECLARE cap INT DEFAULT 0;
  DECLARE qtd INT DEFAULT 0;
  
  IF NEW.papel = 'vigilante' THEN
    -- Buscar capacidade configurada
    SELECT vigilantes_capacidade INTO cap
    FROM juries
    WHERE id = NEW.jury_id;
    
    -- Contar vigilantes já alocados
    SELECT COUNT(*) INTO qtd
    FROM jury_vigilantes
    WHERE jury_id = NEW.jury_id AND papel = 'vigilante';
    
    -- Bloquear se capacidade atingida
    IF qtd >= cap THEN
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Capacidade de vigilantes atingida';
    END IF;
  END IF;
END//

-- ---------------------------------------------
-- Trigger: Validar supervisor único por júri
-- ---------------------------------------------
DROP TRIGGER IF EXISTS trg_jv_supervisor_unico//

CREATE TRIGGER trg_jv_supervisor_unico
BEFORE INSERT ON jury_vigilantes
FOR EACH ROW
BEGIN
  DECLARE existe INT DEFAULT 0;
  
  IF NEW.papel = 'supervisor' THEN
    -- Verificar se já existe supervisor
    SELECT COUNT(*) INTO existe
    FROM jury_vigilantes
    WHERE jury_id = NEW.jury_id AND papel = 'supervisor';
    
    -- Bloquear se já houver supervisor
    IF existe > 0 THEN
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Júri já possui supervisor';
    END IF;
  END IF;
END//

-- ---------------------------------------------
-- Trigger: Prevenir conflito de horário
-- ---------------------------------------------
DROP TRIGGER IF EXISTS trg_jv_no_overlap_ins//

CREATE TRIGGER trg_jv_no_overlap_ins
BEFORE INSERT ON jury_vigilantes
FOR EACH ROW
BEGIN
  DECLARE confl INT DEFAULT 0;
  DECLARE inicio_novo DATETIME;
  DECLARE fim_novo DATETIME;
  
  -- Buscar janela temporal do júri novo
  SELECT inicio, fim INTO inicio_novo, fim_novo
  FROM juries
  WHERE id = NEW.jury_id;
  
  -- Verificar se docente tem conflito de horário
  -- Condição de sobreposição: (fim_novo > juri_inicio) AND (inicio_novo < juri_fim)
  SELECT COUNT(*) INTO confl
  FROM jury_vigilantes jv
  WHERE jv.vigilante_id = NEW.vigilante_id
    AND jv.juri_inicio IS NOT NULL
    AND jv.juri_fim IS NOT NULL
    AND fim_novo > jv.juri_inicio
    AND inicio_novo < jv.juri_fim;
  
  -- Bloquear se houver conflito
  IF confl > 0 THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Conflito de horário';
  END IF;
END//

-- ---------------------------------------------
-- Trigger: Prevenir conflito de horário no UPDATE
-- ---------------------------------------------
DROP TRIGGER IF EXISTS trg_jv_no_overlap_upd//

CREATE TRIGGER trg_jv_no_overlap_upd
BEFORE UPDATE ON jury_vigilantes
FOR EACH ROW
BEGIN
  DECLARE confl INT DEFAULT 0;
  DECLARE inicio_novo DATETIME;
  DECLARE fim_novo DATETIME;
  
  -- Apenas verificar se júri ou vigilante mudou
  IF NEW.jury_id != OLD.jury_id OR NEW.vigilante_id != OLD.vigilante_id THEN
    -- Buscar janela temporal do júri novo
    SELECT inicio, fim INTO inicio_novo, fim_novo
    FROM juries
    WHERE id = NEW.jury_id;
    
    -- Verificar conflitos (excluindo o próprio registro)
    SELECT COUNT(*) INTO confl
    FROM jury_vigilantes jv
    WHERE jv.vigilante_id = NEW.vigilante_id
      AND jv.id != NEW.id
      AND jv.juri_inicio IS NOT NULL
      AND jv.juri_fim IS NOT NULL
      AND fim_novo > jv.juri_inicio
      AND inicio_novo < jv.juri_fim;
    
    IF confl > 0 THEN
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Conflito de horário';
    END IF;
  END IF;
END//

DELIMITER ;

-- ============================================
-- 4. POPULAR JANELAS TEMPORAIS EXISTENTES
-- ============================================
-- Atualizar júris existentes com janelas temporais
-- baseadas em exam_date + start_time/end_time

UPDATE juries
SET 
  inicio = CONCAT(exam_date, ' ', start_time),
  fim = CONCAT(exam_date, ' ', end_time)
WHERE inicio IS NULL OR fim IS NULL;

-- Atualizar alocações existentes com janelas materializadas
UPDATE jury_vigilantes jv
INNER JOIN juries j ON jv.jury_id = j.id
SET 
  jv.juri_inicio = j.inicio,
  jv.juri_fim = j.fim
WHERE jv.juri_inicio IS NULL OR jv.juri_fim IS NULL;

-- ============================================
-- 5. ÍNDICES ADICIONAIS PARA PERFORMANCE
-- ============================================

-- Índice para buscar júris por local e data
CREATE INDEX IF NOT EXISTS idx_juries_local_date
  ON juries (location, exam_date);

-- Índice para buscar júris por janela temporal
CREATE INDEX IF NOT EXISTS idx_juries_intervalo
  ON juries (inicio, fim);

-- ============================================
-- VERIFICAÇÕES FINAIS
-- ============================================

-- Verificar integridade
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

-- ============================================
-- NOTAS IMPORTANTES
-- ============================================
-- 1. Triggers garantem validações em tempo de INSERT/UPDATE
-- 2. View vw_docente_score calcula score agregado (cache lógico)
-- 3. Colunas juri_inicio/juri_fim são desnormalizadas para performance
-- 4. Índices otimizam queries de conflito de horário (O(log n) vs O(n))
-- 5. ENUM 'papel' facilita queries e garante integridade de dados
-- ============================================
