-- Migration: User Management System
-- Date: 2026-01-16

-- 1. Add columns to users table for account status
ALTER TABLE users
ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE,
ADD COLUMN IF NOT EXISTS deactivated_at TIMESTAMP NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS deactivation_reason TEXT NULL DEFAULT NULL;

-- 2. Create user_roles table for multiple role assignment
CREATE TABLE IF NOT EXISTS user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role ENUM('vigilante', 'supervisor', 'membro', 'coordenador') NOT NULL,
    assigned_by INT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_role (user_id, role),
    INDEX idx_user_id (user_id),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create user_audit_log table for tracking changes
CREATE TABLE IF NOT EXISTS user_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT NULL,
    performed_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Migrate existing roles from users.role to user_roles table
INSERT INTO user_roles (user_id, role, assigned_at)
SELECT id, role, created_at
FROM users
WHERE role IN ('vigilante', 'supervisor', 'membro', 'coordenador')
ON DUPLICATE KEY UPDATE user_id = user_id;

-- 5. Add hierarchical roles (Supervisor includes Vigilante)
INSERT INTO user_roles (user_id, role, assigned_at)
SELECT id, 'vigilante', created_at
FROM users
WHERE role = 'supervisor'
  AND id NOT IN (SELECT user_id FROM user_roles WHERE role = 'vigilante')
ON DUPLICATE KEY UPDATE user_id = user_id;

-- 6. Add hierarchical roles (Coordenador includes Membro)
INSERT INTO user_roles (user_id, role, assigned_at)
SELECT id, 'membro', created_at
FROM users
WHERE role = 'coordenador'
  AND id NOT IN (SELECT user_id FROM user_roles WHERE role = 'membro')
ON DUPLICATE KEY UPDATE user_id = user_id;

-- 7. Create index for better performance
CREATE INDEX idx_users_active ON users(is_active);
CREATE INDEX idx_users_email ON users(email);
