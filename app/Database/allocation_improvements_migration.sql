-- ============================================================================
-- MIGRATION: Melhorias no Sistema de Alocação de Vigilantes e Supervisores
-- Versão: 2.1
-- Data: 09/10/2025
-- ============================================================================

-- 1. ADICIONAR CAMPOS DE CAPACIDADE E CONTROLE NOS JÚRIS
-- ============================================================================

ALTER TABLE juries 
ADD COLUMN IF NOT EXISTS vigilantes_capacity INT DEFAULT 2 AFTER candidates_quota,
ADD COLUMN IF NOT EXISTS requires_supervisor TINYINT(1) DEFAULT 1 AFTER vigilantes_capacity,
ADD INDEX idx_juries_datetime (exam_date, start_time, end_time);

COMMENT ON COLUMN juries.vigilantes_capacity IS 'Número máximo de vigilantes permitidos neste júri';
COMMENT ON COLUMN juries.requires_supervisor IS 'Indica se o júri requer supervisor obrigatoriamente';

-- 2. ADICIONAR CAMPOS AUXILIARES EM JURY_VIGILANTES
-- ============================================================================

ALTER TABLE jury_vigilantes
ADD COLUMN IF NOT EXISTS jury_exam_date DATE NULL AFTER vigilante_id,
ADD COLUMN IF NOT EXISTS jury_start_time TIME NULL AFTER jury_exam_date,
ADD COLUMN IF NOT EXISTS jury_end_time TIME NULL AFTER jury_start_time;

CREATE INDEX IF NOT EXISTS idx_jv_vigilante_datetime ON jury_vigilantes (vigilante_id, jury_exam_date, jury_start_time, jury_end_time);

-- 3. FUNÇÃO: VERIFICAR CAPACIDADE DE VIGILANTES
-- ============================================================================

DELIMITER $$

DROP TRIGGER IF EXISTS trg_check_vigilantes_capacity$$

CREATE TRIGGER trg_check_vigilantes_capacity 
BEFORE INSERT ON jury_vigilantes
FOR EACH ROW
BEGIN
    DECLARE v_capacity INT;
    DECLARE v_current_count INT;
    DECLARE v_exam_date DATE;
    DECLARE v_start_time TIME;
    DECLARE v_end_time TIME;
    
    -- Buscar capacidade e dados do júri
    SELECT vigilantes_capacity, exam_date, start_time, end_time
    INTO v_capacity, v_exam_date, v_start_time, v_end_time
    FROM juries 
    WHERE id = NEW.jury_id;
    
    -- Contar vigilantes já alocados
    SELECT COUNT(*) 
    INTO v_current_count
    FROM jury_vigilantes 
    WHERE jury_id = NEW.jury_id;
    
    -- Verificar se excede capacidade
    IF v_current_count >= v_capacity THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Capacidade máxima de vigilantes atingida para este júri';
    END IF;
    
    -- Preencher campos auxiliares automaticamente
    SET NEW.jury_exam_date = v_exam_date;
    SET NEW.jury_start_time = v_start_time;
    SET NEW.jury_end_time = v_end_time;
END$$

DELIMITER ;

-- 4. FUNÇÃO: VERIFICAR CONFLITOS DE HORÁRIO DE VIGILANTE
-- ============================================================================

DELIMITER $$

DROP TRIGGER IF EXISTS trg_check_vigilante_conflicts$$

CREATE TRIGGER trg_check_vigilante_conflicts
BEFORE INSERT ON jury_vigilantes
FOR EACH ROW
BEGIN
    DECLARE v_conflict_count INT;
    
    -- Verificar se vigilante já está alocado em júri com horário conflitante
    SELECT COUNT(*)
    INTO v_conflict_count
    FROM jury_vigilantes jv
    INNER JOIN juries j ON j.id = jv.jury_id
    WHERE jv.vigilante_id = NEW.vigilante_id
      AND j.exam_date = (SELECT exam_date FROM juries WHERE id = NEW.jury_id)
      AND j.id != NEW.jury_id
      AND (
          -- Verifica sobreposição de horários: (start1 < end2) AND (start2 < end1)
          (j.start_time < (SELECT end_time FROM juries WHERE id = NEW.jury_id))
          AND
          ((SELECT start_time FROM juries WHERE id = NEW.jury_id) < j.end_time)
      );
    
    IF v_conflict_count > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Vigilante já está alocado em júri com horário conflitante no mesmo dia';
    END IF;
END$$

DELIMITER ;

-- 5. FUNÇÃO: VERIFICAR CONFLITOS DE HORÁRIO DE SUPERVISOR
-- ============================================================================

DELIMITER $$

DROP TRIGGER IF EXISTS trg_check_supervisor_conflicts$$

CREATE TRIGGER trg_check_supervisor_conflicts
BEFORE UPDATE ON juries
FOR EACH ROW
BEGIN
    DECLARE v_conflict_count INT;
    
    -- Apenas verificar se supervisor_id está sendo alterado e não é NULL
    IF NEW.supervisor_id IS NOT NULL AND (OLD.supervisor_id IS NULL OR OLD.supervisor_id != NEW.supervisor_id) THEN
        
        -- Verificar se supervisor já está alocado em júri com horário conflitante
        SELECT COUNT(*)
        INTO v_conflict_count
        FROM juries j
        WHERE j.supervisor_id = NEW.supervisor_id
          AND j.exam_date = NEW.exam_date
          AND j.id != NEW.id
          AND (
              -- Verifica sobreposição de horários
              (j.start_time < NEW.end_time) AND (NEW.start_time < j.end_time)
          );
        
        IF v_conflict_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Supervisor já está alocado em júri com horário conflitante no mesmo dia';
        END IF;
    END IF;
END$$

DELIMITER ;

-- 6. VIEW: SCORE DE CARGA POR VIGILANTE/SUPERVISOR
-- ============================================================================

CREATE OR REPLACE VIEW vw_vigilante_workload AS
SELECT 
    u.id AS user_id,
    u.name,
    u.email,
    u.role,
    u.available_for_vigilance,
    u.supervisor_eligible,
    COALESCE(COUNT(DISTINCT jv.jury_id), 0) AS vigilance_count,
    COALESCE(COUNT(DISTINCT js.id), 0) AS supervision_count,
    -- Score: vigilância = 1 ponto, supervisão = 2 pontos
    COALESCE(COUNT(DISTINCT jv.jury_id), 0) + (COALESCE(COUNT(DISTINCT js.id), 0) * 2) AS workload_score,
    GROUP_CONCAT(DISTINCT DATE_FORMAT(j.exam_date, '%d/%m/%Y') ORDER BY j.exam_date SEPARATOR ', ') AS exam_dates
FROM users u
LEFT JOIN jury_vigilantes jv ON jv.vigilante_id = u.id
LEFT JOIN juries j ON j.id = jv.jury_id
LEFT JOIN juries js ON js.supervisor_id = u.id
WHERE u.role IN ('vigilante', 'membro', 'coordenador')
GROUP BY u.id, u.name, u.email, u.role, u.available_for_vigilance, u.supervisor_eligible;

-- 7. VIEW: SLOTS E OCUPAÇÃO DOS JÚRIS
-- ============================================================================

CREATE OR REPLACE VIEW vw_jury_slots AS
SELECT 
    j.id AS jury_id,
    j.subject,
    j.exam_date,
    j.start_time,
    j.end_time,
    j.location,
    j.room,
    j.candidates_quota,
    j.vigilantes_capacity,
    j.requires_supervisor,
    COALESCE(COUNT(jv.id), 0) AS vigilantes_allocated,
    j.vigilantes_capacity - COALESCE(COUNT(jv.id), 0) AS vigilantes_available,
    CASE 
        WHEN j.supervisor_id IS NOT NULL THEN 1 
        ELSE 0 
    END AS has_supervisor,
    j.supervisor_id,
    s.name AS supervisor_name,
    CASE
        WHEN COALESCE(COUNT(jv.id), 0) < j.vigilantes_capacity THEN 'incomplete'
        WHEN COALESCE(COUNT(jv.id), 0) = j.vigilantes_capacity THEN 'full'
        ELSE 'overfilled'
    END AS occupancy_status
FROM juries j
LEFT JOIN jury_vigilantes jv ON jv.jury_id = j.id
LEFT JOIN users s ON s.id = j.supervisor_id
GROUP BY j.id, j.subject, j.exam_date, j.start_time, j.end_time, j.location, 
         j.room, j.candidates_quota, j.vigilantes_capacity, j.requires_supervisor, 
         j.supervisor_id, s.name;

-- 8. VIEW: VIGILANTES ELEGÍVEIS POR JÚRI (SEM CONFLITOS)
-- ============================================================================

CREATE OR REPLACE VIEW vw_eligible_vigilantes AS
SELECT 
    j.id AS jury_id,
    j.subject,
    j.exam_date,
    j.start_time,
    j.end_time,
    u.id AS vigilante_id,
    u.name AS vigilante_name,
    u.email,
    vw.vigilance_count,
    vw.supervision_count,
    vw.workload_score,
    -- Verificar se tem conflito (0 = elegível, 1 = conflito)
    CASE 
        WHEN EXISTS (
            SELECT 1 
            FROM jury_vigilantes jv2
            INNER JOIN juries j2 ON j2.id = jv2.jury_id
            WHERE jv2.vigilante_id = u.id
              AND j2.exam_date = j.exam_date
              AND (j2.start_time < j.end_time AND j.start_time < j2.end_time)
        ) THEN 1
        ELSE 0
    END AS has_conflict
FROM juries j
CROSS JOIN users u
LEFT JOIN vw_vigilante_workload vw ON vw.user_id = u.id
WHERE u.role = 'vigilante' 
  AND u.available_for_vigilance = 1;

-- 9. VIEW: SUPERVISORES ELEGÍVEIS POR JÚRI (SEM CONFLITOS)
-- ============================================================================

CREATE OR REPLACE VIEW vw_eligible_supervisors AS
SELECT 
    j.id AS jury_id,
    j.subject,
    j.exam_date,
    j.start_time,
    j.end_time,
    u.id AS supervisor_id,
    u.name AS supervisor_name,
    u.email,
    vw.supervision_count,
    vw.workload_score,
    -- Verificar se tem conflito
    CASE 
        WHEN EXISTS (
            SELECT 1 
            FROM juries j2
            WHERE j2.supervisor_id = u.id
              AND j2.exam_date = j.exam_date
              AND j2.id != j.id
              AND (j2.start_time < j.end_time AND j.start_time < j2.end_time)
        ) THEN 1
        ELSE 0
    END AS has_conflict
FROM juries j
CROSS JOIN users u
LEFT JOIN vw_vigilante_workload vw ON vw.user_id = u.id
WHERE u.supervisor_eligible = 1;

-- 10. VIEW: ESTATÍSTICAS GERAIS DE ALOCAÇÃO
-- ============================================================================

CREATE OR REPLACE VIEW vw_allocation_stats AS
SELECT 
    COUNT(DISTINCT j.id) AS total_juries,
    SUM(j.vigilantes_capacity) AS total_capacity,
    COUNT(DISTINCT jv.id) AS total_allocated,
    SUM(j.vigilantes_capacity) - COUNT(DISTINCT jv.id) AS slots_available,
    COUNT(DISTINCT CASE WHEN j.supervisor_id IS NOT NULL THEN j.id END) AS juries_with_supervisor,
    COUNT(DISTINCT CASE WHEN j.supervisor_id IS NULL AND j.requires_supervisor = 1 THEN j.id END) AS juries_without_supervisor,
    ROUND(AVG(vw.workload_score), 2) AS avg_workload_score,
    ROUND(STDDEV(vw.workload_score), 2) AS workload_std_deviation,
    COUNT(DISTINCT CASE WHEN vw.workload_score = 0 THEN vw.user_id END) AS vigilantes_without_allocation
FROM juries j
LEFT JOIN jury_vigilantes jv ON jv.jury_id = j.id
LEFT JOIN vw_vigilante_workload vw ON vw.available_for_vigilance = 1;

-- 11. ATUALIZAR CAMPOS AUXILIARES EM JURY_VIGILANTES EXISTENTES
-- ============================================================================

UPDATE jury_vigilantes jv
INNER JOIN juries j ON j.id = jv.jury_id
SET 
    jv.jury_exam_date = j.exam_date,
    jv.jury_start_time = j.start_time,
    jv.jury_end_time = j.end_time
WHERE jv.jury_exam_date IS NULL;

-- 12. ÍNDICES ADICIONAIS PARA PERFORMANCE
-- ============================================================================

CREATE INDEX IF NOT EXISTS idx_users_availability ON users (available_for_vigilance, supervisor_eligible);
CREATE INDEX IF NOT EXISTS idx_juries_supervisor ON juries (supervisor_id);

-- ============================================================================
-- FIM DA MIGRATION
-- ============================================================================
-- 
-- PRÓXIMOS PASSOS:
-- 1. Executar: mysql -u usuario -p base < app/Database/allocation_improvements_migration.sql
-- 2. Implementar AllocationService em PHP
-- 3. Criar endpoints de API para validação e auto-alocação
-- 4. Melhorar UI com feedback visual em tempo real
-- 
-- ============================================================================
