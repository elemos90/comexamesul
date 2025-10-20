-- ============================================
-- MIGRATIONS SIMPLIFICADAS: Dados Mestres
-- ============================================
-- Sem triggers - validação no backend
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
    capacity INT NULL,
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
    capacity INT NOT NULL DEFAULT 30,
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
-- DADOS DE EXEMPLO
-- ============================================

-- Disciplinas
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

-- Locais
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

-- Verificação
SELECT '✅ Tabelas criadas!' AS '';
SELECT CONCAT('Disciplinas: ', COUNT(*)) AS '' FROM disciplines;
SELECT CONCAT('Locais: ', COUNT(*)) AS '' FROM exam_locations;
SELECT CONCAT('Salas: ', COUNT(*)) AS '' FROM exam_rooms;
