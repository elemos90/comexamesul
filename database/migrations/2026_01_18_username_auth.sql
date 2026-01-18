-- Migration: Username-Based Authentication System
-- Date: 2026-01-18
-- Description: Migrates from email-based to username-based authentication

-- ============================================
-- PHASE 1: Add new columns to users table
-- ============================================

-- 1.1 Add username column (nullable initially for migration)
ALTER TABLE users
ADD COLUMN IF NOT EXISTS username VARCHAR(50) NULL UNIQUE AFTER name;

-- 1.2 Add password control flags
ALTER TABLE users
ADD COLUMN IF NOT EXISTS must_change_password BOOLEAN DEFAULT FALSE AFTER password_hash,
ADD COLUMN IF NOT EXISTS temp_password_at TIMESTAMP NULL AFTER must_change_password;

-- 1.3 Add profile completion flag
ALTER TABLE users
ADD COLUMN IF NOT EXISTS profile_complete BOOLEAN DEFAULT FALSE AFTER temp_password_at;

-- 1.4 Add additional profile fields for wizard
ALTER TABLE users
ADD COLUMN IF NOT EXISTS birth_date DATE NULL AFTER gender,
ADD COLUMN IF NOT EXISTS document_type VARCHAR(50) NULL AFTER birth_date,
ADD COLUMN IF NOT EXISTS document_number VARCHAR(50) NULL AFTER document_type,
ADD COLUMN IF NOT EXISTS bank_account_holder VARCHAR(150) NULL AFTER nib;

-- ============================================
-- PHASE 2: Create password reset requests table
-- ============================================

CREATE TABLE IF NOT EXISTS password_reset_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    resolved_by INT NULL,
    new_temp_password VARCHAR(255) NULL,
    status ENUM('pending', 'resolved', 'cancelled') DEFAULT 'pending',
    notes TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_requested_at (requested_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PHASE 3: Migrate existing usernames from email
-- ============================================

-- Generate username from email prefix (before @)
-- Handle duplicates by appending user ID
UPDATE users 
SET username = CONCAT(
    LOWER(REGEXP_REPLACE(SUBSTRING_INDEX(email, '@', 1), '[^a-zA-Z0-9._]', '')),
    CASE 
        WHEN (SELECT COUNT(*) FROM (SELECT id FROM users u2 WHERE LOWER(SUBSTRING_INDEX(u2.email, '@', 1)) = LOWER(SUBSTRING_INDEX(users.email, '@', 1))) AS dup) > 1 
        THEN CONCAT('_', id)
        ELSE ''
    END
)
WHERE username IS NULL AND email IS NOT NULL;

-- For users without email, use 'user_' + id
UPDATE users 
SET username = CONCAT('user_', id)
WHERE username IS NULL;

-- ============================================
-- PHASE 4: Make username required
-- ============================================

-- Ensure no NULL usernames before altering
UPDATE users SET username = CONCAT('user_', id) WHERE username IS NULL OR username = '';

-- Make username NOT NULL
ALTER TABLE users MODIFY username VARCHAR(50) NOT NULL;

-- ============================================
-- PHASE 5: Make email optional
-- ============================================

ALTER TABLE users MODIFY email VARCHAR(255) NULL;

-- ============================================
-- PHASE 6: Set profile_complete for existing users
-- ============================================

-- Mark profiles as complete if they have the required fields
UPDATE users 
SET profile_complete = TRUE
WHERE phone IS NOT NULL AND phone != ''
  AND nuit IS NOT NULL AND nuit != ''
  AND nib IS NOT NULL AND nib != ''
  AND bank_name IS NOT NULL AND bank_name != '';

-- ============================================
-- PHASE 7: Create indexes for better performance
-- ============================================

CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_users_profile_complete ON users(profile_complete);
CREATE INDEX IF NOT EXISTS idx_users_must_change_password ON users(must_change_password);

-- ============================================
-- PHASE 8: Audit log for auth changes
-- ============================================

-- Ensure user_audit_log exists and has proper structure
INSERT INTO user_audit_log (user_id, action, details, performed_by)
SELECT id, 'username_migration', CONCAT('Username set to: ', username), id
FROM users
WHERE username IS NOT NULL
ON DUPLICATE KEY UPDATE user_id = user_id;
