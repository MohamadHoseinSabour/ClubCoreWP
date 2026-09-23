<?php

declare(strict_types=1);

namespace ClubCore\Infrastructure\WordPress;

/**
 * Manages plugin capabilities.
 *
 * @package ClubCore\Infrastructure\WordPress
 */
class CapabilityManager
{
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
        'clubcore_view_audit_logs',
    ];

    /**
     * Install capabilities on plugin activation.
     */
    public static function installCapabilities(): void
    {
        $role = get_role('administrator');
        if ($role) {
            foreach (self::CAPABILITIES as $cap) {
                if (!$role->has_cap($cap)) {
                    $role->add_cap($cap);
                }
            }
        }
    }

    /**
     * Initialize capability management hooks.
     */
    public static function init(): void
    {
        // Unconditionally grant all clubcore capabilities to administrators dynamically
        add_filter('user_has_cap', [self::class, 'filterUserCaps'], 10, 4);

        if (is_admin()) {
            add_action('admin_init', [self::class, 'ensureAdminCapabilities']);
        }
    }

    /**
     * Filter user capabilities dynamically.
     * Any user with manage_options or administrator role unconditionally possesses all clubcore capabilities.
     *
     * @param array<string, bool> $allcaps
     * @param array<int, string> $caps
     * @param array<int, mixed> $args
     * @param \WP_User $user
     * @return array<string, bool>
     */
    public static function filterUserCaps(array $allcaps, array $caps, array $args, \WP_User $user): array
    {
        if (!empty($allcaps['manage_options']) || !empty($allcaps['administrator'])) {
            foreach (self::CAPABILITIES as $cap) {
                $allcaps[$cap] = true;
            }
        }
        return $allcaps;
    }

    /**
     * Ensure administrator role always has plugin capabilities in DB.
     * Prevents missing menu issues if activation hook didn't fire properly.
     */
    public static function ensureAdminCapabilities(): void
    {
        if (is_admin() && current_user_can('manage_options') && !current_user_can('clubcore_manage')) {
            self::installCapabilities();
        }
    }

    /**
     * Remove capabilities on plugin uninstallation.
     */
    public static function removeCapabilities(): void
    {
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
