-- Tabela para templates de locais reutilizáveis
CREATE TABLE IF NOT EXISTS location_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    location VARCHAR(150) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_location_templates_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_location_templates_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para disciplinas dentro dos templates
CREATE TABLE IF NOT EXISTS location_template_disciplines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    subject VARCHAR(180) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_template_disciplines_template FOREIGN KEY (template_id) REFERENCES location_templates (id) ON DELETE CASCADE,
    INDEX idx_template_disciplines_template (template_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para salas dentro das disciplinas do template
CREATE TABLE IF NOT EXISTS location_template_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    discipline_id INT NOT NULL,
    room VARCHAR(60) NOT NULL,
    candidates_quota INT NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_template_rooms_discipline FOREIGN KEY (discipline_id) REFERENCES location_template_disciplines (id) ON DELETE CASCADE,
    INDEX idx_template_rooms_discipline (discipline_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para estatísticas agregadas por local
CREATE TABLE IF NOT EXISTS location_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location VARCHAR(150) NOT NULL,
    exam_date DATE NOT NULL,
    total_juries INT NOT NULL DEFAULT 0,
    total_disciplines INT NOT NULL DEFAULT 0,
    total_candidates INT NOT NULL DEFAULT 0,
    total_vigilantes INT NOT NULL DEFAULT 0,
    total_supervisors INT NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_location_stats (location, exam_date),
    INDEX idx_location_stats_date (exam_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
