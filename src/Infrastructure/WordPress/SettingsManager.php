<?php

declare(strict_types=1);

namespace ClubCore\Infrastructure\WordPress;

/**
 * Manages WordPress Settings API registration, sanitization, and defaults.
 *
 * @package ClubCore\Infrastructure\WordPress
 */
class SettingsManager
{
    public const ALL_GROUPS = [
        'clubcore_general_settings',
        'clubcore_access_settings',
        'clubcore_sms_settings',
        'clubcore_sms_options',
        'clubcore_pattern_settings',
        'clubcore_pattern_options',
        'clubcore_wc_settings',
        'clubcore_appearance_settings',
        'clubcore_import_export_settings',
        'clubcore_privacy_settings',
        'clubcore_advanced_settings',
    ];

    public function init(): void
    {
        add_action('admin_init', [$this, 'registerSettings']);
        add_filter('allowed_options', [$this, 'filterAllowedOptions']);

        // Ensure Administrator (manage_options) is unconditionally authorized to save all settings groups
        foreach (self::ALL_GROUPS as $group) {
            add_filter("option_page_capability_{$group}", static fn() => 'manage_options');
        }
    }

    public static function installDefaults(): void
    {
        $defaults = [
            'clubcore_admin_menu_title' => 'باشگاه مشتریان',
            'clubcore_admin_page_slug' => 'customer-club',
            'clubcore_admin_menu_icon' => 'dashicons-groups',
            'clubcore_admin_menu_position' => 30,
            'clubcore_terminal_title' => get_bloginfo('name') ?: 'باشگاه مشتریان',
            'clubcore_terminal_slug' => 'club-terminal',
            'clubcore_terminal_pin' => '1234',
            'clubcore_terminal_require_pin' => '0',
            'clubcore_terminal_allowed_roles' => ['administrator', 'shop_manager'],
            'clubcore_terminal_guest_mode' => 'login_required',
            'clubcore_sms_provider' => 'melipayamak',
            'clubcore_sms_auth_mode' => 'classic',
            'clubcore_sms_username' => '',
            'clubcore_sms_password' => '',
            'clubcore_sms_api_token' => '',
            'clubcore_sms_timeout' => 30,
            'clubcore_pattern_body_id' => '',
            'clubcore_pattern_template' => '',
            'clubcore_wc_auto_enroll' => 'disabled',
            'clubcore_wc_send_sms' => '0',
            'clubcore_wc_guest_matching' => '0',
            'clubcore_appearance_primary' => '#2271b1',
            'clubcore_appearance_secondary' => '#135e96',
            'clubcore_appearance_card' => '#ffffff',
            'clubcore_appearance_text' => '#1d2327',
            'clubcore_appearance_button' => '#2271b1',
            'clubcore_appearance_radius' => '6px',
            'clubcore_sms_log_retention_days' => 90,
            'clubcore_audit_log_retention_days' => 180,
            'clubcore_anonymize_on_user_delete' => '1',
            'clubcore_import_chunk_size' => 500,
            'clubcore_import_default_mode' => 'skip_duplicates',
            'clubcore_delete_data_on_uninstall' => '0',
            'clubcore_debug_logging' => '0',
        ];

        foreach ($defaults as $opt => $val) {
            if (get_option($opt) === false) {
                add_option($opt, $val);
            }
        }
    }

    public function registerSettings(): void
    {
        // 1. General & Terminal
        register_setting('clubcore_general_settings', 'clubcore_admin_menu_title', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clubcore_general_settings', 'clubcore_admin_page_slug', ['sanitize_callback' => 'sanitize_title']);
        register_setting('clubcore_general_settings', 'clubcore_admin_menu_icon', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clubcore_general_settings', 'clubcore_admin_menu_position', ['sanitize_callback' => 'absint']);
        register_setting('clubcore_general_settings', 'clubcore_terminal_title', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clubcore_general_settings', 'clubcore_terminal_slug', ['sanitize_callback' => 'sanitize_title']);
        register_setting('clubcore_general_settings', 'clubcore_terminal_pin', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clubcore_general_settings', 'clubcore_terminal_require_pin', ['sanitize_callback' => 'sanitize_text_field']);

        // 2. Access Management & Terminal Roles
        register_setting('clubcore_access_settings', 'clubcore_role_caps', ['sanitize_callback' => [$this, 'saveRoleCapabilities']]);
        register_setting('clubcore_access_settings', 'clubcore_terminal_allowed_roles', ['sanitize_callback' => [$this, 'sanitizeTerminalRoles']]);
        register_setting('clubcore_access_settings', 'clubcore_terminal_guest_mode', ['sanitize_callback' => 'sanitize_text_field']);

        // 3. SMS (registered for both clubcore_sms_settings and alias clubcore_sms_options)
        $smsFields = [
            'clubcore_sms_provider' => 'sanitize_text_field',
            'clubcore_sms_auth_mode' => 'sanitize_text_field',
            'clubcore_sms_username' => 'sanitize_text_field',
            'clubcore_sms_password' => [$this, 'sanitizePassword'],
            'clubcore_sms_api_token' => [$this, 'sanitizePassword'],
            'clubcore_sms_timeout' => 'absint',
        ];
        foreach (['clubcore_sms_settings', 'clubcore_sms_options'] as $smsGroup) {
            foreach ($smsFields as $opt => $cb) {
                register_setting($smsGroup, $opt, ['sanitize_callback' => $cb]);
            }
        }

        // 4. Pattern (registered for both clubcore_pattern_settings and alias clubcore_pattern_options)
        foreach (['clubcore_pattern_settings', 'clubcore_pattern_options'] as $patternGroup) {
            register_setting($patternGroup, 'clubcore_pattern_body_id', ['sanitize_callback' => 'sanitize_text_field']);
            register_setting($patternGroup, 'clubcore_pattern_template', ['sanitize_callback' => 'sanitize_textarea_field']);
        }

        // 5. WooCommerce
        register_setting('clubcore_wc_settings', 'clubcore_wc_auto_enroll', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clubcore_wc_settings', 'clubcore_wc_send_sms', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clubcore_wc_settings', 'clubcore_wc_guest_matching', ['sanitize_callback' => 'sanitize_text_field']);

        // 6. Appearance
        register_setting('clubcore_appearance_settings', 'clubcore_appearance_primary', ['sanitize_callback' => 'sanitize_hex_color']);
        register_setting('clubcore_appearance_settings', 'clubcore_appearance_secondary', ['sanitize_callback' => 'sanitize_hex_color']);
        register_setting('clubcore_appearance_settings', 'clubcore_appearance_card', ['sanitize_callback' => 'sanitize_hex_color']);
        register_setting('clubcore_appearance_settings', 'clubcore_appearance_text', ['sanitize_callback' => 'sanitize_hex_color']);
        register_setting('clubcore_appearance_settings', 'clubcore_appearance_button', ['sanitize_callback' => 'sanitize_hex_color']);
        register_setting('clubcore_appearance_settings', 'clubcore_appearance_radius', ['sanitize_callback' => 'sanitize_text_field']);

        // 7. Privacy
        register_setting('clubcore_privacy_settings', 'clubcore_sms_log_retention_days', ['sanitize_callback' => 'absint']);
        register_setting('clubcore_privacy_settings', 'clubcore_audit_log_retention_days', ['sanitize_callback' => 'absint']);
        register_setting('clubcore_privacy_settings', 'clubcore_anonymize_on_user_delete', ['sanitize_callback' => 'sanitize_text_field']);

        // 8. Import / Export
        register_setting('clubcore_import_export_settings', 'clubcore_import_chunk_size', ['sanitize_callback' => 'absint']);
        register_setting('clubcore_import_export_settings', 'clubcore_import_default_mode', ['sanitize_callback' => 'sanitize_text_field']);

        // 9. Advanced
        register_setting('clubcore_advanced_settings', 'clubcore_delete_data_on_uninstall', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clubcore_advanced_settings', 'clubcore_debug_logging', ['sanitize_callback' => 'sanitize_text_field']);
    }

    /**
     * Whitelist all plugin option pages and their options for options.php processing.
     * Prevents WordPress "Sorry, you are not allowed to edit this page" errors.
     *
     * @param array<string, array<int, string>> $allowed
     * @return array<string, array<int, string>>
     */
    public function filterAllowedOptions(array $allowed): array
    {
        $map = [
            'clubcore_general_settings' => [
                'clubcore_admin_menu_title',
                'clubcore_admin_page_slug',
                'clubcore_admin_menu_icon',
                'clubcore_admin_menu_position',
                'clubcore_terminal_title',
                'clubcore_terminal_slug',
                'clubcore_terminal_pin',
                'clubcore_terminal_require_pin',
            ],
            'clubcore_access_settings' => [
                'clubcore_role_caps',
                'clubcore_terminal_allowed_roles',
                'clubcore_terminal_guest_mode',
            ],
            'clubcore_sms_settings' => [
                'clubcore_sms_provider',
                'clubcore_sms_auth_mode',
                'clubcore_sms_username',
                'clubcore_sms_password',
                'clubcore_sms_api_token',
                'clubcore_sms_timeout',
            ],
            'clubcore_pattern_settings' => [
                'clubcore_pattern_body_id',
                'clubcore_pattern_template',
            ],
            'clubcore_wc_settings' => [
                'clubcore_wc_auto_enroll',
                'clubcore_wc_send_sms',
                'clubcore_wc_guest_matching',
            ],
            'clubcore_appearance_settings' => [
                'clubcore_appearance_primary',
                'clubcore_appearance_secondary',
                'clubcore_appearance_card',
                'clubcore_appearance_text',
                'clubcore_appearance_button',
                'clubcore_appearance_radius',
            ],
            'clubcore_import_export_settings' => [
                'clubcore_import_chunk_size',
                'clubcore_import_default_mode',
            ],
            'clubcore_privacy_settings' => [
                'clubcore_sms_log_retention_days',
                'clubcore_audit_log_retention_days',
                'clubcore_anonymize_on_user_delete',
            ],
            'clubcore_advanced_settings' => [
                'clubcore_delete_data_on_uninstall',
                'clubcore_debug_logging',
            ],
        ];

        // Register aliases
        $map['clubcore_sms_options'] = $map['clubcore_sms_settings'];
        $map['clubcore_pattern_options'] = $map['clubcore_pattern_settings'];

        foreach ($map as $group => $options) {
            if (!isset($allowed[$group])) {
                $allowed[$group] = [];
            }
            $allowed[$group] = array_values(array_unique(array_merge($allowed[$group], $options)));
        }

        return $allowed;
    }

    /**
     * Save role capabilities across WordPress roles when access form is submitted.
     *
     * @param mixed $input
     * @return array
     */
    public function saveRoleCapabilities(mixed $input): array
    {
        if (!is_array($input)) {
            $input = [];
        }

        global $wp_roles;
        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }

        foreach ($wp_roles->roles as $roleKey => $roleInfo) {
            if ($roleKey === 'administrator') {
                continue; // Administrator always has all capabilities
            }

            $role = get_role($roleKey);
            if (!$role) {
                continue;
            }

            foreach (CapabilityManager::CAPABILITIES as $cap) {
                $enabled = !empty($input[$roleKey][$cap]);
                if ($enabled) {
                    $role->add_cap($cap);
                } else {
                    $role->remove_cap($cap);
                }
            }
        }

        return $input;
    }

    /**
     * Sanitize and enforce terminal allowed roles list.
     *
     * @param mixed $input
     * @return array<int, string>
     */
    public function sanitizeTerminalRoles(mixed $input): array
    {
        if (!is_array($input)) {
            $input = ['administrator'];
        }

        $clean = array_values(array_unique(array_map('sanitize_key', $input)));
        if (!in_array('administrator', $clean, true)) {
            $clean[] = 'administrator';
        }

        return $clean;
    }

    public function sanitizePassword(string $newVal): string
    {
        // Don't overwrite if left blank on edit
        if (trim($newVal) === '' || trim($newVal) === '********') {
            return (string) get_option('clubcore_sms_password', '');
        }
        return sanitize_text_field($newVal);
    }
}
