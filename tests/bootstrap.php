<?php

declare(strict_types=1);

// Composer Autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    // Basic PSR-4 autoloader for tests when vendor isn't installed
    spl_autoload_register(function ($class) {
        $prefix = 'ClubCore\\';
        $baseDir = __DIR__ . '/../src/';
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
}

// Minimal WordPress stub functions for Unit Testing without full WP core
if (!function_exists('__')) {
    function __(string $text, string $domain = 'default'): string {
        return $text;
    }
}
if (!function_exists('esc_html__')) {
    function esc_html__(string $text, string $domain = 'default'): string {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('esc_html')) {
    function esc_html(string $text): string {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('current_time')) {
    function current_time(string $type, bool $gmt = false): string {
        return gmdate('Y-m-d H:i:s');
    }
}
if (!function_exists('get_current_user_id')) {
    function get_current_user_id(): int {
        return 1;
    }
}
if (!function_exists('is_email')) {
    function is_email(string $email): bool {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
if (!function_exists('apply_filters')) {
    function apply_filters(string $tag, $value, ...$args) {
        return $value;
    }
}
if (!function_exists('do_action')) {
    function do_action(string $tag, ...$args): void {}
}
if (!function_exists('wp_salt')) {
    function wp_salt(string $scheme = 'auth'): string {
        return 'test-salt-secret-key-123456';
    }
}
