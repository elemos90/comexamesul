-- ============================================
-- MIGRATIONS: DADOS MESTRES (Disciplinas, Locais, Salas)
-- ============================================
-- Descrição: Cadastro centralizado de disciplinas, locais e salas
-- Autor: Sistema de Gestão de Júris
-- Data: 11/10/2025
-- ============================================

-- 1. TABELA: Disciplinas
CREATE TABLE IF NOT EXISTS disciplines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(180) NOT NULL,
    description TEXT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_disciplines_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_disciplines_active (active),
    INDEX idx_disciplines_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABELA: Locais de Realização
CREATE TABLE IF NOT EXISTS exam_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    address VARCHAR(255) NULL,
    city VARCHAR(100) NULL,
    capacity INT NULL COMMENT 'Capacidade total de candidatos',
    description TEXT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_locations_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_locations_active (active),
    INDEX idx_locations_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABELA: Salas dos Locais
CREATE TABLE IF NOT EXISTS exam_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location_id INT NOT NULL,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(60) NOT NULL,
    capacity INT NOT NULL DEFAULT 30 COMMENT 'Capacidade de candidatos',
    floor VARCHAR(20) NULL,
    building VARCHAR(50) NULL,
    notes TEXT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_room_location_code (location_id, code),
    CONSTRAINT fk_rooms_location FOREIGN KEY (location_id) REFERENCES exam_locations (id) ON DELETE CASCADE,
    CONSTRAINT fk_rooms_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_rooms_active (active),
    INDEX idx_rooms_location (location_id, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MODIFICAR TABELA JURIES
-- ============================================
-- Adicionar foreign keys para disciplina, local e sala

ALTER TABLE juries 
    ADD COLUMN discipline_id INT NULL AFTER id,
    ADD COLUMN location_id INT NULL AFTER candidates_quota,
    ADD COLUMN room_id INT NULL AFTER location_id;

-- Adicionar índices e constraints (apenas se não existirem)
ALTER TABLE juries 
    ADD CONSTRAINT fk_juries_discipline FOREIGN KEY (discipline_id) REFERENCES disciplines (id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_juries_location FOREIGN KEY (location_id) REFERENCES exam_locations (id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_juries_room FOREIGN KEY (room_id) REFERENCES exam_rooms (id) ON DELETE SET NULL;

ALTER TABLE juries
    ADD INDEX idx_juries_discipline (discipline_id),
    ADD INDEX idx_juries_location_date (location_id, exam_date),
    ADD INDEX idx_juries_room_schedule (room_id, exam_date, start_time, end_time);

-- ============================================
-- TRIGGER: Validar conflito de sala
-- ============================================
-- Impede que a mesma sala seja alocada no mesmo horário

DELIMITER $$

DROP TRIGGER IF EXISTS trg_validate_room_conflict$$

CREATE TRIGGER trg_validate_room_conflict
BEFORE INSERT ON juries
FOR EACH ROW
BEGIN
    DECLARE conflict_count INT DEFAULT 0;
    
    -- Verificar se a sala já está ocupada no mesmo horário
    IF NEW.room_id IS NOT NULL THEN
        SELECT COUNT(*) INTO conflict_count
        FROM juries
        WHERE room_id = NEW.room_id
          AND exam_date = NEW.exam_date
          AND id != COALESCE(NEW.id, 0)
          AND (
              -- Horários sobrepostos
              (NEW.start_time < end_time AND NEW.end_time > start_time)
          );
        
        IF conflict_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Conflito: Esta sala já está ocupada neste horário.';
        END IF;
    END IF;
END$$

DELIMITER ;

-- ============================================
-- TRIGGER: Validar conflito de sala (UPDATE)
-- ============================================

DELIMITER $$

DROP TRIGGER IF EXISTS trg_validate_room_conflict_update$$

CREATE TRIGGER trg_validate_room_conflict_update
BEFORE UPDATE ON juries
FOR EACH ROW
BEGIN
    DECLARE conflict_count INT DEFAULT 0;
    
    IF NEW.room_id IS NOT NULL THEN
        SELECT COUNT(*) INTO conflict_count
        FROM juries
        WHERE room_id = NEW.room_id
          AND exam_date = NEW.exam_date
          AND id != NEW.id
          AND (
              (NEW.start_time < end_time AND NEW.end_time > start_time)
          );
        
        IF conflict_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Conflito: Esta sala já está ocupada neste horário.';
        END IF;
    END IF;
END$$

DELIMITER ;

-- ============================================
-- VIEW: Júris com informações completas
-- ============================================
-- View para facilitar queries com joins

CREATE OR REPLACE VIEW vw_juries_full AS
SELECT 
    j.id,
    j.exam_date,
    j.start_time,
    j.end_time,
    j.candidates_quota,
    j.notes,
    j.supervisor_id,
    j.approved_by,
    j.created_by,
    j.created_at,
    j.updated_at,
    
    -- Disciplina
    j.discipline_id,
    d.code AS discipline_code,
    d.name AS discipline_name,
    j.subject AS subject_legacy,
    
    -- Local
    j.location_id,
    el.code AS location_code,
    el.name AS location_name,
    el.address AS location_address,
    j.location AS location_legacy,
    
    -- Sala
    j.room_id,
    er.code AS room_code,
    er.name AS room_name,
    er.capacity AS room_capacity,
    er.floor AS room_floor,
    er.building AS room_building,
    j.room AS room_legacy,
    
    -- Supervisor
    us.name AS supervisor_name,
    us.email AS supervisor_email
    
FROM juries j
LEFT JOIN disciplines d ON d.id = j.discipline_id
LEFT JOIN exam_locations el ON el.id = j.location_id
LEFT JOIN exam_rooms er ON er.id = j.room_id
LEFT JOIN users us ON us.id = j.supervisor_id;

-- ============================================
-- DADOS DE EXEMPLO (SEED)
-- ============================================

-- Disciplinas de exemplo
INSERT IGNORE INTO disciplines (code, name, description, active, created_at, updated_at) VALUES
('MAT1', 'Matemática I', 'Matemática básica e introdução ao cálculo', 1, NOW(), NOW()),
('MAT2', 'Matemática II', 'Cálculo diferencial e integral', 1, NOW(), NOW()),
('FIS1', 'Física I', 'Mecânica clássica e introdução à física', 1, NOW(), NOW()),
('QUI1', 'Química I', 'Química geral e inorgânica', 1, NOW(), NOW()),
('BIO1', 'Biologia I', 'Biologia celular e molecular', 1, NOW(), NOW()),
('POR1', 'Português I', 'Gramática e interpretação de texto', 1, NOW(), NOW()),
('ING1', 'Inglês I', 'Inglês básico e comunicação', 1, NOW(), NOW()),
('HIS1', 'História I', 'História de Moçambique', 1, NOW(), NOW()),
('GEO1', 'Geografia I', 'Geografia física e humana', 1, NOW(), NOW()),
('INF1', 'Informática I', 'Introdução à informática', 1, NOW(), NOW());

-- Locais de exemplo
INSERT IGNORE INTO exam_locations (code, name, address, city, capacity, active, created_at, updated_at) VALUES
('CC', 'Campus Central', 'Av. Principal, s/n', 'Beira', 500, 1, NOW(), NOW()),
('ES1', 'Escola Secundária Samora Machel', 'Rua da Educação, 123', 'Beira', 300, 1, NOW(), NOW()),
('ES2', 'Escola Secundária Eduardo Mondlane', 'Av. Julius Nyerere, 456', 'Beira', 250, 1, NOW(), NOW()),
('CB', 'Campus Bairro', 'Bairro Central, Lote 10', 'Beira', 200, 1, NOW(), NOW());

-- Salas do Campus Central
INSERT IGNORE INTO exam_rooms (location_id, code, name, capacity, floor, building, active, created_at, updated_at)
SELECT id, '101', 'Sala 101', 35, 'Piso 1', 'Bloco A', 1, NOW(), NOW() FROM exam_locations WHERE code = 'CC'
UNION ALL
SELECT id, '102', 'Sala 102', 40, 'Piso 1', 'Bloco A', 1, NOW(), NOW() FROM exam_locations WHERE code = 'CC'
UNION ALL
SELECT id, '103', 'Sala 103', 30, 'Piso 1', 'Bloco A', 1, NOW(), NOW() FROM exam_locations WHERE code = 'CC'
UNION ALL
SELECT id, '201', 'Sala 201', 35, 'Piso 2', 'Bloco A', 1, NOW(), NOW() FROM exam_locations WHERE code = 'CC'
UNION ALL
SELECT id, '202', 'Sala 202', 40, 'Piso 2', 'Bloco A', 1, NOW(), NOW() FROM exam_locations WHERE code = 'CC'
UNION ALL
SELECT id, 'AUD1', 'Auditório Principal', 100, 'Piso Térreo', 'Bloco B', 1, NOW(), NOW() FROM exam_locations WHERE code = 'CC'
UNION ALL
SELECT id, 'LAB1', 'Laboratório de Informática', 25, 'Piso 1', 'Bloco C', 1, NOW(), NOW() FROM exam_locations WHERE code = 'CC';

-- Salas da Escola Secundária Samora Machel
INSERT IGNORE INTO exam_rooms (location_id, code, name, capacity, floor, building, active, created_at, updated_at)
SELECT id, 'A1', 'Sala A1', 30, 'Piso 1', 'Edifício Principal', 1, NOW(), NOW() FROM exam_locations WHERE code = 'ES1'
UNION ALL
SELECT id, 'A2', 'Sala A2', 30, 'Piso 1', 'Edifício Principal', 1, NOW(), NOW() FROM exam_locations WHERE code = 'ES1'
UNION ALL
SELECT id, 'A3', 'Sala A3', 35, 'Piso 1', 'Edifício Principal', 1, NOW(), NOW() FROM exam_locations WHERE code = 'ES1'
UNION ALL
SELECT id, 'B1', 'Sala B1', 30, 'Piso 2', 'Edifício Principal', 1, NOW(), NOW() FROM exam_locations WHERE code = 'ES1'
UNION ALL
SELECT id, 'B2', 'Sala B2', 30, 'Piso 2', 'Edifício Principal', 1, NOW(), NOW() FROM exam_locations WHERE code = 'ES1';

-- Salas da Escola Secundária Eduardo Mondlane
INSERT IGNORE INTO exam_rooms (location_id, code, name, capacity, floor, building, active, created_at, updated_at)
SELECT id, '1A', 'Sala 1A', 28, 'Piso 1', NULL, 1, NOW(), NOW() FROM exam_locations WHERE code = 'ES2'
UNION ALL
SELECT id, '1B', 'Sala 1B', 28, 'Piso 1', NULL, 1, NOW(), NOW() FROM exam_locations WHERE code = 'ES2'
UNION ALL
SELECT id, '2A', 'Sala 2A', 32, 'Piso 2', NULL, 1, NOW(), NOW() FROM exam_locations WHERE code = 'ES2'
UNION ALL
SELECT id, '2B', 'Sala 2B', 32, 'Piso 2', NULL, 1, NOW(), NOW() FROM exam_locations WHERE code = 'ES2';

-- Salas do Campus Bairro
INSERT IGNORE INTO exam_rooms (location_id, code, name, capacity, floor, building, active, created_at, updated_at)
SELECT id, 'S1', 'Sala 1', 25, 'Térreo', NULL, 1, NOW(), NOW() FROM exam_locations WHERE code = 'CB'
UNION ALL
SELECT id, 'S2', 'Sala 2', 25, 'Térreo', NULL, 1, NOW(), NOW() FROM exam_locations WHERE code = 'CB'
UNION ALL
SELECT id, 'S3', 'Sala 3', 30, 'Térreo', NULL, 1, NOW(), NOW() FROM exam_locations WHERE code = 'CB';

-- ============================================
-- VERIFICAÇÃO
-- ============================================

SELECT '✅ Tabelas criadas com sucesso!' AS '';
SELECT CONCAT('📚 Disciplinas cadastradas: ', COUNT(*)) AS '' FROM disciplines;
SELECT CONCAT('📍 Locais cadastrados: ', COUNT(*)) AS '' FROM exam_locations;
SELECT CONCAT('🏛️ Salas cadastradas: ', COUNT(*)) AS '' FROM exam_rooms;
