CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    gender ENUM('M','F','O') DEFAULT 'M',
    university VARCHAR(150) NULL,
    nuit VARCHAR(30) NULL,
    degree ENUM('Licenciado','Mestre','Doutor') DEFAULT 'Licenciado',
    major_area VARCHAR(150) NULL,
    bank_name VARCHAR(120) NULL,
    nib VARCHAR(32) NULL,
    role ENUM('vigilante','membro','coordenador') NOT NULL DEFAULT 'vigilante',
    password_hash VARCHAR(255) NOT NULL,
    email_verified_at DATETIME NULL,
    verification_token VARCHAR(120) NULL,
    avatar_url VARCHAR(255) NULL,
    supervisor_eligible TINYINT(1) NOT NULL DEFAULT 0,
    available_for_vigilance TINYINT(1) NOT NULL DEFAULT 0,
    created_by INT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_users_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS exam_vacancies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    deadline_at DATETIME NOT NULL,
    status ENUM('aberta','fechada','encerrada') NOT NULL DEFAULT 'aberta',
    created_by INT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_vacancies_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_vacancies_deadline (deadline_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS juries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(180) NOT NULL,
    exam_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    location VARCHAR(150) NOT NULL,
    room VARCHAR(60) NOT NULL,
    candidates_quota INT NOT NULL,
    notes TEXT NULL,
    supervisor_id INT NULL,
    approved_by INT NULL,
    created_by INT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_juries_supervisor FOREIGN KEY (supervisor_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT fk_juries_approved_by FOREIGN KEY (approved_by) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT fk_juries_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_juries_schedule (exam_date, start_time, end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS jury_vigilantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jury_id INT NOT NULL,
    vigilante_id INT NOT NULL,
    assigned_by INT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY uq_jury_vigilante (jury_id, vigilante_id),
    CONSTRAINT fk_jv_jury FOREIGN KEY (jury_id) REFERENCES juries (id) ON DELETE CASCADE,
    CONSTRAINT fk_jv_vigilante FOREIGN KEY (vigilante_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_jv_assigned_by FOREIGN KEY (assigned_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS exam_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jury_id INT NOT NULL,
    supervisor_id INT NOT NULL,
    present_m INT NOT NULL DEFAULT 0,
    present_f INT NOT NULL DEFAULT 0,
    absent_m INT NOT NULL DEFAULT 0,
    absent_f INT NOT NULL DEFAULT 0,
    total INT NOT NULL DEFAULT 0,
    occurrences TEXT NULL,
    submitted_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_exam_reports_jury (jury_id),
    CONSTRAINT fk_reports_jury FOREIGN KEY (jury_id) REFERENCES juries (id) ON DELETE CASCADE,
    CONSTRAINT fk_reports_supervisor FOREIGN KEY (supervisor_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    entity VARCHAR(120) NOT NULL,
    entity_id INT NULL,
    action VARCHAR(60) NOT NULL,
    metadata JSON NULL,
    ip VARCHAR(64) NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_activity_entity (entity, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(100) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_password_resets_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
