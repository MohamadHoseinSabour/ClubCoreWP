<?php

declare(strict_types=1);

namespace ClubCore\Presentation\Admin;

/**
 * Admin Menu Manager.
 *
 * Registers the Customer Club admin menu and submenu pages.
 * Uses capabilities for access control, not role names.
 *
 * @package ClubCore\Presentation\Admin
 */
class MenuManager
{
    /**
     * Register hooks.
     */
    public function init(): void
    {
        add_action('admin_menu', [$this, 'registerMenus']);
    }

    /**
     * Register admin menu and submenu pages.
     */
    public function registerMenus(): void
    {
        $menuTitle = get_option('clubcore_admin_menu_title', __('باشگاه مشتریان', 'clubcore'));
        $menuSlug = get_option('clubcore_admin_page_slug', 'customer-club');
        $menuIcon = get_option('clubcore_admin_menu_icon', 'dashicons-groups');
        $menuPosition = (int) get_option('clubcore_admin_menu_position', 30);

        $mainCap = current_user_can('clubcore_add_members') ? 'clubcore_add_members' : 'manage_options';
        $membersCap = current_user_can('clubcore_view_members') ? 'clubcore_view_members' : 'manage_options';
        $smsCap = current_user_can('clubcore_view_sms_logs') ? 'clubcore_view_sms_logs' : 'manage_options';
        $importCap = current_user_can('clubcore_import_members') ? 'clubcore_import_members' : 'manage_options';
        $auditCap = current_user_can('clubcore_view_audit_logs') ? 'clubcore_view_audit_logs' : 'manage_options';
        $settingsCap = current_user_can('clubcore_manage_settings') ? 'clubcore_manage_settings' : 'manage_options';

        // Main menu page.
        add_menu_page(
            __('باشگاه مشتریان', 'clubcore'),
            $menuTitle,
            $mainCap,
            $menuSlug,
            [$this, 'renderAddCustomerPage'],
            $menuIcon,
            $menuPosition,
        );

        // Add Customer (replaces default submenu duplicate).
        add_submenu_page(
            $menuSlug,
            __('ثبت مشتری', 'clubcore'),
            __('ثبت مشتری', 'clubcore'),
            $mainCap,
            $menuSlug,
            [$this, 'renderAddCustomerPage'],
        );

        // Members.
        add_submenu_page(
            $menuSlug,
            __('اعضا', 'clubcore'),
            __('اعضا', 'clubcore'),
            $membersCap,
            $menuSlug . '-members',
            [$this, 'renderMembersPage'],
        );

        // SMS History.
        add_submenu_page(
            $menuSlug,
            __('تاریخچه پیامک', 'clubcore'),
            __('تاریخچه پیامک', 'clubcore'),
            $smsCap,
            $menuSlug . '-sms-history',
            [$this, 'renderSmsHistoryPage'],
        );

        // Import / Export.
        add_submenu_page(
            $menuSlug,
            __('وارد / خروجی', 'clubcore'),
            __('وارد / خروجی', 'clubcore'),
            $importCap,
            $menuSlug . '-import-export',
            [$this, 'renderImportExportPage'],
        );

        // Audit Log.
        add_submenu_page(
            $menuSlug,
            __('گزارش عملیات', 'clubcore'),
            __('گزارش عملیات', 'clubcore'),
            $auditCap,
            $menuSlug . '-audit-log',
            [$this, 'renderAuditLogPage'],
        );

        // Settings.
        add_submenu_page(
            $menuSlug,
            __('تنظیمات', 'clubcore'),
            __('تنظیمات', 'clubcore'),
            $settingsCap,
            $menuSlug . '-settings',
            [$this, 'renderSettingsPage'],
        );
    }

    /**
     * Render: Add Customer page.
     */
    public function renderAddCustomerPage(): void
    {
        if (!current_user_can('clubcore_add_members') && !current_user_can('manage_options')) {
            wp_die(esc_html__('شما دسترسی مشاهده این صفحه را ندارید.', 'clubcore'));
        }
        include CLUBCORE_PLUGIN_DIR . 'templates/admin/add-customer.php';
    }

    /**
     * Render: Members list page.
     */
    public function renderMembersPage(): void
    {
        if (!current_user_can('clubcore_view_members') && !current_user_can('manage_options')) {
            wp_die(esc_html__('شما دسترسی مشاهده این صفحه را ندارید.', 'clubcore'));
        }

        // Check for member detail view.
        $memberId = isset($_GET['member_id']) ? absint($_GET['member_id']) : 0;
        if ($memberId > 0 && isset($_GET['action']) && $_GET['action'] === 'view') {
            // Verify nonce for member detail views.
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'clubcore_view_member_' . $memberId)) {
                wp_die(esc_html__('لینک نامعتبر است.', 'clubcore'));
            }
            include CLUBCORE_PLUGIN_DIR . 'templates/admin/member-detail.php';
            return;
        }

        include CLUBCORE_PLUGIN_DIR . 'templates/admin/members-list.php';
    }

    /**
     * Render: SMS History page.
     */
    public function renderSmsHistoryPage(): void
    {
        if (!current_user_can('clubcore_view_sms_logs') && !current_user_can('manage_options')) {
            wp_die(esc_html__('شما دسترسی مشاهده این صفحه را ندارید.', 'clubcore'));
        }
        include CLUBCORE_PLUGIN_DIR . 'templates/admin/sms-history.php';
    }

    /**
     * Render: Import/Export page.
     */
    public function renderImportExportPage(): void
    {
        if (!current_user_can('clubcore_import_members') && !current_user_can('manage_options')) {
            wp_die(esc_html__('شما دسترسی مشاهده این صفحه را ندارید.', 'clubcore'));
        }
        include CLUBCORE_PLUGIN_DIR . 'templates/admin/import-export.php';
    }

    /**
     * Render: Audit Log page.
     */
    public function renderAuditLogPage(): void
    {
        if (!current_user_can('clubcore_view_audit_logs') && !current_user_can('manage_options')) {
            wp_die(esc_html__('شما دسترسی مشاهده این صفحه را ندارید.', 'clubcore'));
        }
        include CLUBCORE_PLUGIN_DIR . 'templates/admin/audit-log.php';
    }

    /**
     * Render: Settings page.
     */
    public function renderSettingsPage(): void
    {
        if (!current_user_can('clubcore_manage_settings') && !current_user_can('manage_options')) {
            wp_die(esc_html__('شما دسترسی مشاهده این صفحه را ندارید.', 'clubcore'));
        }
        include CLUBCORE_PLUGIN_DIR . 'templates/admin/settings.php';
    }

    /**
     * Get the plugin's admin page slug.
     *
     * @return string
     */
    public static function getMenuSlug(): string
    {
        return get_option('clubcore_admin_page_slug', 'customer-club');
    }
}
