<?php
namespace ClubCore\Infrastructure\WordPress;

/**
 * Manages plugin capabilities.
 */
class CapabilityManager {
    public const CAPABILITIES = [
        'clubcore_manage',
        'clubcore_manage_settings',
        'clubcore_view_members',
        'clubcore_add_members',
        'clubcore_edit_members',
        'clubcore_delete_members',
        'clubcore_import_members',
        'clubcore_export_members',
        'clubcore_send_sms',
        'clubcore_view_sms_logs',
        'clubcore_view_audit_logs'
    ];

    /**
     * Install capabilities on plugin activation.
     */
    public static function installCapabilities(): void {
        $role = get_role('administrator');
        if ($role) {
            foreach (self::CAPABILITIES as $cap) {
                $role->add_cap($cap);
            }
        }
    }

    /**
     * Remove capabilities on plugin uninstallation.
     */
    public static function removeCapabilities(): void {
        global $wp_roles;
        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }
        foreach ($wp_roles->roles as $role_name => $role_info) {
            $role = get_role($role_name);
            if ($role) {
                foreach (self::CAPABILITIES as $cap) {
                    $role->remove_cap($cap);
                }
            }
        }
    }
}
