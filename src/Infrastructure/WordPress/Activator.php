<?php
namespace ClubCore\Infrastructure\WordPress;

/**
 * Handles plugin activation.
 */
class Activator {
    /**
     * Activate the plugin.
     */
    public static function activate(): void {
        MigrationManager::install();
        CapabilityManager::installCapabilities();
        
        // Calls SettingsManager::installDefaults() once created in Phase 4/5
        // if (class_exists('\ClubCore\Infrastructure\WordPress\SettingsManager')) {
        //     \ClubCore\Infrastructure\WordPress\SettingsManager::installDefaults();
        // }
        
        update_option('clubcore_db_version', MigrationManager::SCHEMA_VERSION);
        update_option('clubcore_plugin_version', defined('CLUBCORE_VERSION') ? CLUBCORE_VERSION : '1.0.0');
        
        flush_rewrite_rules();
    }
}
