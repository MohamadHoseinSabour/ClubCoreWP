<?php

declare(strict_types=1);

namespace ClubCore\Domain\Entity;

/**
 * Audit log entry entity.
 *
 * Records actions performed in the Customer Club system.
 * Sensitive data (passwords, API keys) must NEVER be stored in context.
 *
 * @package ClubCore\Domain\Entity
 */
class AuditEntry
{
    // Action constants.
    public const ACTION_MEMBER_CREATED = 'member_created';
    public const ACTION_MEMBER_UPDATED = 'member_updated';
    public const ACTION_MEMBER_DELETED = 'member_deleted';
    public const ACTION_MEMBER_IMPORTED = 'member_imported';
    public const ACTION_SMS_SENT = 'sms_sent';
    public const ACTION_SMS_FAILED = 'sms_failed';
    public const ACTION_SMS_RESENT = 'sms_resent';
    public const ACTION_TEST_SMS_SENT = 'test_sms_sent';
    public const ACTION_IMPORT_STARTED = 'import_started';
    public const ACTION_IMPORT_COMPLETED = 'import_completed';
    public const ACTION_IMPORT_FAILED = 'import_failed';
    public const ACTION_EXPORT_CREATED = 'export_created';
    public const ACTION_SETTINGS_UPDATED = 'settings_updated';
    public const ACTION_SMS_CREDENTIALS_UPDATED = 'sms_credentials_updated';
    public const ACTION_PATTERN_UPDATED = 'pattern_updated';
    public const ACTION_ROLE_PERMISSIONS_UPDATED = 'role_permissions_updated';
    public const ACTION_APPEARANCE_UPDATED = 'appearance_updated';
    public const ACTION_WC_CUSTOMER_LINKED = 'wc_customer_linked';
    public const ACTION_WC_CUSTOMER_SYNCED = 'wc_customer_synced';
    public const ACTION_WC_AUTO_ENROLLMENT = 'wc_auto_enrollment';

    // Object types.
    public const OBJECT_MEMBER = 'member';
    public const OBJECT_SMS = 'sms';
    public const OBJECT_IMPORT = 'import';
    public const OBJECT_EXPORT = 'export';
    public const OBJECT_SETTINGS = 'settings';
    public const OBJECT_WOOCOMMERCE = 'woocommerce';

    // Result types.
    public const RESULT_SUCCESS = 'success';
    public const RESULT_FAILURE = 'failure';
    public const RESULT_SKIPPED = 'skipped';

    private int $id = 0;
    private int $actorUserId;
    private string $action;
    private string $objectType;
    private int $objectId;
    private string $result;
    private ?string $context;
    private string $ipHash;
    private string $createdAt;

    /**
     * Create a new audit entry.
     *
     * @param string $action     Action performed (use ACTION_* constants).
     * @param string $objectType Type of object (use OBJECT_* constants).
     * @param int    $objectId   ID of the affected object.
     * @param string $result     Result of the action (use RESULT_* constants).
     * @param string $context    Optional JSON-encoded context data.
     */
    public function __construct(
        string $action,
        string $objectType = '',
        int $objectId = 0,
        string $result = self::RESULT_SUCCESS,
        string $context = '',
    ) {
        $this->actorUserId = get_current_user_id();
        $this->action = $action;
        $this->objectType = $objectType;
        $this->objectId = $objectId;
        $this->result = $result;
        $this->context = $context ?: null;
        $this->ipHash = self::hashIp();
        $this->createdAt = current_time('mysql', true);
    }

    /**
     * Hydrate from database row.
     *
     * @param object|array $data Database row.
     *
     * @return self
     */
    public static function fromRow(object|array $data): self
    {
        $data = (object) $data;
        $entry = new self(
            $data->action ?? '',
            $data->object_type ?? '',
            (int) ($data->object_id ?? 0),
            $data->result ?? self::RESULT_SUCCESS,
            $data->context ?? '',
        );
        $entry->id = (int) ($data->id ?? 0);
        $entry->actorUserId = (int) ($data->actor_user_id ?? 0);
        $entry->ipHash = $data->ip_hash ?? '';
        $entry->createdAt = $data->created_at ?? '';
        return $entry;
    }

    /**
     * Convert to array for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'actor_user_id' => $this->actorUserId,
            'action' => $this->action,
            'object_type' => $this->objectType,
            'object_id' => $this->objectId,
            'result' => $this->result,
            'context' => $this->context,
            'ip_hash' => $this->ipHash,
            'created_at' => $this->createdAt,
        ];
    }

    // ─── Getters ───────────────────────────────────────────

    public function getId(): int { return $this->id; }
    public function getActorUserId(): int { return $this->actorUserId; }
    public function getAction(): string { return $this->action; }
    public function getObjectType(): string { return $this->objectType; }
    public function getObjectId(): int { return $this->objectId; }
    public function getResult(): string { return $this->result; }
    public function getContext(): ?string { return $this->context; }
    public function getIpHash(): string { return $this->ipHash; }
    public function getCreatedAt(): string { return $this->createdAt; }

    /**
     * Get decoded context data.
     *
     * @return array<string, mixed>
     */
    public function getContextData(): array
    {
        if (empty($this->context)) {
            return [];
        }
        $decoded = json_decode($this->context, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Get human-readable action label.
     *
     * @return string
     */
    public function getActionLabel(): string
    {
        return match ($this->action) {
            self::ACTION_MEMBER_CREATED => __('ایجاد عضو', 'clubcore'),
            self::ACTION_MEMBER_UPDATED => __('ویرایش عضو', 'clubcore'),
            self::ACTION_MEMBER_DELETED => __('حذف عضو', 'clubcore'),
            self::ACTION_MEMBER_IMPORTED => __('وارد کردن عضو', 'clubcore'),
            self::ACTION_SMS_SENT => __('ارسال پیامک', 'clubcore'),
            self::ACTION_SMS_FAILED => __('خطا در ارسال پیامک', 'clubcore'),
            self::ACTION_SMS_RESENT => __('ارسال مجدد پیامک', 'clubcore'),
            self::ACTION_TEST_SMS_SENT => __('ارسال پیامک آزمایشی', 'clubcore'),
            self::ACTION_IMPORT_STARTED => __('شروع وارد کردن', 'clubcore'),
            self::ACTION_IMPORT_COMPLETED => __('پایان وارد کردن', 'clubcore'),
            self::ACTION_IMPORT_FAILED => __('خطا در وارد کردن', 'clubcore'),
            self::ACTION_EXPORT_CREATED => __('خروجی گرفتن', 'clubcore'),
            self::ACTION_SETTINGS_UPDATED => __('بروزرسانی تنظیمات', 'clubcore'),
            self::ACTION_SMS_CREDENTIALS_UPDATED => __('بروزرسانی اطلاعات پیامک', 'clubcore'),
            self::ACTION_PATTERN_UPDATED => __('بروزرسانی الگوی پیامک', 'clubcore'),
            self::ACTION_ROLE_PERMISSIONS_UPDATED => __('بروزرسانی دسترسی نقش‌ها', 'clubcore'),
            self::ACTION_APPEARANCE_UPDATED => __('بروزرسانی ظاهر', 'clubcore'),
            self::ACTION_WC_CUSTOMER_LINKED => __('اتصال مشتری ووکامرس', 'clubcore'),
            self::ACTION_WC_CUSTOMER_SYNCED => __('همگام‌سازی مشتری ووکامرس', 'clubcore'),
            self::ACTION_WC_AUTO_ENROLLMENT => __('عضویت خودکار ووکامرس', 'clubcore'),
            default => $this->action,
        };
    }

    /**
     * Hash an IP address for privacy.
     *
     * @return string SHA-256 hash of the IP with a salt.
     */
    private static function hashIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (empty($ip)) {
            return '';
        }
        $salt = wp_salt('auth');
        return hash('sha256', $ip . $salt);
    }

    /**
     * Mask a phone number for privacy in context data.
     *
     * Shows first 4 and last 2 digits: 0912***xx89
     *
     * @param string $phone Phone number.
     *
     * @return string Masked phone.
     */
    public static function maskPhone(string $phone): string
    {
        $length = mb_strlen($phone);
        if ($length < 6) {
            return str_repeat('*', $length);
        }
        return mb_substr($phone, 0, 4) . str_repeat('*', $length - 6) . mb_substr($phone, -2);
    }
}
