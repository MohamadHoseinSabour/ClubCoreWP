<?php
namespace ClubCore\Infrastructure\WordPress;

/**
 * Manages database migrations and tables.
 */
class MigrationManager {
    public const SCHEMA_VERSION = '1.0.0';

    /**
     * Run initial installation routines.
     */
    public static function install(): void {
        self::createTables();
    }

    /**
     * Checks if migrations are needed and runs them.
     */
    public static function checkAndMigrate(): void {
        $current_version = get_option('clubcore_db_version', '0.0.0');
        if (version_compare($current_version, self::SCHEMA_VERSION, '<')) {
            self::install();
            update_option('clubcore_db_version', self::SCHEMA_VERSION);
        }
    }

    /**
     * Creates or updates the database tables using dbDelta.
     */
    private static function createTables(): void {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $prefix = $wpdb->prefix;

        $sql_members = "CREATE TABLE {$prefix}clubcore_members (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED DEFAULT 0,
            phone_normalized VARCHAR(20) NOT NULL,
            phone_display VARCHAR(30) DEFAULT '',
            first_name VARCHAR(100) DEFAULT '',
            last_name VARCHAR(100) DEFAULT '',
            email VARCHAR(200) DEFAULT '',
            membership_status VARCHAR(30) DEFAULT 'active',
            membership_source VARCHAR(30) DEFAULT 'admin',
            membership_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            membership_created_at_gmt DATETIME DEFAULT CURRENT_TIMESTAMP,
            created_by BIGINT UNSIGNED DEFAULT 0,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            updated_at_gmt DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_sms_status VARCHAR(30) DEFAULT '',
            last_sms_sent_at DATETIME DEFAULT NULL,
            woocommerce_linked TINYINT(1) DEFAULT 0,
            UNIQUE KEY phone_normalized (phone_normalized),
            KEY user_id (user_id),
            KEY membership_status (membership_status),
            KEY membership_created_at (membership_created_at)
        ) $charset_collate;";

        $sql_sms_logs = "CREATE TABLE {$prefix}clubcore_sms_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            member_id BIGINT UNSIGNED DEFAULT 0,
            user_id BIGINT UNSIGNED DEFAULT 0,
            phone VARCHAR(20) NOT NULL,
            pattern_id VARCHAR(50) DEFAULT '',
            provider VARCHAR(50) DEFAULT '',
            request_type VARCHAR(30) DEFAULT 'welcome',
            status VARCHAR(30) DEFAULT 'pending',
            provider_reference VARCHAR(100) DEFAULT '',
            provider_error_code VARCHAR(30) DEFAULT '',
            provider_error_message TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            created_by BIGINT UNSIGNED DEFAULT 0,
            KEY member_id (member_id),
            KEY phone (phone),
            KEY request_type (request_type),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        $sql_audit_logs = "CREATE TABLE {$prefix}clubcore_audit_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            actor_user_id BIGINT UNSIGNED DEFAULT 0,
            action VARCHAR(100) NOT NULL,
            object_type VARCHAR(50) DEFAULT '',
            object_id BIGINT UNSIGNED DEFAULT 0,
            result VARCHAR(30) DEFAULT 'success',
            context TEXT DEFAULT NULL,
            ip_hash VARCHAR(64) DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY actor_user_id (actor_user_id),
            KEY action (action),
            KEY object_type (object_type),
            KEY created_at (created_at)
        ) $charset_collate;";

        $sql_import_jobs = "CREATE TABLE {$prefix}clubcore_import_jobs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            created_by BIGINT UNSIGNED DEFAULT 0,
            file_name VARCHAR(255) DEFAULT '',
            source_type VARCHAR(30) DEFAULT 'csv',
            status VARCHAR(30) DEFAULT 'pending',
            total_rows INT UNSIGNED DEFAULT 0,
            processed_rows INT UNSIGNED DEFAULT 0,
            success_rows INT UNSIGNED DEFAULT 0,
            failed_rows INT UNSIGNED DEFAULT 0,
            skipped_rows INT UNSIGNED DEFAULT 0,
            send_sms TINYINT(1) DEFAULT 0,
            import_mode VARCHAR(30) DEFAULT 'skip_duplicates',
            error_summary TEXT DEFAULT NULL,
            started_at DATETIME DEFAULT NULL,
            completed_at DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY status (status)
        ) $charset_collate;";

        dbDelta($sql_members);
        dbDelta($sql_sms_logs);
        dbDelta($sql_audit_logs);
        dbDelta($sql_import_jobs);
    }
}
