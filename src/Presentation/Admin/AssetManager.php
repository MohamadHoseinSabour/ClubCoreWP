<?php

declare(strict_types=1);

namespace ClubCore\Presentation\Admin;

/**
 * Asset Manager.
 *
 * Handles CSS and JS enqueuing for admin pages.
 * Assets are ONLY loaded on ClubCore admin pages (not all wp-admin pages).
 *
 * @package ClubCore\Presentation\Admin
 */
class AssetManager
{
    /**
     * Register hooks.
     */
    public function init(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Enqueue admin assets only on plugin pages.
     *
     * @param string $hookSuffix The current admin page hook suffix.
     */
    public function enqueueAssets(string $hookSuffix): void
    {
        if (!$this->isPluginPage($hookSuffix)) {
            return;
        }

        $version = defined('CLUBCORE_VERSION') ? CLUBCORE_VERSION : '1.0.0';

        // Main admin CSS.
        wp_enqueue_style(
            'clubcore-admin',
            CLUBCORE_PLUGIN_URL . 'assets/css/admin.css',
            [],
            $version,
        );

        // RTL styles.
        wp_style_add_data('clubcore-admin', 'rtl', 'replace');

        // CSS Variables for appearance customization.
        $this->injectCssVariables();

        // Main admin JS.
        wp_enqueue_script(
            'clubcore-admin',
            CLUBCORE_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            $version,
            true,
        );

        // Localize common AJAX data.
        wp_localize_script('clubcore-admin', 'clubcoreAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonces' => [
                'createMember' => wp_create_nonce('clubcore_create_member'),
                'testSms' => wp_create_nonce('clubcore_test_sms'),
                'resendSms' => wp_create_nonce('clubcore_resend_sms'),
                'import' => wp_create_nonce('clubcore_import'),
                'saveSettings' => wp_create_nonce('clubcore_save_settings'),
            ],
            'i18n' => [
                'loading' => __('در حال بارگذاری...', 'clubcore'),
                'success' => __('عملیات با موفقیت انجام شد.', 'clubcore'),
                'error' => __('خطایی رخ داد.', 'clubcore'),
                'confirm' => __('آیا مطمئن هستید؟', 'clubcore'),
                'confirmDelete' => __('آیا از حذف این مورد اطمینان دارید؟ این عملیات قابل بازگشت نیست.', 'clubcore'),
                'confirmBulkSms' => __('توجه: این عملیات ممکن است تعداد زیادی پیامک ارسال کند. آیا ادامه می‌دهید؟', 'clubcore'),
                'required' => __('این فیلد الزامی است.', 'clubcore'),
                'invalidPhone' => __('شماره موبایل نامعتبر است.', 'clubcore'),
            ],
        ]);

        // Page-specific assets.
        $this->enqueuePageSpecificAssets($hookSuffix, $version);
    }

    /**
     * Check if the current admin page belongs to ClubCore.
     *
     * @param string $hookSuffix Admin page hook suffix.
     *
     * @return bool
     */
    private function isPluginPage(string $hookSuffix): bool
    {
        $menuSlug = MenuManager::getMenuSlug();

        $pluginPages = [
            'toplevel_page_' . $menuSlug,
            $menuSlug . '_page_' . $menuSlug . '-members',
            $menuSlug . '_page_' . $menuSlug . '-sms-history',
            $menuSlug . '_page_' . $menuSlug . '-import-export',
            $menuSlug . '_page_' . $menuSlug . '-audit-log',
            $menuSlug . '_page_' . $menuSlug . '-settings',
        ];

        // Also check with sanitized menu title for submenu pages.
        foreach ($pluginPages as $page) {
            if (str_contains($hookSuffix, $menuSlug)) {
                return true;
            }
        }

        return in_array($hookSuffix, $pluginPages, true);
    }

    /**
     * Inject CSS variables for appearance customization.
     */
    private function injectCssVariables(): void
    {
        $defaults = [
            'primary' => '#2271b1',
            'secondary' => '#135e96',
            'background' => '#f0f0f1',
            'card' => '#ffffff',
            'text' => '#1d2327',
            'button' => '#2271b1',
            'success' => '#00a32a',
            'warning' => '#dba617',
            'error' => '#d63638',
            'radius' => '6px',
        ];

        $vars = [];
        foreach ($defaults as $key => $default) {
            $value = get_option('clubcore_appearance_' . $key, $default);
            $vars[] = '--cc-' . $key . ': ' . sanitize_hex_color($value ?: $default) . ';';
        }
        // Radius is not a color.
        $radius = get_option('clubcore_appearance_radius', '6px');
        $vars[] = '--cc-radius: ' . esc_attr($radius) . ';';

        $css = ':root { ' . implode(' ', $vars) . ' }';
        wp_add_inline_style('clubcore-admin', $css);
    }

    /**
     * Enqueue page-specific assets.
     *
     * @param string $hookSuffix Admin page hook suffix.
     * @param string $version    Plugin version.
     */
    private function enqueuePageSpecificAssets(string $hookSuffix, string $version): void
    {
        $menuSlug = MenuManager::getMenuSlug();

        // Add Customer page.
        if (str_contains($hookSuffix, 'toplevel_page_' . $menuSlug) || $hookSuffix === 'toplevel_page_' . $menuSlug) {
            wp_enqueue_script(
                'clubcore-add-customer',
                CLUBCORE_PLUGIN_URL . 'assets/js/add-customer.js',
                ['jquery', 'clubcore-admin'],
                $version,
                true,
            );
        }

        // Import/Export page.
        if (str_contains($hookSuffix, 'import-export')) {
            wp_enqueue_script(
                'clubcore-import',
                CLUBCORE_PLUGIN_URL . 'assets/js/import.js',
                ['jquery', 'clubcore-admin'],
                $version,
                true,
            );
        }

        // Settings page.
        if (str_contains($hookSuffix, 'settings')) {
            wp_enqueue_script(
                'clubcore-settings',
                CLUBCORE_PLUGIN_URL . 'assets/js/settings.js',
                ['jquery', 'clubcore-admin', 'wp-color-picker'],
                $version,
                true,
            );
            wp_enqueue_style('wp-color-picker');
        }
    }
}
