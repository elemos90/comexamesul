-- ============================================
-- SCRIPT DE VERIFICAÇÃO - Sistema Top-3
-- Execute este script para verificar se tudo está configurado corretamente
-- ============================================

SELECT '=== VERIFICAÇÃO DO SISTEMA TOP-3 ===' AS '';

-- 1. Verificar colunas em juries
SELECT '1. Verificando colunas em JURIES...' AS '';
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'comexamesul'
  AND TABLE_NAME = 'juries'
  AND COLUMN_NAME IN ('inicio', 'fim', 'vigilantes_capacidade', 'location');

-- 2. Verificar colunas em jury_vigilantes
SELECT '2. Verificando colunas em JURY_VIGILANTES...' AS '';
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'comexamesul'
  AND TABLE_NAME = 'jury_vigilantes'
  AND COLUMN_NAME IN ('papel', 'juri_inicio', 'juri_fim');

-- 3. Verificar colunas em users
SELECT '3. Verificando colunas em USERS...' AS '';
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'comexamesul'
  AND TABLE_NAME = 'users'
  AND COLUMN_NAME IN ('campus', 'experiencia_supervisao', 'available_for_vigilance', 'active');

-- 4. Verificar índices
SELECT '4. Verificando ÍNDICES...' AS '';
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'comexamesul'
  AND TABLE_NAME IN ('juries', 'jury_vigilantes')
  AND INDEX_NAME LIKE 'idx_%'
ORDER BY TABLE_NAME, INDEX_NAME;

-- 5. Contar júris com janelas temporais
SELECT '5. Verificando JÚRIS com janelas temporais...' AS '';
SELECT 
    COUNT(*) AS total_juris,
    SUM(CASE WHEN inicio IS NOT NULL AND fim IS NOT NULL THEN 1 ELSE 0 END) AS com_janela,
    SUM(CASE WHEN inicio IS NULL OR fim IS NULL THEN 1 ELSE 0 END) AS sem_janela
FROM juries;

-- 6. Contar docentes elegíveis
SELECT '6. Verificando DOCENTES elegíveis...' AS '';
SELECT 
    COUNT(*) AS total_docentes,
    SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) AS ativos,
    SUM(CASE WHEN available_for_vigilance = 1 THEN 1 ELSE 0 END) AS disponiveis,
    SUM(CASE WHEN active = 1 AND available_for_vigilance = 1 THEN 1 ELSE 0 END) AS elegiveis,
    SUM(CASE WHEN campus IS NOT NULL THEN 1 ELSE 0 END) AS com_campus,
    SUM(CASE WHEN experiencia_supervisao > 0 THEN 1 ELSE 0 END) AS com_experiencia
FROM users
WHERE role IN ('coordenador', 'membro', 'docente');

-- 7. Alocações existentes
SELECT '7. Verificando ALOCAÇÕES existentes...' AS '';
SELECT 
    COUNT(*) AS total_alocacoes,
    SUM(CASE WHEN papel = 'vigilante' THEN 1 ELSE 0 END) AS vigilantes,
    SUM(CASE WHEN papel = 'supervisor' THEN 1 ELSE 0 END) AS supervisores,
    SUM(CASE WHEN juri_inicio IS NOT NULL AND juri_fim IS NOT NULL THEN 1 ELSE 0 END) AS com_janela_materializada
FROM jury_vigilantes;

-- 8. Top-5 docentes por score
SELECT '8. TOP-5 DOCENTES por score...' AS '';
SELECT 
    u.id,
    u.name,
    u.campus,
    u.experiencia_supervisao,
    COALESCE(SUM(CASE WHEN jv.papel='vigilante' THEN 1 ELSE 0 END), 0) AS n_vigias,
    COALESCE(SUM(CASE WHEN jv.papel='supervisor' THEN 1 ELSE 0 END), 0) AS n_supervisoes,
    COALESCE(SUM(CASE 
        WHEN jv.papel='vigilante' THEN 1
        WHEN jv.papel='supervisor' THEN 2
        ELSE 0
    END), 0) AS score
FROM users u
LEFT JOIN jury_vigilantes jv ON jv.vigilante_id = u.id
WHERE u.role IN ('coordenador', 'membro', 'docente')
  AND u.active = 1
  AND u.available_for_vigilance = 1
GROUP BY u.id, u.name, u.campus, u.experiencia_supervisao
ORDER BY score ASC
LIMIT 5;

-- 9. Distribuição por campus
SELECT '9. Distribuição de DOCENTES por campus...' AS '';
SELECT 
    campus,
    COUNT(*) AS total,
    SUM(CASE WHEN active = 1 AND available_for_vigilance = 1 THEN 1 ELSE 0 END) AS elegiveis
FROM users
WHERE role IN ('coordenador', 'membro', 'docente')
GROUP BY campus
ORDER BY elegiveis DESC;

-- 10. Júris sem alocações (oportunidades para Top-3)
SELECT '10. JÚRIS sem alocações completas (use Top-3!)...' AS '';
SELECT 
    j.id,
    j.subject,
    j.location,
    j.room,
    j.exam_date,
    TIME(j.inicio) AS inicio,
    TIME(j.fim) AS fim,
    j.vigilantes_capacidade,
    COALESCE(COUNT(CASE WHEN jv.papel='vigilante' THEN 1 END), 0) AS vigilantes_alocados,
    CASE WHEN EXISTS(SELECT 1 FROM jury_vigilantes WHERE jury_id=j.id AND papel='supervisor') 
         THEN 'SIM' ELSE 'NÃO' END AS tem_supervisor
FROM juries j
LEFT JOIN jury_vigilantes jv ON jv.jury_id = j.id
WHERE j.inicio IS NOT NULL 
  AND j.fim IS NOT NULL
GROUP BY j.id, j.subject, j.location, j.room, j.exam_date, j.inicio, j.fim, j.vigilantes_capacidade
HAVING vigilantes_alocados < j.vigilantes_capacidade 
    OR tem_supervisor = 'NÃO'
ORDER BY j.exam_date, j.inicio
LIMIT 10;

-- 11. Resumo final
SELECT '=== RESUMO FINAL ===' AS '';
SELECT 
    'Júris criados' AS metrica,
    COUNT(*) AS valor
FROM juries
UNION ALL
SELECT 
    'Júris com janelas temporais',
    COUNT(*)
FROM juries
WHERE inicio IS NOT NULL AND fim IS NOT NULL
UNION ALL
SELECT 
    'Docentes elegíveis',
    COUNT(*)
FROM users
WHERE role IN ('coordenador', 'membro', 'docente')
  AND active = 1
  AND available_for_vigilance = 1
UNION ALL
SELECT 
    'Alocações totais',
    COUNT(*)
FROM jury_vigilantes
UNION ALL
SELECT 
    'Slots vazios (oportunidade Top-3)',
    SUM(j.vigilantes_capacidade) - COUNT(jv.id)
FROM juries j
LEFT JOIN jury_vigilantes jv ON jv.jury_id = j.id AND jv.papel = 'vigilante'
WHERE j.inicio IS NOT NULL;

-- 12. Diagnóstico
SELECT '=== DIAGNÓSTICO ===' AS '';
SELECT 
    CASE 
        WHEN (SELECT COUNT(*) FROM juries WHERE inicio IS NOT NULL) = 0 
        THEN '❌ ERRO: Nenhum júri com janelas temporais. Execute migrations!'
        WHEN (SELECT COUNT(*) FROM users WHERE active=1 AND available_for_vigilance=1) < 3
        THEN '⚠️  ATENÇÃO: Poucos docentes disponíveis (<3). Ative mais docentes!'
        WHEN (SELECT COUNT(*) FROM users WHERE campus IS NULL) > 0
        THEN '⚠️  ATENÇÃO: Alguns docentes sem campus. Popule campo campus!'
        ELSE '✅ TUDO OK! Sistema Top-3 pronto para uso!'
    END AS status;

SELECT '=== FIM DA VERIFICAÇÃO ===' AS '';
