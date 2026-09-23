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

        // Main menu page.
        add_menu_page(
            __('باشگاه مشتریان', 'clubcore'),
            $menuTitle,
            'clubcore_add_members',
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
            'clubcore_add_members',
            $menuSlug,
            [$this, 'renderAddCustomerPage'],
        );

        // Members.
        add_submenu_page(
            $menuSlug,
            __('اعضا', 'clubcore'),
            __('اعضا', 'clubcore'),
            'clubcore_view_members',
            $menuSlug . '-members',
            [$this, 'renderMembersPage'],
        );

        // SMS History.
        add_submenu_page(
            $menuSlug,
            __('تاریخچه پیامک', 'clubcore'),
            __('تاریخچه پیامک', 'clubcore'),
            'clubcore_view_sms_logs',
            $menuSlug . '-sms-history',
            [$this, 'renderSmsHistoryPage'],
        );

        // Import / Export.
        add_submenu_page(
            $menuSlug,
            __('وارد / خروجی', 'clubcore'),
            __('وارد / خروجی', 'clubcore'),
            'clubcore_import_members',
            $menuSlug . '-import-export',
            [$this, 'renderImportExportPage'],
        );

        // Audit Log.
        add_submenu_page(
            $menuSlug,
            __('گزارش عملیات', 'clubcore'),
            __('گزارش عملیات', 'clubcore'),
            'clubcore_view_audit_logs',
            $menuSlug . '-audit-log',
            [$this, 'renderAuditLogPage'],
        );

        // Settings.
        add_submenu_page(
            $menuSlug,
            __('تنظیمات', 'clubcore'),
            __('تنظیمات', 'clubcore'),
            'clubcore_manage_settings',
            $menuSlug . '-settings',
            [$this, 'renderSettingsPage'],
        );
    }

    /**
     * Render: Add Customer page.
     */
    public function renderAddCustomerPage(): void
    {
        if (!current_user_can('clubcore_add_members')) {
            wp_die(esc_html__('شما دسترسی مشاهده این صفحه را ندارید.', 'clubcore'));
        }
        include CLUBCORE_PLUGIN_DIR . 'templates/admin/add-customer.php';
    }

    /**
     * Render: Members list page.
     */
    public function renderMembersPage(): void
    {
        if (!current_user_can('clubcore_view_members')) {
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
        if (!current_user_can('clubcore_view_sms_logs')) {
            wp_die(esc_html__('شما دسترسی مشاهده این صفحه را ندارید.', 'clubcore'));
        }
        include CLUBCORE_PLUGIN_DIR . 'templates/admin/sms-history.php';
    }

    /**
     * Render: Import/Export page.
     */
    public function renderImportExportPage(): void
    {
        if (!current_user_can('clubcore_import_members')) {
            wp_die(esc_html__('شما دسترسی مشاهده این صفحه را ندارید.', 'clubcore'));
        }
        include CLUBCORE_PLUGIN_DIR . 'templates/admin/import-export.php';
    }

    /**
     * Render: Audit Log page.
     */
    public function renderAuditLogPage(): void
    {
        if (!current_user_can('clubcore_view_audit_logs')) {
            wp_die(esc_html__('شما دسترسی مشاهده این صفحه را ندارید.', 'clubcore'));
        }
        include CLUBCORE_PLUGIN_DIR . 'templates/admin/audit-log.php';
    }

    /**
     * Render: Settings page.
     */
    public function renderSettingsPage(): void
    {
        if (!current_user_can('clubcore_manage_settings')) {
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
