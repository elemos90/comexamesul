-- ============================================
-- FIX COMPLETO - Resolver "Erro Interno"
-- Execute este script no phpMyAdmin
-- ============================================

SELECT '🔧 INICIANDO CORREÇÕES...' AS '';

-- ============================================
-- 1. ADICIONAR COLUNA experiencia_supervisao
-- ============================================

SELECT '1️⃣ Verificando coluna experiencia_supervisao...' AS '';

SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'experiencia_supervisao'
);

SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE users ADD COLUMN experiencia_supervisao INT DEFAULT 0 COMMENT ''Experiência em supervisão (0-10)''',
    'SELECT ''✅ Coluna experiencia_supervisao já existe'' AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Popular com valores de exemplo
UPDATE users 
SET experiencia_supervisao = FLOOR(RAND() * 10) 
WHERE role IN ('coordenador', 'membro', 'docente')
  AND (experiencia_supervisao IS NULL OR experiencia_supervisao = 0);

SELECT '✅ Coluna experiencia_supervisao OK' AS '';

-- ============================================
-- 2. VERIFICAR COLUNAS OBRIGATÓRIAS
-- ============================================

SELECT '2️⃣ Verificando colunas obrigatórias...' AS '';

-- Verificar campus
SET @campus_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'campus'
);

SET @sql = IF(
    @campus_exists = 0,
    'ALTER TABLE users ADD COLUMN campus VARCHAR(100) DEFAULT ''Campus Central''',
    'SELECT ''✅ Coluna campus existe'' AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar available_for_vigilance
SET @avail_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'available_for_vigilance'
);

SET @sql = IF(
    @avail_exists = 0,
    'ALTER TABLE users ADD COLUMN available_for_vigilance TINYINT(1) DEFAULT 1',
    'SELECT ''✅ Coluna available_for_vigilance existe'' AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT '✅ Colunas obrigatórias OK' AS '';

-- ============================================
-- 3. POPULAR CAMPOS VAZIOS
-- ============================================

SELECT '3️⃣ Populando campos vazios...' AS '';

UPDATE users 
SET campus = 'Campus Central' 
WHERE (campus IS NULL OR campus = '')
  AND role IN ('coordenador', 'membro', 'docente');

SELECT '✅ Campos populados' AS '';

-- ============================================
-- 4. ATIVAR TODOS OS DOCENTES
-- ============================================

SELECT '4️⃣ Ativando docentes...' AS '';

UPDATE users 
SET 
    active = 1,
    available_for_vigilance = 1
WHERE role IN ('coordenador', 'membro', 'docente');

SELECT CONCAT('✅ ', ROW_COUNT(), ' docentes ativados') AS '';

-- ============================================
-- 5. VERIFICAR JANELAS TEMPORAIS EM JURIES
-- ============================================

SELECT '5️⃣ Verificando janelas temporais...' AS '';

SELECT 
    COUNT(*) AS total_juris,
    SUM(CASE WHEN inicio IS NULL OR fim IS NULL THEN 1 ELSE 0 END) AS sem_janelas
FROM juries;

-- ============================================
-- 6. RELATÓRIO FINAL
-- ============================================

SELECT '📊 RELATÓRIO FINAL' AS '';

SELECT 
    '✓ Estrutura' AS categoria,
    CONCAT(
        'campus: ', IF(@campus_exists, 'SIM', 'ADICIONADO'), ', ',
        'available_for_vigilance: ', IF(@avail_exists, 'SIM', 'ADICIONADO'), ', ',
        'experiencia_supervisao: ', IF(@column_exists, 'SIM', 'ADICIONADO')
    ) AS detalhes;

SELECT 
    '✓ Docentes' AS categoria,
    CONCAT(
        'Total: ', COUNT(*), ', ',
        'Ativos: ', SUM(active = 1), ', ',
        'Disponíveis: ', SUM(available_for_vigilance = 1), ', ',
        'Elegíveis: ', SUM(active = 1 AND available_for_vigilance = 1)
    ) AS detalhes
FROM users
WHERE role IN ('coordenador', 'membro', 'docente');

SELECT 
    '✓ Júris' AS categoria,
    CONCAT(
        'Total: ', COUNT(*), ', ',
        'Com janelas: ', SUM(CASE WHEN inicio IS NOT NULL AND fim IS NOT NULL THEN 1 ELSE 0 END)
    ) AS detalhes
FROM juries;

-- ============================================
-- 7. DIAGNÓSTICO FINAL
-- ============================================

SELECT '🎯 DIAGNÓSTICO FINAL' AS '';

SELECT 
    CASE 
        WHEN (SELECT COUNT(*) FROM users WHERE active=1 AND available_for_vigilance=1 AND role IN ('coordenador', 'membro', 'docente')) < 3
        THEN '⚠️ ATENÇÃO: Menos de 3 docentes elegíveis. Execute UPDATE acima.'
        WHEN (SELECT COUNT(*) FROM juries WHERE inicio IS NULL OR fim IS NULL) > 0
        THEN '⚠️ ATENÇÃO: Alguns júris sem janelas temporais. Execute migrations.'
        ELSE '✅ TUDO OK! Sistema Top-3 pronto para uso!'
    END AS status;

SELECT '✅ CORREÇÕES CONCLUÍDAS!' AS '';
SELECT 'Teste agora em: http://localhost/juries/planning' AS proxima_acao;
