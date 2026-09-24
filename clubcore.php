<?php
/**
 * Plugin Name: ClubCore - Customer Club
 * Description: Professional WordPress Customer Club Platform | باشگاه مشتریان پیشرفته وردپرس
 * Version: 1.0.1
 * Author: Mohamad Hosein Sabour
 * Text Domain: clubcore
 * Domain Path: /languages
 * Requires PHP: 8.1
 * Requires at least: 6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CLUBCORE_VERSION', '1.0.1');
define('CLUBCORE_PLUGIN_FILE', __FILE__);
define('CLUBCORE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CLUBCORE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CLUBCORE_PLUGIN_BASENAME', plugin_basename(__FILE__));

// 1. PHP Version Check (Graceful early exit before loading any PHP 8.1 code)
if (version_compare(PHP_VERSION, '8.1', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p><strong>ClubCore:</strong> ' .
            sprintf(
                esc_html__('این افزونه نیازمند PHP نسخه ۸.۱ یا بالاتر است. نسخه فعلی سرور شما: %s می‌باشد. لطفاً از طریق هاست نسخه PHP را به ۸.۱ یا ۸.۲ ارتقا دهید.', 'clubcore'),
                esc_html(PHP_VERSION)
            ) . '</p></div>';
    });
    return;
}

// 2. WordPress Version Check
global $wp_version;
if (isset($wp_version) && version_compare($wp_version, '6.4', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p><strong>ClubCore:</strong> ' .
            esc_html__('این افزونه نیازمند وردپرس نسخه ۶.۴ یا بالاتر است.', 'clubcore') .
            '</p></div>';
    });
    return;
}

// 3. Built-in PSR-4 Autoloader for ClubCore classes
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

// 4. Load Composer Autoloader if present (for optional PhpSpreadsheet)
if (file_exists(CLUBCORE_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once CLUBCORE_PLUGIN_DIR . 'vendor/autoload.php';
}

// 5. Declare WooCommerce HPOS Compatibility
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

// 6. Activation & Deactivation Hooks
register_activation_hook(__FILE__, function () {
    try {
        \ClubCore\Infrastructure\WordPress\Activator::activate();
    } catch (\Throwable $e) {
        update_option('clubcore_activation_error', $e->getMessage());
    }
});

register_deactivation_hook(__FILE__, function () {
    try {
        \ClubCore\Infrastructure\WordPress\Deactivator::deactivate();
    } catch (\Throwable $e) {
        // Silent
    }
});

// 7. Add "Settings" link in WordPress Plugins list
add_filter('plugin_action_links_' . CLUBCORE_PLUGIN_BASENAME, function ($links) {
    $slug = get_option('clubcore_admin_page_slug', 'customer-club');
    $settingsUrl = admin_url('admin.php?page=' . $slug . '-settings');
    $settingsLink = sprintf(
        '<a href="%s" style="font-weight:600; color:#2271b1;">%s</a>',
        esc_url($settingsUrl),
        esc_html__('تنظیمات', 'clubcore')
    );
    array_unshift($links, $settingsLink);
    return $links;
});

// 8. Self-healing capabilities on admin_init (User is guaranteed to be authenticated)
add_action('admin_init', function () {
    if (class_exists(\ClubCore\Infrastructure\WordPress\CapabilityManager::class)) {
        \ClubCore\Infrastructure\WordPress\CapabilityManager::ensureAdminCapabilities();
    }
});

// 9. Safe Bootstrap: Protects entire site from crashing (Zero Downtime / Error Trap)
add_action('plugins_loaded', function () {
    try {
        \ClubCore\Plugin::init();
    } catch (\Throwable $e) {
        add_action('admin_notices', function () use ($e) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>خطای افزونه باشگاه مشتریان (ClubCore):</strong> ' . esc_html($e->getMessage()) . '</p>';
            echo '<p><small>در فایل: <code>' . esc_html($e->getFile()) . '</code> در خط <strong>' . (int)$e->getLine() . '</strong></small></p>';
            echo '</div>';
        });

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf('ClubCore Error: %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));
        }
    }
});
