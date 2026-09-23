<?php

declare(strict_types=1);

namespace ClubCore\Presentation\Admin;

/**
 * Admin Bootstrap.
 *
 * Initializes admin-only components: menus, pages, assets, AJAX handlers.
 *
 * @package ClubCore\Presentation\Admin
 */
class AdminBootstrap
{
    private MenuManager $menuManager;
    private AssetManager $assetManager;

    public function __construct()
    {
        $this->menuManager = new MenuManager();
        $this->assetManager = new AssetManager();
    }

    /**
     * Initialize admin hooks.
     */
    public function init(): void
    {
        $this->menuManager->init();
        $this->assetManager->init();

        // AJAX handlers.
        add_action('wp_ajax_clubcore_create_member', [$this, 'handleCreateMember']);
        add_action('wp_ajax_clubcore_send_test_sms', [$this, 'handleSendTestSms']);
        add_action('wp_ajax_clubcore_import_preview', [$this, 'handleImportPreview']);
        add_action('wp_ajax_clubcore_import_execute', [$this, 'handleImportExecute']);
        add_action('wp_ajax_clubcore_resend_sms', [$this, 'handleResendSms']);
        add_action('wp_ajax_clubcore_save_settings', [$this, 'handleSaveSettings']);
    }

    /**
     * AJAX: Create member.
     */
    public function handleCreateMember(): void
    {
        check_ajax_referer('clubcore_create_member', 'nonce');

        if (!current_user_can('clubcore_add_members')) {
            wp_send_json_error([
                'message' => __('شما دسترسی انجام این عملیات را ندارید.', 'clubcore'),
            ], 403);
        }

        $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
        $firstName = sanitize_text_field(wp_unslash($_POST['first_name'] ?? ''));
        $lastName = sanitize_text_field(wp_unslash($_POST['last_name'] ?? ''));
        $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));

        if (empty($phone)) {
            wp_send_json_error([
                'message' => __('شماره موبایل الزامی است.', 'clubcore'),
            ]);
        }

        try {
            $plugin = \ClubCore\Plugin::init();
            $useCase = $plugin->getCreateMemberUseCase();

            $request = new \ClubCore\Application\Dto\CreateMemberRequest(
                phone: $phone,
                firstName: $firstName,
                lastName: $lastName,
                email: $email,
            );

            $result = $useCase->execute($request);

            if ($result->status === 'created') {
                wp_send_json_success([
                    'status' => 'created',
                    'message' => __('مشتری با موفقیت ثبت شد.', 'clubcore'),
                    'member' => [
                        'id' => $result->member?->getId(),
                        'user_id' => $result->member?->getUserId(),
                        'phone' => $result->member?->getPhoneDisplay(),
                        'first_name' => $result->member?->getFirstName(),
                        'last_name' => $result->member?->getLastName(),
                        'membership_status' => $result->member?->getMembershipStatus(),
                        'created_at' => $result->member?->getMembershipCreatedAt(),
                    ],
                    'sms_status' => $result->smsStatus ?? '',
                    'sms_reference' => $result->smsReference ?? '',
                ]);
            } elseif ($result->status === 'already_exists') {
                wp_send_json_success([
                    'status' => 'already_exists',
                    'message' => __('این مشتری قبلاً در باشگاه ثبت شده است.', 'clubcore'),
                    'member' => [
                        'id' => $result->member?->getId(),
                        'phone' => $result->member?->getPhoneDisplay(),
                        'first_name' => $result->member?->getFirstName(),
                        'last_name' => $result->member?->getLastName(),
                    ],
                ]);
            } else {
                wp_send_json_error([
                    'status' => $result->status,
                    'message' => $result->message,
                    'errors' => $result->errors,
                ]);
            }
        } catch (\ClubCore\Domain\Exception\InvalidPhoneException $e) {
            wp_send_json_error([
                'status' => 'validation_error',
                'message' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'status' => 'error',
                'message' => __('خطایی رخ داد. لطفاً دوباره تلاش کنید.', 'clubcore'),
            ]);
        }
    }

    /**
     * AJAX: Send test SMS.
     */
    public function handleSendTestSms(): void
    {
        check_ajax_referer('clubcore_test_sms', 'nonce');

        if (!current_user_can('clubcore_manage_settings')) {
            wp_send_json_error([
                'message' => __('شما دسترسی انجام این عملیات را ندارید.', 'clubcore'),
            ], 403);
        }

        $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
        $firstName = sanitize_text_field(wp_unslash($_POST['first_name'] ?? ''));
        $lastName = sanitize_text_field(wp_unslash($_POST['last_name'] ?? ''));

        if (empty($phone)) {
            wp_send_json_error([
                'message' => __('شماره موبایل الزامی است.', 'clubcore'),
            ]);
        }

        try {
            $plugin = \ClubCore\Plugin::init();
            $smsService = $plugin->getSmsService();
            $result = $smsService->sendTestSms($phone, $firstName, $lastName);

            if ($result->success) {
                wp_send_json_success([
                    'message' => __('پیامک آزمایشی با موفقیت ارسال شد.', 'clubcore'),
                    'reference' => $result->providerReference,
                ]);
            } else {
                wp_send_json_error([
                    'message' => $result->userMessage ?: __('خطا در ارسال پیامک آزمایشی.', 'clubcore'),
                    'error_type' => $result->errorType,
                ]);
            }
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => __('خطا در ارسال پیامک آزمایشی.', 'clubcore'),
            ]);
        }
    }

    /**
     * AJAX: Resend SMS.
     */
    public function handleResendSms(): void
    {
        check_ajax_referer('clubcore_resend_sms', 'nonce');

        if (!current_user_can('clubcore_send_sms')) {
            wp_send_json_error([
                'message' => __('شما دسترسی انجام این عملیات را ندارید.', 'clubcore'),
            ], 403);
        }

        $memberId = absint($_POST['member_id'] ?? 0);
        if (!$memberId) {
            wp_send_json_error([
                'message' => __('شناسه عضو نامعتبر است.', 'clubcore'),
            ]);
        }

        // Rate limit check: max 3 resends per member per hour.
        $rateLimitKey = 'clubcore_resend_' . $memberId;
        $attempts = (int) get_transient($rateLimitKey);
        if ($attempts >= 3) {
            wp_send_json_error([
                'message' => __('تعداد ارسال مجدد به حداکثر رسیده است. لطفاً کمی صبر کنید.', 'clubcore'),
            ]);
        }

        try {
            $plugin = \ClubCore\Plugin::init();
            $smsService = $plugin->getSmsService();
            $memberRepo = $plugin->getMemberRepository();

            $member = $memberRepo->findById($memberId);
            if (!$member) {
                wp_send_json_error([
                    'message' => __('عضو یافت نشد.', 'clubcore'),
                ]);
            }

            $result = $smsService->sendMemberSms($member, 'resend');

            // Update rate limit.
            set_transient($rateLimitKey, $attempts + 1, HOUR_IN_SECONDS);

            if ($result->success) {
                wp_send_json_success([
                    'message' => __('پیامک با موفقیت ارسال مجدد شد.', 'clubcore'),
                    'reference' => $result->providerReference,
                ]);
            } else {
                wp_send_json_error([
                    'message' => $result->userMessage ?: __('خطا در ارسال مجدد پیامک.', 'clubcore'),
                ]);
            }
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => __('خطا در ارسال مجدد پیامک.', 'clubcore'),
            ]);
        }
    }

    /**
     * AJAX: Import preview.
     */
    public function handleImportPreview(): void
    {
        check_ajax_referer('clubcore_import', 'nonce');

        if (!current_user_can('clubcore_import_members')) {
            wp_send_json_error([
                'message' => __('شما دسترسی انجام این عملیات را ندارید.', 'clubcore'),
            ], 403);
        }

        $sourceType = sanitize_text_field(wp_unslash($_POST['source_type'] ?? 'file'));
        $filePath = null;
        $rawText = null;

        if ($sourceType === 'file') {
            if (empty($_FILES['import_file']['tmp_name'])) {
                wp_send_json_error(['message' => __('فایلی آپلود نشده است.', 'clubcore')]);
            }
            $filePath = $_FILES['import_file']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['csv', 'txt', 'xlsx'], true)) {
                wp_send_json_error(['message' => __('فرمت فایل مجاز نیست (فقط CSV و XLSX).', 'clubcore')]);
            }
            if ($ext === 'xlsx') {
                $sourceType = 'xlsx';
            }
        } else {
            $rawText = wp_unslash($_POST['raw_text'] ?? '');
            if (empty(trim($rawText))) {
                wp_send_json_error(['message' => __('متنی برای پردازش وارد نشده است.', 'clubcore')]);
            }
        }

        try {
            $importService = \ClubCore\Plugin::init()->getImportService();
            $preview = $importService->preview($sourceType, $filePath, $rawText);
            wp_send_json_success(['preview' => $preview]);
        } catch (\Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Import execute.
     */
    public function handleImportExecute(): void
    {
        check_ajax_referer('clubcore_import', 'nonce');

        if (!current_user_can('clubcore_import_members')) {
            wp_send_json_error([
                'message' => __('شما دسترسی انجام این عملیات را ندارید.', 'clubcore'),
            ], 403);
        }

        $sourceType = sanitize_text_field(wp_unslash($_POST['source_type'] ?? 'file'));
        $mode = sanitize_text_field(wp_unslash($_POST['mode'] ?? 'skip_duplicates'));
        $sendSms = !empty($_POST['send_sms']);
        $dryRun = !empty($_POST['dry_run']);
        $filePath = null;
        $rawText = null;

        if ($sourceType === 'file') {
            if (empty($_FILES['import_file']['tmp_name'])) {
                wp_send_json_error(['message' => __('فایل یافت نشد.', 'clubcore')]);
            }
            $filePath = $_FILES['import_file']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION));
            if ($ext === 'xlsx') {
                $sourceType = 'xlsx';
            }
        } else {
            $rawText = wp_unslash($_POST['raw_text'] ?? '');
        }

        try {
            $importService = \ClubCore\Plugin::init()->getImportService();
            // Default mapping
            $mapping = ['phone' => 0, 'first_name' => 1, 'last_name' => 2, 'email' => 3];
            $res = $importService->execute(
                $sourceType,
                $filePath,
                $rawText,
                $mapping,
                $mode,
                $dryRun,
                $sendSms
            );
            wp_send_json_success($res);
        } catch (\Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Save settings.
     */
    public function handleSaveSettings(): void
    {
        check_ajax_referer('clubcore_save_settings', 'nonce');

        if (!current_user_can('clubcore_manage_settings')) {
            wp_send_json_error([
                'message' => __('شما دسترسی انجام این عملیات را ندارید.', 'clubcore'),
            ], 403);
        }

        // Implementation will be completed in Phase 9.
        wp_send_json_error(['message' => __('این قابلیت در حال توسعه است.', 'clubcore')]);
    }
}
