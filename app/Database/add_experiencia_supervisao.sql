-- ============================================
-- Adicionar coluna experiencia_supervisao
-- ============================================

-- Verificar e adicionar se não existir
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
    'SELECT ''Coluna experiencia_supervisao já existe'' AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Popular com valores de exemplo (opcional)
UPDATE users 
SET experiencia_supervisao = FLOOR(RAND() * 10) 
WHERE role IN ('coordenador', 'membro', 'docente')
  AND experiencia_supervisao = 0;

-- Verificar resultado
SELECT 
    COUNT(*) AS total_docentes,
    AVG(experiencia_supervisao) AS experiencia_media,
    MAX(experiencia_supervisao) AS max_experiencia
FROM users
WHERE role IN ('coordenador', 'membro', 'docente');

SELECT 'Coluna experiencia_supervisao adicionada/verificada com sucesso!' AS resultado;
