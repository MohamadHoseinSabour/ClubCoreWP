<?php
namespace ClubCore\Infrastructure\WordPress;

/**
 * Handles plugin deactivation.
 */
class Deactivator {
    /**
     * Deactivate the plugin.
     */
    public static function deactivate(): void {
        // Does NOT delete data (members, users, logs)
        
        // Only cleans up scheduled tasks if any
        $hook = 'clubcore_scheduled_tasks';
        if (wp_next_scheduled($hook)) {
            wp_clear_scheduled_hook($hook);
        }
        
        flush_rewrite_rules();
    }
}
