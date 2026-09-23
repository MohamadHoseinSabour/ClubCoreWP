<?php
/**
 * Plugin Name: ClubCore - Customer Club
 * Description: Professional WordPress Customer Club Plugin
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

if (version_compare(PHP_VERSION, '8.1', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>' . esc_html__('ClubCore requires PHP 8.1 or higher.', 'clubcore') . '</p></div>';
    });
    return;
}

global $wp_version;
if (version_compare($wp_version, '6.4', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>' . esc_html__('ClubCore requires WordPress 6.4 or higher.', 'clubcore') . '</p></div>';
    });
    return;
}

if (file_exists(CLUBCORE_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once CLUBCORE_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>' . esc_html__('ClubCore dependencies are missing. Please run "composer install".', 'clubcore') . '</p></div>';
    });
    return;
}

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

register_activation_hook(__FILE__, ['ClubCore\Infrastructure\WordPress\Activator', 'activate']);
register_deactivation_hook(__FILE__, ['ClubCore\Infrastructure\WordPress\Deactivator', 'deactivate']);

add_action('plugins_loaded', ['ClubCore\Plugin', 'init']);
