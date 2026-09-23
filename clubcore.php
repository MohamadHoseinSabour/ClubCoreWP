<?php
/**
 * Plugin Name: ClubCore - Customer Club
 * Description: Professional WordPress Customer Club Platform | باشگاه مشتریان پیشرفته وردپرس
 * Version: 1.0.0
 * Author: Mohamad Hosein Sabour
 * Text Domain: clubcore
 * Domain Path: /languages
 * Requires PHP: 8.1
 * Requires at least: 6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CLUBCORE_VERSION', '1.0.0');
define('CLUBCORE_PLUGIN_FILE', __FILE__);
define('CLUBCORE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CLUBCORE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CLUBCORE_PLUGIN_BASENAME', plugin_basename(__FILE__));

// PHP Version Check
if (version_compare(PHP_VERSION, '8.1', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>' . esc_html__('ClubCore requires PHP 8.1 or higher.', 'clubcore') . '</p></div>';
    });
    return;
}

// WordPress Version Check
global $wp_version;
if (isset($wp_version) && version_compare($wp_version, '6.4', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>' . esc_html__('ClubCore requires WordPress 6.4 or higher.', 'clubcore') . '</p></div>';
    });
    return;
}

// 1. Built-in PSR-4 Autoloader for ClubCore classes (Works 100% without composer)
spl_autoload_register(function ($class) {
    $prefix = 'ClubCore\\';
    $baseDir = CLUBCORE_PLUGIN_DIR . 'src/';
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// 2. Load Composer Autoloader if available (for third-party libraries like PhpSpreadsheet)
if (file_exists(CLUBCORE_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once CLUBCORE_PLUGIN_DIR . 'vendor/autoload.php';
}

// 3. Declare WooCommerce HPOS Compatibility
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

// 4. Activation & Deactivation Hooks
register_activation_hook(__FILE__, ['ClubCore\Infrastructure\WordPress\Activator', 'activate']);
register_deactivation_hook(__FILE__, ['ClubCore\Infrastructure\WordPress\Deactivator', 'deactivate']);

// 5. Add "Settings" link in WordPress Plugins List
add_filter('plugin_action_links_' . CLUBCORE_PLUGIN_BASENAME, function ($links) {
    $slug = get_option('clubcore_admin_page_slug', 'customer-club');
    $settingsUrl = admin_url('admin.php?page=' . $slug . '-settings');
    $settingsLink = sprintf(
        '<a href="%s" style="font-weight:600;">%s</a>',
        esc_url($settingsUrl),
        esc_html__('تنظیمات', 'clubcore')
    );
    array_unshift($links, $settingsLink);
    return $links;
});

// 6. Bootstrap Plugin
add_action('plugins_loaded', function () {
    \ClubCore\Infrastructure\WordPress\CapabilityManager::ensureAdminCapabilities();
    \ClubCore\Plugin::init();
});
