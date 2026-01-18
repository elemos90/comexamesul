-- ==============================================
-- INSTITUTIONAL NOTIFICATIONS MODULE
-- Migration: Notifications System
-- Date: 2026-01-17
-- ==============================================

-- Main notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('informativa', 'alerta', 'urgente') NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    context_type VARCHAR(50) NULL COMMENT 'jury, exam, payment, report, user, general',
    context_id INT NULL,
    is_automatic BOOLEAN DEFAULT FALSE,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_context (context_type, context_id),
    INDEX idx_created_at (created_at),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notification recipients and read status
CREATE TABLE IF NOT EXISTS notification_recipients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_recipient (notification_id, user_id),
    INDEX idx_user_unread (user_id, read_at),
    INDEX idx_notification (notification_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Delivery channels and status
CREATE TABLE IF NOT EXISTS notification_channels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id INT NOT NULL,
    channel ENUM('internal', 'email', 'sms') NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    error_message TEXT NULL,
    
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_notification (notification_id),
    INDEX idx_channel (channel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================
-- VERIFICATION QUERIES
-- ==============================================

-- Check tables created
SELECT 'Tables Created:' as Status;
SELECT TABLE_NAME 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME IN ('notifications', 'notification_recipients', 'notification_channels');

-- Show structure
DESCRIBE notifications;
DESCRIBE notification_recipients;
DESCRIBE notification_channels;
