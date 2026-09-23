<?php

declare(strict_types=1);

namespace ClubCore\Infrastructure\WordPress;

/**
 * Manages database migrations and tables.
 *
 * @package ClubCore\Infrastructure\WordPress
 */
class MigrationManager
{
    public const SCHEMA_VERSION = '1.0.0';

    /**
     * Run initial installation routines.
     */
    public static function install(): void
    {
        self::createTables();
    }

    /**
     * Checks if migrations are needed and runs them (admin only).
     */
    public static function checkAndMigrate(): void
    {
        if (!is_admin()) {
            return;
        }

        $currentVersion = get_option('clubcore_db_version', '0.0.0');
        if (version_compare((string) $currentVersion, self::SCHEMA_VERSION, '<')) {
            self::install();
            update_option('clubcore_db_version', self::SCHEMA_VERSION);
        }
    }

    /**
     * Creates or updates the database tables using dbDelta.
     * Compatible with MySQL 5.7+, MySQL 8.0+ and strict modes (NO_ZERO_DATE).
     */
    private static function createTables(): void
    {
        global $wpdb;
        $charsetCollate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $prefix = $wpdb->prefix;

        $sqlMembers = "CREATE TABLE {$prefix}clubcore_members (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned DEFAULT 0,
            phone_normalized varchar(20) NOT NULL,
            phone_display varchar(30) DEFAULT '',
            first_name varchar(100) DEFAULT '',
            last_name varchar(100) DEFAULT '',
            email varchar(200) DEFAULT '',
            membership_status varchar(30) DEFAULT 'active',
            membership_source varchar(30) DEFAULT 'admin',
            membership_created_at datetime DEFAULT NULL,
            membership_created_at_gmt datetime DEFAULT NULL,
            created_by bigint(20) unsigned DEFAULT 0,
            updated_at datetime DEFAULT NULL,
            updated_at_gmt datetime DEFAULT NULL,
            last_sms_status varchar(30) DEFAULT '',
            last_sms_sent_at datetime DEFAULT NULL,
            woocommerce_linked tinyint(1) DEFAULT 0,
            PRIMARY KEY  (id),
            UNIQUE KEY phone_normalized (phone_normalized),
            KEY user_id (user_id),
            KEY membership_status (membership_status),
            KEY membership_created_at (membership_created_at)
        ) $charsetCollate;";

        $sqlSmsLogs = "CREATE TABLE {$prefix}clubcore_sms_logs (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            member_id bigint(20) unsigned DEFAULT 0,
            user_id bigint(20) unsigned DEFAULT 0,
            phone varchar(20) NOT NULL,
            pattern_id varchar(50) DEFAULT '',
            provider varchar(50) DEFAULT '',
            request_type varchar(30) DEFAULT 'welcome',
            status varchar(30) DEFAULT 'pending',
            provider_reference varchar(100) DEFAULT '',
            provider_error_code varchar(30) DEFAULT '',
            provider_error_message text DEFAULT NULL,
            created_at datetime DEFAULT NULL,
            created_by bigint(20) unsigned DEFAULT 0,
            PRIMARY KEY  (id),
            KEY member_id (member_id),
            KEY phone (phone),
            KEY request_type (request_type),
            KEY status (status),
            KEY created_at (created_at)
        ) $charsetCollate;";

        $sqlAuditLogs = "CREATE TABLE {$prefix}clubcore_audit_logs (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            actor_user_id bigint(20) unsigned DEFAULT 0,
            action varchar(100) NOT NULL,
            object_type varchar(50) DEFAULT '',
            object_id bigint(20) unsigned DEFAULT 0,
            result varchar(30) DEFAULT 'success',
            context text DEFAULT NULL,
            ip_hash varchar(64) DEFAULT '',
            created_at datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY actor_user_id (actor_user_id),
            KEY action (action),
            KEY object_type (object_type),
            KEY created_at (created_at)
        ) $charsetCollate;";

        $sqlImportJobs = "CREATE TABLE {$prefix}clubcore_import_jobs (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            created_by bigint(20) unsigned DEFAULT 0,
            file_name varchar(255) DEFAULT '',
            source_type varchar(30) DEFAULT 'csv',
            status varchar(30) DEFAULT 'pending',
            total_rows int(10) unsigned DEFAULT 0,
            processed_rows int(10) unsigned DEFAULT 0,
            success_rows int(10) unsigned DEFAULT 0,
            failed_rows int(10) unsigned DEFAULT 0,
            skipped_rows int(10) unsigned DEFAULT 0,
            send_sms tinyint(1) DEFAULT 0,
            import_mode varchar(30) DEFAULT 'skip_duplicates',
            error_summary text DEFAULT NULL,
            started_at datetime DEFAULT NULL,
            completed_at datetime DEFAULT NULL,
            created_at datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY status (status)
        ) $charsetCollate;";

        dbDelta($sqlMembers);
        dbDelta($sqlSmsLogs);
        dbDelta($sqlAuditLogs);
        dbDelta($sqlImportJobs);
    }
}
