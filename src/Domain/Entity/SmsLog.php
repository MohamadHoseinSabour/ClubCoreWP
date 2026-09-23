<?php

declare(strict_types=1);

namespace ClubCore\Domain\Entity;

/**
 * SMS log entry entity.
 *
 * Records all SMS sending attempts with provider details.
 * API credentials (password, API key, token) must NEVER be stored here.
 *
 * @package ClubCore\Domain\Entity
 */
class SmsLog
{
    private int $id = 0;
    private int $memberId = 0;
    private int $userId = 0;
    private string $phone;
    private string $patternId = '';
    private string $provider = '';
    private string $requestType = 'welcome';
    private string $status = 'pending';
    private string $providerReference = '';
    private string $providerErrorCode = '';
    private ?string $providerErrorMessage = null;
    private string $createdAt;
    private int $createdBy = 0;

    /**
     * Valid request types.
     */
    public const TYPE_WELCOME = 'welcome';
    public const TYPE_RESEND = 'resend';
    public const TYPE_TEST = 'test';
    public const TYPE_IMPORT = 'import';
    public const TYPE_WOOCOMMERCE = 'woocommerce';

    /**
     * Valid statuses.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_QUEUED = 'queued';

    public function __construct(string $phone, string $requestType = self::TYPE_WELCOME)
    {
        $this->phone = $phone;
        $this->requestType = $requestType;
        $this->createdAt = current_time('mysql', true);
        $this->createdBy = get_current_user_id();
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
        $log = new self($data->phone ?? '', $data->request_type ?? self::TYPE_WELCOME);
        $log->id = (int) ($data->id ?? 0);
        $log->memberId = (int) ($data->member_id ?? 0);
        $log->userId = (int) ($data->user_id ?? 0);
        $log->patternId = $data->pattern_id ?? '';
        $log->provider = $data->provider ?? '';
        $log->status = $data->status ?? self::STATUS_PENDING;
        $log->providerReference = $data->provider_reference ?? '';
        $log->providerErrorCode = $data->provider_error_code ?? '';
        $log->providerErrorMessage = $data->provider_error_message ?? null;
        $log->createdAt = $data->created_at ?? '';
        $log->createdBy = (int) ($data->created_by ?? 0);
        return $log;
    }

    /**
     * Convert to array for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'member_id' => $this->memberId,
            'user_id' => $this->userId,
            'phone' => $this->phone,
            'pattern_id' => $this->patternId,
            'provider' => $this->provider,
            'request_type' => $this->requestType,
            'status' => $this->status,
            'provider_reference' => $this->providerReference,
            'provider_error_code' => $this->providerErrorCode,
            'provider_error_message' => $this->providerErrorMessage,
            'created_at' => $this->createdAt,
            'created_by' => $this->createdBy,
        ];
    }

    // ─── Getters ───────────────────────────────────────────

    public function getId(): int { return $this->id; }
    public function getMemberId(): int { return $this->memberId; }
    public function getUserId(): int { return $this->userId; }
    public function getPhone(): string { return $this->phone; }
    public function getPatternId(): string { return $this->patternId; }
    public function getProvider(): string { return $this->provider; }
    public function getRequestType(): string { return $this->requestType; }
    public function getStatus(): string { return $this->status; }
    public function getProviderReference(): string { return $this->providerReference; }
    public function getProviderErrorCode(): string { return $this->providerErrorCode; }
    public function getProviderErrorMessage(): ?string { return $this->providerErrorMessage; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getCreatedBy(): int { return $this->createdBy; }

    public function isSent(): bool { return $this->status === self::STATUS_SENT; }
    public function isFailed(): bool { return $this->status === self::STATUS_FAILED; }

    /**
     * Get human-readable type label.
     */
    public function getTypeLabel(): string
    {
        return match ($this->requestType) {
            self::TYPE_WELCOME => __('خوش‌آمدگویی', 'clubcore'),
            self::TYPE_RESEND => __('ارسال مجدد', 'clubcore'),
            self::TYPE_TEST => __('آزمایشی', 'clubcore'),
            self::TYPE_IMPORT => __('وارد کردن', 'clubcore'),
            self::TYPE_WOOCOMMERCE => __('ووکامرس', 'clubcore'),
            default => $this->requestType,
        };
    }

    /**
     * Get human-readable status label.
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => __('در انتظار', 'clubcore'),
            self::STATUS_SENT => __('ارسال شده', 'clubcore'),
            self::STATUS_FAILED => __('ناموفق', 'clubcore'),
            self::STATUS_QUEUED => __('در صف', 'clubcore'),
            default => $this->status,
        };
    }

    // ─── Setters ───────────────────────────────────────────

    public function setId(int $id): self { $this->id = $id; return $this; }
    public function setMemberId(int $memberId): self { $this->memberId = $memberId; return $this; }
    public function setUserId(int $userId): self { $this->userId = $userId; return $this; }
    public function setPatternId(string $patternId): self { $this->patternId = $patternId; return $this; }
    public function setProvider(string $provider): self { $this->provider = $provider; return $this; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function setProviderReference(string $ref): self { $this->providerReference = $ref; return $this; }
    public function setProviderErrorCode(string $code): self { $this->providerErrorCode = $code; return $this; }
    public function setProviderErrorMessage(?string $msg): self { $this->providerErrorMessage = $msg; return $this; }

    /**
     * Mark as sent with provider reference.
     */
    public function markSent(string $providerReference): self
    {
        $this->status = self::STATUS_SENT;
        $this->providerReference = $providerReference;
        return $this;
    }

    /**
     * Mark as failed with error details.
     */
    public function markFailed(string $errorCode, string $errorMessage): self
    {
        $this->status = self::STATUS_FAILED;
        $this->providerErrorCode = $errorCode;
        $this->providerErrorMessage = $errorMessage;
        return $this;
    }
}
