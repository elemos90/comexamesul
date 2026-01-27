<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use App\Database\Connection;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we are using the test database connection
        // Environment variables should be set by phpunit.xml

        if (env('DB_CONNECTION') === 'sqlite') {
            $this->migrate();
        }
    }

    protected function navigateToTestDir()
    {
        // Helper if needed
    }

    protected function migrate()
    {
        try {
            $db = Connection::getInstance();

            // Register RAND function for SQLite compatibility (MySQL RAND() -> SQLite custom func)
            if ($db->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
                $db->sqliteCreateFunction('RAND', function () {
                    return mt_rand() / mt_getrandmax();
                });
            }

            // --- TABLES ---

            $db->exec("CREATE TABLE IF NOT EXISTS notifications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                type VARCHAR(50) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                context_type VARCHAR(50),
                context_id INTEGER,
                is_automatic BOOLEAN DEFAULT 0,
                created_by INTEGER,
                created_at DATETIME,
                updated_at DATETIME
            )");

            $db->exec("CREATE TABLE IF NOT EXISTS notification_recipients (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                notification_id INTEGER,
                user_id INTEGER,
                read_at DATETIME,
                created_at DATETIME
            )");

            $db->exec("CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255),
                username VARCHAR(255),
                email VARCHAR(255),
                phone VARCHAR(50),
                gender VARCHAR(20),
                birth_date DATE,
                document_type VARCHAR(50),
                document_number VARCHAR(50),
                origin_university VARCHAR(255),
                university VARCHAR(255),
                nuit VARCHAR(50),
                degree VARCHAR(100),
                major_area VARCHAR(100),
                bank_name VARCHAR(100),
                nib VARCHAR(50),
                bank_account_holder VARCHAR(255),
                role VARCHAR(50) DEFAULT 'user',
                email_verified_at DATETIME,
                verification_token VARCHAR(255),
                avatar_url VARCHAR(255),
                supervisor_eligible TINYINT DEFAULT 0,
                available_for_vigilance TINYINT DEFAULT 0,
                must_change_password TINYINT DEFAULT 0,
                profile_complete TINYINT DEFAULT 0,
                profile_completed TINYINT DEFAULT 0,
                profile_completed_at DATETIME,
                created_by INTEGER,
                created_at DATETIME,
                updated_at DATETIME
            )");

            $db->exec("CREATE TABLE IF NOT EXISTS juries (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                vacancy_id INTEGER,
                subject VARCHAR(255),
                exam_date DATE,
                start_time TIME,
                end_time TIME,
                room_id INTEGER,
                room VARCHAR(255),
                location VARCHAR(255),
                location_id INTEGER,
                candidates_quota INTEGER,
                vigilantes_capacity INTEGER DEFAULT 2,
                supervisor_id INTEGER,
                requires_supervisor TINYINT DEFAULT 1,
                notes TEXT,
                approved_by INTEGER,
                created_by INTEGER,
                created_at DATETIME,
                updated_at DATETIME
            )");

            $db->exec("CREATE TABLE IF NOT EXISTS jury_vigilantes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                jury_id INTEGER,
                vigilante_id INTEGER,
                assigned_by INTEGER,
                created_at DATETIME,
                updated_at DATETIME
            )");

            // --- VIEWS (Simplified for SQLite) ---

            // View: Workload
            // Simplified scoring logic for test
            $db->exec("
                CREATE VIEW IF NOT EXISTS vw_vigilante_workload AS
                SELECT 
                    u.id as user_id,
                    u.name,
                    u.email,
                    COUNT(jv.jury_id) as vigilance_count,
                    0 as supervision_count,
                    COUNT(jv.jury_id) * 1.0 as workload_score
                FROM users u
                LEFT JOIN jury_vigilantes jv ON jv.vigilante_id = u.id
                WHERE u.role IN ('vigilante', 'membro', 'coordenador')
                GROUP BY u.id, u.name, u.email
            ");

            // View: Jury Slots
            $db->exec("
                CREATE VIEW IF NOT EXISTS vw_jury_slots AS
                SELECT 
                    j.id as jury_id,
                    j.subject,
                    j.exam_date,
                    j.start_time,
                    j.end_time,
                    j.room,
                    j.vigilantes_capacity,
                    COUNT(jv.id) as vigilantes_allocated,
                    j.vigilantes_capacity - COUNT(jv.id) as vigilantes_available,
                    CASE
                        WHEN COUNT(jv.id) < j.vigilantes_capacity THEN 'incomplete'
                        WHEN COUNT(jv.id) = j.vigilantes_capacity THEN 'full'
                        ELSE 'overfilled'
                    END as occupancy_status
                FROM juries j
                LEFT JOIN jury_vigilantes jv ON jv.jury_id = j.id
                GROUP BY j.id
            ");

            // View: Eligible Vigilantes
            // Note: SQLite doesn't support CROSS JOIN nicely inside views with complex logic sometimes,
            // but standard syntax works. Simplified conflict check.
            $db->exec("
                CREATE VIEW IF NOT EXISTS vw_eligible_vigilantes AS
                SELECT 
                    j.id as jury_id,
                    j.subject,
                    j.exam_date,
                    u.id as vigilante_id,
                    u.name as vigilante_name,
                    vw.workload_score,
                    u.supervisor_eligible,
                    0 as has_conflict -- Simplified for unit tests (assume no conflict unless tested explicitly)
                FROM juries j
                JOIN users u ON u.role = 'vigilante' AND u.available_for_vigilance = 1
                LEFT JOIN vw_vigilante_workload vw ON vw.user_id = u.id
            ");

            // View: Stats
            $db->exec("
                CREATE VIEW IF NOT EXISTS vw_allocation_stats AS
                SELECT 
                    COUNT(*) as total_juries,
                    0 as avg_workload_score,
                    0 as workload_std_deviation
                FROM juries
            ");
        } catch (\PDOException $e) {
            file_put_contents(__DIR__ . '/../migration_error.txt', "Migration Error: " . $e->getMessage());
            throw $e;
        }
    }
}
