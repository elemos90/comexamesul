-- ============================================
-- CRIAÇÃO DE TRIGGERS - Método Alternativo
-- Execute este arquivo completo de uma vez
-- ============================================

-- Limpar triggers existentes
DROP TRIGGER IF EXISTS trg_jv_set_interval_bi;
DROP TRIGGER IF EXISTS trg_jv_set_interval_bu;
DROP TRIGGER IF EXISTS trg_jv_check_cap;
DROP TRIGGER IF EXISTS trg_jv_supervisor_unico;
DROP TRIGGER IF EXISTS trg_jv_no_overlap_ins;
DROP TRIGGER IF EXISTS trg_jv_no_overlap_upd;

-- Procedimento para criar todos os triggers
DROP PROCEDURE IF EXISTS create_all_triggers;

DELIMITER //

CREATE PROCEDURE create_all_triggers()
BEGIN
    -- Este procedimento cria todos os triggers necessários
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
    BEGIN
        -- Ignorar erros se trigger já existir
    END;
    
    -- Nota: Como não podemos criar triggers dentro de procedures no MySQL,
    -- vamos usar uma abordagem diferente
    SELECT 'Execute os triggers manualmente um por um' AS mensagem;
END //

DELIMITER ;

-- Como alternativa, vamos tentar criar os triggers diretamente
-- Execute cada bloco SEPARADAMENTE no phpMyAdmin

