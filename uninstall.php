<?php
/**
 * Fired when the plugin is uninstalled.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

if (file_exists(plugin_dir_path(__FILE__) . 'vendor/autoload.php')) {
    require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
}

$delete_data = get_option('clubcore_delete_data_on_uninstall', false);

if ($delete_data) {
    global $wpdb;
    
    // Drop custom tables
    $tables = [
        "{$wpdb->prefix}clubcore_members",
        "{$wpdb->prefix}clubcore_sms_logs",
        "{$wpdb->prefix}clubcore_audit_logs",
        "{$wpdb->prefix}clubcore_import_jobs"
    ];
    
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS {$table}");
    }
    
    // Delete options
    delete_option('clubcore_db_version');
    delete_option('clubcore_plugin_version');
    delete_option('clubcore_delete_data_on_uninstall');
    
    // Remove capabilities
    if (class_exists('ClubCore\Infrastructure\WordPress\CapabilityManager')) {
        \ClubCore\Infrastructure\WordPress\CapabilityManager::removeCapabilities();
    }
}
