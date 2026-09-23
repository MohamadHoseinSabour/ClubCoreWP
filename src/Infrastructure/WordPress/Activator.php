<?php

declare(strict_types=1);

namespace ClubCore\Infrastructure\WordPress;

/**
 * Handles plugin activation.
 *
 * @package ClubCore\Infrastructure\WordPress
 */
class Activator
{
    /**
     * Activate the plugin.
     */
    public static function activate(): void
    {
        MigrationManager::install();
        CapabilityManager::installCapabilities();
        SettingsManager::installDefaults();

        update_option('clubcore_db_version', MigrationManager::SCHEMA_VERSION);
        update_option('clubcore_plugin_version', defined('CLUBCORE_VERSION') ? CLUBCORE_VERSION : '1.0.0');

        flush_rewrite_rules();
    }
}
