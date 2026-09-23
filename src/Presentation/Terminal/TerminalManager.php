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

    public function renderShortcode(): string
    {
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
            if (current_user_can('clubcore_add_members') || current_user_can('manage_options') || current_user_can('edit_posts')) {
                return true;
            }
        }

        // 2. Check if PIN requirement is disabled
        $requirePin = (bool) get_option('clubcore_terminal_require_pin', '0');
        if (!$requirePin) {
            return true;
        }

        // 3. Check session cookie
        if (!empty($_COOKIE[self::SESSION_COOKIE])) {
            $hash = sanitize_text_field(wp_unslash($_COOKIE[self::SESSION_COOKIE]));
            if ($hash === $this->generateSessionHash()) {
                return true;
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
            wp_send_json_error([
                'message' => __('شما اجازه دسترسی به دستگاه ثبت مشتری را ندارید. لطفاً پین امنیتی را وارد کنید.', 'clubcore'),
                'need_pin' => true,
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
        if (!current_user_can('clubcore_add_members') && !current_user_can('manage_options')) {
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
