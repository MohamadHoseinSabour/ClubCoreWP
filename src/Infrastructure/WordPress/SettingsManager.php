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
    public function init(): void
    {
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public static function installDefaults(): void
    {
        $defaults = [
            'clubcore_admin_menu_title' => 'باشگاه مشتریان',
            'clubcore_admin_page_slug' => 'customer-club',
            'clubcore_admin_menu_icon' => 'dashicons-groups',
            'clubcore_admin_menu_position' => 30,
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
        // General & Terminal
        register_setting('clubcore_general_settings', 'clubcore_admin_menu_title', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clubcore_general_settings', 'clubcore_admin_page_slug', ['sanitize_callback' => 'sanitize_title']);
        register_setting('clubcore_general_settings', 'clubcore_admin_menu_icon', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clubcore_general_settings', 'clubcore_admin_menu_position', ['sanitize_callback' => 'absint']);
        register_setting('clubcore_general_settings', 'clubcore_terminal_title', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clubcore_general_settings', 'clubcore_terminal_slug', ['sanitize_callback' => 'sanitize_title']);
        register_setting('clubcore_general_settings', 'clubcore_terminal_pin', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clubcore_general_settings', 'clubcore_terminal_require_pin', ['sanitize_callback' => 'sanitize_text_field']);

        // SMS
        register_setting('clubcore_sms_settings', 'clubcore_sms_provider', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clubcore_sms_settings', 'clubcore_sms_auth_mode', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clubcore_sms_settings', 'clubcore_sms_username', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clubcore_sms_settings', 'clubcore_sms_password', ['sanitize_callback' => [$this, 'sanitizePassword']]);
        register_setting('clubcore_sms_settings', 'clubcore_sms_api_token', ['sanitize_callback' => [$this, 'sanitizePassword']]);
        register_setting('clubcore_sms_settings', 'clubcore_sms_timeout', ['sanitize_callback' => 'absint']);

        // Pattern
        register_setting('clubcore_pattern_settings', 'clubcore_pattern_body_id', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clubcore_pattern_settings', 'clubcore_pattern_template', ['sanitize_callback' => 'sanitize_textarea_field']);

        // WooCommerce
        register_setting('clubcore_wc_settings', 'clubcore_wc_auto_enroll', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clubcore_wc_settings', 'clubcore_wc_send_sms', ['sanitize_callback' => 'sanitize_text_field']);

        // Appearance
        register_setting('clubcore_appearance_settings', 'clubcore_appearance_primary', ['sanitize_callback' => 'sanitize_hex_color']);
        register_setting('clubcore_appearance_settings', 'clubcore_appearance_secondary', ['sanitize_callback' => 'sanitize_hex_color']);
        register_setting('clubcore_appearance_settings', 'clubcore_appearance_card', ['sanitize_callback' => 'sanitize_hex_color']);
        register_setting('clubcore_appearance_settings', 'clubcore_appearance_text', ['sanitize_callback' => 'sanitize_hex_color']);
        register_setting('clubcore_appearance_settings', 'clubcore_appearance_button', ['sanitize_callback' => 'sanitize_hex_color']);
        register_setting('clubcore_appearance_settings', 'clubcore_appearance_radius', ['sanitize_callback' => 'sanitize_text_field']);

        // Privacy
        register_setting('clubcore_privacy_settings', 'clubcore_sms_log_retention_days', ['sanitize_callback' => 'absint']);
        register_setting('clubcore_privacy_settings', 'clubcore_audit_log_retention_days', ['sanitize_callback' => 'absint']);
        register_setting('clubcore_privacy_settings', 'clubcore_anonymize_on_user_delete', ['sanitize_callback' => 'sanitize_text_field']);

        // Import / Export
        register_setting('clubcore_import_export_settings', 'clubcore_import_chunk_size', ['sanitize_callback' => 'absint']);
        register_setting('clubcore_import_export_settings', 'clubcore_import_default_mode', ['sanitize_callback' => 'sanitize_text_field']);

        // Advanced
        register_setting('clubcore_advanced_settings', 'clubcore_delete_data_on_uninstall', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clubcore_advanced_settings', 'clubcore_debug_logging', ['sanitize_callback' => 'sanitize_text_field']);
    }

    public function sanitizePassword(string $newVal): string
    {
        // Don't overwrite if left blank on edit
        if (trim($newVal) === '') {
            return (string) get_option('clubcore_sms_password', '');
        }
        return sanitize_text_field($newVal);
    }
}
