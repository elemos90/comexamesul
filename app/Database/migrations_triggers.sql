-- ============================================
-- TRIGGERS: Sistema de Alocação Automática
-- ============================================

DELIMITER $$

-- 1. Materializar janela temporal no INSERT
DROP TRIGGER IF EXISTS trg_jv_set_interval_bi$$

CREATE TRIGGER trg_jv_set_interval_bi
BEFORE INSERT ON jury_vigilantes
FOR EACH ROW
BEGIN
  SELECT inicio, fim INTO NEW.juri_inicio, NEW.juri_fim
  FROM juries
  WHERE id = NEW.jury_id;
END$$

-- 2. Materializar janela temporal no UPDATE
DROP TRIGGER IF EXISTS trg_jv_set_interval_bu$$

CREATE TRIGGER trg_jv_set_interval_bu
BEFORE UPDATE ON jury_vigilantes
FOR EACH ROW
BEGIN
  IF NEW.jury_id != OLD.jury_id THEN
    SELECT inicio, fim INTO NEW.juri_inicio, NEW.juri_fim
    FROM juries
    WHERE id = NEW.jury_id;
  END IF;
END$$

-- 3. Validar capacidade de vigilantes
DROP TRIGGER IF EXISTS trg_jv_check_cap$$

CREATE TRIGGER trg_jv_check_cap
BEFORE INSERT ON jury_vigilantes
FOR EACH ROW
BEGIN
  DECLARE cap INT DEFAULT 0;
  DECLARE qtd INT DEFAULT 0;
  
  IF NEW.papel = 'vigilante' THEN
    SELECT vigilantes_capacidade INTO cap
    FROM juries
    WHERE id = NEW.jury_id;
    
    SELECT COUNT(*) INTO qtd
    FROM jury_vigilantes
    WHERE jury_id = NEW.jury_id AND papel = 'vigilante';
    
    IF qtd >= cap THEN
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Capacidade de vigilantes atingida';
    END IF;
  END IF;
END$$

-- 4. Validar supervisor único
DROP TRIGGER IF EXISTS trg_jv_supervisor_unico$$

CREATE TRIGGER trg_jv_supervisor_unico
BEFORE INSERT ON jury_vigilantes
FOR EACH ROW
BEGIN
  DECLARE existe INT DEFAULT 0;
  
  IF NEW.papel = 'supervisor' THEN
    SELECT COUNT(*) INTO existe
    FROM jury_vigilantes
    WHERE jury_id = NEW.jury_id AND papel = 'supervisor';
    
    IF existe > 0 THEN
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Júri já possui supervisor';
    END IF;
  END IF;
END$$

-- 5. Prevenir conflito de horário no INSERT
DROP TRIGGER IF EXISTS trg_jv_no_overlap_ins$$

CREATE TRIGGER trg_jv_no_overlap_ins
BEFORE INSERT ON jury_vigilantes
FOR EACH ROW
BEGIN
  DECLARE confl INT DEFAULT 0;
  DECLARE inicio_novo DATETIME;
  DECLARE fim_novo DATETIME;
  
  SELECT inicio, fim INTO inicio_novo, fim_novo
  FROM juries
  WHERE id = NEW.jury_id;
  
  SELECT COUNT(*) INTO confl
  FROM jury_vigilantes jv
  WHERE jv.vigilante_id = NEW.vigilante_id
    AND jv.juri_inicio IS NOT NULL
    AND jv.juri_fim IS NOT NULL
    AND fim_novo > jv.juri_inicio
    AND inicio_novo < jv.juri_fim;
  
  IF confl > 0 THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Conflito de horário';
  END IF;
END$$

-- 6. Prevenir conflito de horário no UPDATE
DROP TRIGGER IF EXISTS trg_jv_no_overlap_upd$$

CREATE TRIGGER trg_jv_no_overlap_upd
BEFORE UPDATE ON jury_vigilantes
FOR EACH ROW
BEGIN
  DECLARE confl INT DEFAULT 0;
  DECLARE inicio_novo DATETIME;
  DECLARE fim_novo DATETIME;
  
  IF NEW.jury_id != OLD.jury_id OR NEW.vigilante_id != OLD.vigilante_id THEN
    SELECT inicio, fim INTO inicio_novo, fim_novo
    FROM juries
    WHERE id = NEW.jury_id;
    
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
END$$

DELIMITER ;

-- Verificar triggers criados
SHOW TRIGGERS LIKE 'jury_vigilantes';

SELECT '✅ Triggers criados com sucesso!' AS status;
