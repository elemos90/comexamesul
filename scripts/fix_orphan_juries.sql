-- ================================================
-- DIAGNÓSTICO E CORREÇÃO: Júris Órfãos
-- ================================================

-- 1. IDENTIFICAR JÚRIS SEM VAGA (órfãos)
SELECT 
    'JÚRIS ÓRFÃOS' as tipo,
    COUNT(*) as total,
    GROUP_CONCAT(DISTINCT subject ORDER BY subject) as disciplinas
FROM juries 
WHERE vacancy_id IS NULL;

-- 2. LISTAR JÚRIS ÓRFÃOS (detalhes)
SELECT 
    id,
    subject,
    exam_date,
    location,
    room,
    candidates_quota,
    created_at
FROM juries 
WHERE vacancy_id IS NULL
ORDER BY exam_date, subject;

-- 3. VERIFICAR VAGAS DISPONÍVEIS
SELECT 
    id,
    title,
    opening_date,
    closing_date,
    exam_date
FROM exam_vacancies
ORDER BY id DESC
LIMIT 10;

-- ================================================
-- OPÇÃO A: ASSOCIAR JÚRIS ÓRFÃOS A UMA VAGA
-- ================================================

-- IMPORTANTE: Troque X pelo ID da vaga correta
-- SET @vacancy_id_to_use = X;

-- UPDATE juries 
-- SET vacancy_id = @vacancy_id_to_use
-- WHERE vacancy_id IS NULL;

-- ================================================
-- OPÇÃO B: REMOVER JÚRIS ÓRFÃOS (CUIDADO!)
-- ================================================

-- AVISO: Isso remove permanentemente os júris e suas alocações!
-- Descomente apenas se tiver certeza:

-- DELETE FROM jury_vigilantes WHERE jury_id IN (SELECT id FROM juries WHERE vacancy_id IS NULL);
-- DELETE FROM juries WHERE vacancy_id IS NULL;

-- ================================================
-- VERIFICAÇÃO FINAL
-- ================================================

SELECT 
    'JÚRIS COM VAGA' as tipo,
    COUNT(*) as total
FROM juries 
WHERE vacancy_id IS NOT NULL

UNION ALL

SELECT 
    'JÚRIS SEM VAGA' as tipo,
    COUNT(*) as total
FROM juries 
WHERE vacancy_id IS NULL;
