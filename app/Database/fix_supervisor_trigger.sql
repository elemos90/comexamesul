-- ============================================================================
-- FIX: Corrigir trigger de conflitos de supervisor
-- Data: 05/01/2026
-- Problema: Trigger não permitia atribuir mesmo supervisor a júris do mesmo 
--           exame (mesma disciplina, data e horário) mas em salas diferentes
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
        -- EXCLUINDO júris do mesmo exame (mesma disciplina, data e horário)
        SELECT COUNT(*)
        INTO v_conflict_count
        FROM juries j
        WHERE j.supervisor_id = NEW.supervisor_id
          AND j.exam_date = NEW.exam_date
          AND j.id != NEW.id
          -- Exclui júris do mesmo exame (mesma disciplina, mesma data, mesmo horário)
          AND NOT (
              j.subject = NEW.subject 
              AND j.start_time = NEW.start_time 
              AND j.end_time = NEW.end_time
          )
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

-- ============================================================================
-- PARA APLICAR ESTA CORREÇÃO:
-- Execute no MySQL: mysql -u root -p comexamesul < app/Database/fix_supervisor_trigger.sql
-- ============================================================================
