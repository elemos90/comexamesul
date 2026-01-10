-- ============================================================================
-- CORREÇÃO: Remover requisito de supervisor_eligible das views
-- Data: 2026-01-10
-- Descrição: Qualquer vigilante com disponibilidade pode ser supervisor
-- ============================================================================

-- 1. Atualizar VIEW: vw_eligible_supervisors
-- MUDANÇA: Remover supervisor_eligible = 1, usar available_for_vigilance = 1
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
    u.supervisor_eligible,
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
WHERE u.role = 'vigilante' 
  AND u.available_for_vigilance = 1;

-- ============================================================================
-- FIM DA MIGRAÇÃO
-- ============================================================================

