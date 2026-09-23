<?php

declare(strict_types=1);

namespace ClubCore\Presentation\Terminal;

use ClubCore\Application\Dto\CreateMemberRequest;
use ClubCore\Application\UseCase\CreateMemberUseCase;
use ClubCore\Application\Service\SmsService;
use ClubCore\Domain\Entity\Member;
use ClubCore\Domain\ValueObject\PhoneNumber;

/**
 * Manages the Customer Club Touchscreen POS / Kiosk Terminal.
 *
 * Provides a dedicated, mobile/tablet optimized web app interface for cashiers
 * and operators to register customers without accessing WordPress wp-admin.
 *
 * @package ClubCore\Presentation\Terminal
 */
class TerminalManager
{
    public const DEFAULT_SLUG = 'club-terminal';
    private const SESSION_COOKIE = 'clubcore_terminal_auth';

    public function __construct(
        private readonly CreateMemberUseCase $createMemberUseCase,
        private readonly ?SmsService $smsService = null
    ) {}

    public function init(): void
    {
        // 1. Rewrite rule & Query var
        add_action('init', [$this, 'registerRewriteRules']);
        add_filter('query_vars', [$this, 'registerQueryVars']);
        add_filter('request', [$this, 'filterRequest']);

        // 2. Automatically flush rewrite rules when slug is changed in settings
        add_action('update_option_clubcore_terminal_slug', [$this, 'onSlugChanged'], 10, 2);

        // 3. Template redirect for standalone Kiosk UI
        add_action('template_redirect', [$this, 'handleTerminalTemplate'], 1);

        // 4. Shortcode [clubcore_terminal]
        add_shortcode('clubcore_terminal', [$this, 'renderShortcode']);

        // 5. AJAX Handlers (both logged-in and authorized terminal sessions)
        add_action('wp_ajax_clubcore_terminal_register', [$this, 'handleRegister']);
        add_action('wp_ajax_nopriv_clubcore_terminal_register', [$this, 'handleRegister']);

        add_action('wp_ajax_clubcore_terminal_verify_pin', [$this, 'handleVerifyPin']);
        add_action('wp_ajax_nopriv_clubcore_terminal_verify_pin', [$this, 'handleVerifyPin']);

        add_action('wp_ajax_clubcore_terminal_stats', [$this, 'handleGetStats']);
        add_action('wp_ajax_nopriv_clubcore_terminal_stats', [$this, 'handleGetStats']);

        // 6. Admin Bar quick launcher
        add_action('admin_bar_menu', [$this, 'addAdminBarMenu'], 100);
    }

    public function registerRewriteRules(): void
    {
        $slug = $this->getTerminalSlug();
        add_rewrite_rule('^' . preg_quote($slug, '/') . '/?$', 'index.php?clubcore_terminal=1', 'top');
    }

    public function registerQueryVars(array $vars): array
    {
        $vars[] = 'clubcore_terminal';
        return $vars;
    }

    public function filterRequest(array $queryVars): array
    {
        if ($this->isTerminalRequest()) {
            $queryVars['clubcore_terminal'] = '1';
        }
        return $queryVars;
    }

    public function onSlugChanged($oldValue, $newValue): void
    {
        if ($oldValue !== $newValue) {
            $this->registerRewriteRules();
            flush_rewrite_rules(false);
        }
    }

    public function isTerminalRequest(): bool
    {
        // Direct query var or GET param
        if (get_query_var('clubcore_terminal') || isset($_GET['clubcore_terminal'])) {
            return true;
        }

        // Direct URI matching (prevents 404 even if permalinks are not flushed yet)
        if (!empty($_SERVER['REQUEST_URI'])) {
            $requestPath = trim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
            $homePath = trim((string) parse_url(home_url(), PHP_URL_PATH), '/');

            if ($homePath !== '' && str_starts_with($requestPath, $homePath)) {
                $requestPath = trim(substr($requestPath, strlen($homePath)), '/');
            }

            $slug = $this->getTerminalSlug();
            if ($requestPath === $slug || $requestPath === $slug . '/') {
                return true;
            }
        }

        return false;
    }

    public function getTerminalSlug(): string
    {
        return sanitize_title((string) get_option('clubcore_terminal_slug', self::DEFAULT_SLUG)) ?: self::DEFAULT_SLUG;
    }

    public function getTerminalUrl(): string
    {
        $slug = $this->getTerminalSlug();
        if (get_option('permalink_structure')) {
            return home_url('/' . $slug . '/');
        }
        return add_query_arg('clubcore_terminal', '1', home_url('/'));
    }

    public function handleTerminalTemplate(): void
    {
        if (!$this->isTerminalRequest()) {
            return;
        }

        // 1. Access Enforcement for non-admins and unauthorized roles
        if (is_user_logged_in()) {
            if (!$this->isAuthorized()) {
                // Logged-in non-admin or unauthorized user (e.g. subscriber, customer)
                $this->renderAccessDeniedPage();
                exit;
            }
        } else {
            // Guest / Not logged in
            $guestMode = (string) get_option('clubcore_terminal_guest_mode', 'login_required');
            $requirePin = (bool) get_option('clubcore_terminal_require_pin', '0');

            // If login is required and PIN bypass mode is disabled, redirect to login
            if ($guestMode === 'login_required' && !$requirePin) {
                wp_safe_redirect(wp_login_url($this->getTerminalUrl()));
                exit;
            }
        }

        // Prevent WordPress 404 header and status
        global $wp_query;
        if ($wp_query instanceof \WP_Query) {
            $wp_query->is_404 = false;
            $wp_query->is_page = false;
            $wp_query->is_single = false;
            $wp_query->is_home = false;
            $wp_query->is_archive = false;
        }

        // Send 200 OK headers
        status_header(200);
        nocache_headers();

        // Render standalone kiosk template
        $templatePath = CLUBCORE_PLUGIN_DIR . 'templates/terminal/kiosk.php';
        if (file_exists($templatePath)) {
            include $templatePath;
            exit;
        }
    }

    public function renderAccessDeniedPage(): void
    {
        status_header(403);
        nocache_headers();

        $user = wp_get_current_user();
        $userRoleNames = [];
        if (!empty($user->roles)) {
            $allRoles = wp_roles()->get_names();
            foreach ($user->roles as $roleKey) {
                $userRoleNames[] = $allRoles[$roleKey] ?? $roleKey;
            }
        }
        $roleDisplay = !empty($userRoleNames) ? implode('، ', $userRoleNames) : __('کاربر عادی', 'clubcore');
        $siteName = get_bloginfo('name') ?: __('باشگاه مشتریان', 'clubcore');

        echo '<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . esc_html__('عدم دسترسی به دستگاه ثبت مشتری', 'clubcore') . '</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <style>
        body {
            font-family: "Vazirmatn", -apple-system, BlinkMacSystemFont, sans-serif;
            background: #090d16;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            direction: rtl;
        }
        .access-card {
            background: #111827;
            border: 1px solid #1f2937;
            border-radius: 20px;
            max-width: 480px;
            width: 100%;
            padding: 36px 28px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        }
        .icon {
            font-size: 54px;
            margin-bottom: 16px;
            line-height: 1;
        }
        h1 {
            font-size: 20px;
            color: #f87171;
            margin: 0 0 14px;
            font-weight: 700;
        }
        p {
            font-size: 14.5px;
            color: #94a3b8;
            line-height: 1.8;
            margin: 0 0 20px;
        }
        .user-badge {
            background: #1e293b;
            border: 1px solid #334155;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            color: #cbd5e1;
            display: inline-block;
            margin-bottom: 24px;
        }
        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
        }
        .btn-primary {
            background: #2563eb;
            color: #fff;
        }
        .btn-primary:hover {
            background: #1d4ed8;
        }
        .btn-secondary {
            background: #1e293b;
            color: #94a3b8;
            border: 1px solid #334155;
        }
        .btn-secondary:hover {
            background: #334155;
            color: #f8fafc;
        }
    </style>
</head>
<body>
    <div class="access-card">
        <div class="icon">🔒</div>
        <h1>' . esc_html__('عدم دسترسی به دستگاه ثبت مشتری', 'clubcore') . '</h1>
        <p>' . esc_html__('صفحه دستگاه لمسی ثبت مشتری صرفاً برای مدیران و نقش‌های مجاز تنظیم شده است و کاربران عادی به آن دسترسی ندارند.', 'clubcore') . '</p>
        <div class="user-badge">
            ' . esc_html__('نقش کاربری فعلی شما:', 'clubcore') . ' <strong>' . esc_html($roleDisplay) . '</strong>
        </div>
        <div class="btn-group">
            <a href="' . esc_url(wp_logout_url($this->getTerminalUrl())) . '" class="btn btn-primary">' . esc_html__('خروج و ورود با حساب مدیر', 'clubcore') . '</a>
            <a href="' . esc_url(home_url('/')) . '" class="btn btn-secondary">' . esc_html__('بازگشت به سایت', 'clubcore') . '</a>
        </div>
    </div>
</body>
</html>';
    }

    public function renderShortcode(): string
    {
        if (!$this->isAuthorized()) {
            return '<div class="clubcore-notice clubcore-notice-error" style="padding:16px; background:#fef2f2; color:#991b1b; border:1px solid #fecaca; border-radius:8px; text-align:center; font-family:sans-serif; margin:20px 0;">' .
                esc_html__('شما مجوز دسترسی به دستگاه ثبت مشتری را ندارید. دسترسی به این بخش مختص مدیران و نقش‌های مجاز است.', 'clubcore') .
                '</div>';
        }

        ob_start();
        $templatePath = CLUBCORE_PLUGIN_DIR . 'templates/terminal/kiosk.php';
        if (file_exists($templatePath)) {
            $isEmbeddedShortcode = true;
            include $templatePath;
        }
        return (string) ob_get_clean();
    }

    public function isAuthorized(): bool
    {
        // 1. Logged in user with any cashier or admin permissions
        if (is_user_logged_in()) {
            // Administrator is ALWAYS authorized
            if (current_user_can('manage_options') || current_user_can('administrator')) {
                return true;
            }

            // Direct capability
            if (current_user_can('clubcore_add_members')) {
                return true;
            }

            // Role-based authorization configured in settings
            $allowedRoles = (array) get_option('clubcore_terminal_allowed_roles', ['administrator', 'shop_manager']);
            $user = wp_get_current_user();
            if (!empty($user->roles)) {
                foreach ($user->roles as $role) {
                    if (in_array($role, $allowedRoles, true)) {
                        return true;
                    }
                }
            }

            // Regular subscriber, customer, or other unauthorized roles
            return false;
        }

        // 2. Guest / Non-logged-in session
        $guestMode = (string) get_option('clubcore_terminal_guest_mode', 'login_required');
        $requirePin = (bool) get_option('clubcore_terminal_require_pin', '0');

        // Check if PIN mode is enabled
        if ($guestMode === 'allow_pin' || $requirePin) {
            if (!empty($_COOKIE[self::SESSION_COOKIE])) {
                $hash = sanitize_text_field(wp_unslash($_COOKIE[self::SESSION_COOKIE]));
                if ($hash === $this->generateSessionHash()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function handleVerifyPin(): void
    {
        check_ajax_referer('clubcore_terminal_nonce', 'nonce');

        $enteredPin = sanitize_text_field(wp_unslash($_POST['pin'] ?? ''));
        $configuredPin = (string) get_option('clubcore_terminal_pin', '1234');

        if ($enteredPin === $configuredPin) {
            $hash = $this->generateSessionHash();
            $expire = time() + (86400 * 30); // 30 days
            setcookie(self::SESSION_COOKIE, $hash, [
                'expires'  => $expire,
                'path'     => COOKIEPATH ?: '/',
                'domain'   => COOKIE_DOMAIN ?: '',
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            wp_send_json_success([
                'message' => __('ورود با موفقیت انجام شد.', 'clubcore'),
            ]);
        }

        wp_send_json_error([
            'message' => __('کد پین وارد شده اشتباه است.', 'clubcore'),
        ], 403);
    }

    public function handleRegister(): void
    {
        check_ajax_referer('clubcore_terminal_nonce', 'nonce');

        if (!$this->isAuthorized()) {
            $canPin = !is_user_logged_in() && ((bool) get_option('clubcore_terminal_require_pin', '0') || (string) get_option('clubcore_terminal_guest_mode', 'login_required') === 'allow_pin');
            wp_send_json_error([
                'message' => __('شما اجازه دسترسی به دستگاه ثبت مشتری را ندارید.', 'clubcore'),
                'need_pin' => $canPin,
            ], 403);
        }

        $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
        $firstName = sanitize_text_field(wp_unslash($_POST['first_name'] ?? ''));
        $lastName = sanitize_text_field(wp_unslash($_POST['last_name'] ?? ''));

        if (empty($phone)) {
            wp_send_json_error([
                'message' => __('لطفاً شماره موبایل را وارد نمایید.', 'clubcore'),
            ]);
        }

        try {
            $request = new CreateMemberRequest(
                phone: $phone,
                firstName: $firstName,
                lastName: $lastName,
                email: '',
            );

            $result = $this->createMemberUseCase->execute($request);

            if ($result->status === 'created') {
                $member = $result->member;
                $smsStatus = 'pending';
                $smsMessage = '';

                // Attempt to send welcome SMS
                if ($this->smsService !== null && $member !== null) {
                    try {
                        $smsResult = $this->smsService->sendMemberSms($member, 'welcome');
                        $smsStatus = $smsResult->isSuccess() ? 'sent' : 'failed';
                        $smsMessage = $smsResult->isSuccess() ? __('پیامک خوش‌آمدگویی با موفقیت ارسال شد.', 'clubcore') : $smsResult->getErrorMessage();
                    } catch (\Throwable $e) {
                        $smsStatus = 'failed';
                        $smsMessage = $e->getMessage();
                    }
                }

                $displayName = trim($firstName . ' ' . $lastName);
                $greeting = $displayName ? sprintf(__('مشتری گرامی، %s، به باشگاه مشتریان خوش آمدید!', 'clubcore'), $displayName) : __('به باشگاه مشتریان خوش آمدید!', 'clubcore');

                wp_send_json_success([
                    'status' => 'created',
                    'message' => __('مشتری با موفقیت در باشگاه ثبت شد.', 'clubcore'),
                    'greeting' => $greeting,
                    'phone' => $member?->getPhoneDisplay() ?: $phone,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'sms_status' => $smsStatus,
                    'sms_message' => $smsMessage,
                    'today_count' => $this->getTodayRegisteredCount(),
                ]);
            } elseif ($result->status === 'already_exists') {
                $m = $result->member;
                $name = $m ? trim($m->getFirstName() . ' ' . $m->getLastName()) : '';
                $msg = $name ? sprintf(__('این شماره قبلاً در باشگاه ثبت شده است (%s)', 'clubcore'), $name) : __('این شماره قبلاً در باشگاه مشتریان ثبت شده است.', 'clubcore');

                wp_send_json_success([
                    'status' => 'already_exists',
                    'message' => $msg,
                    'phone' => $m?->getPhoneDisplay() ?: $phone,
                    'first_name' => $m?->getFirstName() ?: '',
                    'last_name' => $m?->getLastName() ?: '',
                    'today_count' => $this->getTodayRegisteredCount(),
                ]);
            } else {
                wp_send_json_error([
                    'status' => $result->status,
                    'message' => $result->message ?: __('خطا در ثبت مشتری.', 'clubcore'),
                ]);
            }
        } catch (\Throwable $e) {
            wp_send_json_error([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function handleGetStats(): void
    {
        check_ajax_referer('clubcore_terminal_nonce', 'nonce');
        wp_send_json_success([
            'today_count' => $this->getTodayRegisteredCount(),
        ]);
    }

    public function getTodayRegisteredCount(): int
    {
        global $wpdb;
        $table = $wpdb->prefix . 'clubcore_members';
        $todayStart = current_time('Y-m-d 00:00:00');
        
        // Check if table exists
        $var = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE created_at >= %s",
            $todayStart
        ));

        return (int) $var;
    }

    private function generateSessionHash(): string
    {
        $pin = (string) get_option('clubcore_terminal_pin', '1234');
        return wp_hash('clubcore_terminal_' . $pin . '_' . wp_salt('auth'));
    }

    public function addAdminBarMenu(\WP_Admin_Bar $wp_admin_bar): void
    {
        if (!$this->isAuthorized()) {
            return;
        }

        $wp_admin_bar->add_node([
            'id'    => 'clubcore_terminal_launcher',
            'title' => '📱 ' . __('دستگاه ثبت مشتری (کیوسک)', 'clubcore'),
            'href'  => $this->getTerminalUrl(),
            'meta'  => [
                'target' => '_blank',
                'title'  => __('باز کردن رابط دستگاه لمسی باشگاه مشتریان', 'clubcore'),
            ],
        ]);
    }
}
