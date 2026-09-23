<?php

declare(strict_types=1);

namespace ClubCore\Domain\Exception;

/**
 * Exception for SMS-related errors.
 *
 * Maps provider errors to domain-level error types.
 *
 * @package ClubCore\Domain\Exception
 */
class SmsException extends \RuntimeException
{
    /**
     * Domain error types for SMS failures.
     */
    public const ERROR_AUTHENTICATION = 'AUTHENTICATION_ERROR';
    public const ERROR_INVALID_PHONE = 'INVALID_PHONE';
    public const ERROR_INVALID_PATTERN = 'INVALID_PATTERN';
    public const ERROR_INSUFFICIENT_CREDIT = 'INSUFFICIENT_CREDIT';
    public const ERROR_RATE_LIMITED = 'RATE_LIMITED';
    public const ERROR_TIMEOUT = 'TIMEOUT';
    public const ERROR_PROVIDER = 'PROVIDER_ERROR';
    public const ERROR_VARIABLE_MISMATCH = 'VARIABLE_MISMATCH';
    public const ERROR_MISSING_VARIABLE = 'MISSING_VARIABLE';
    public const ERROR_UNKNOWN = 'UNKNOWN_ERROR';
    public const ERROR_SEND_TIME = 'SEND_TIME_RESTRICTED';
    public const ERROR_IP_BLOCKED = 'IP_BLOCKED';
    public const ERROR_LINK_IN_VARIABLES = 'LINK_IN_VARIABLES';

    private readonly string $errorType;
    private readonly string $providerCode;
    private readonly string $providerMessage;

    /**
     * @param string $errorType      Domain error type constant.
     * @param string $message        Human-readable error message.
     * @param string $providerCode   Original provider error code.
     * @param string $providerMessage Original provider error message.
     */
    public function __construct(
        string $errorType,
        string $message,
        string $providerCode = '',
        string $providerMessage = '',
    ) {
        parent::__construct($message);
        $this->errorType = $errorType;
        $this->providerCode = $providerCode;
        $this->providerMessage = $providerMessage;
    }

    /**
     * Get the domain error type.
     *
     * @return string One of the ERROR_* constants.
     */
    public function getErrorType(): string
    {
        return $this->errorType;
    }

    /**
     * Get the original provider error code.
     *
     * @return string
     */
    public function getProviderCode(): string
    {
        return $this->providerCode;
    }

    /**
     * Get the original provider error message.
     *
     * @return string
     */
    public function getProviderMessage(): string
    {
        return $this->providerMessage;
    }

    /**
     * Get user-friendly error message.
     *
     * @return string Translated error message for UI display.
     */
    public function getUserMessage(): string
    {
        return match ($this->errorType) {
            self::ERROR_AUTHENTICATION => __('خطای احراز هویت سرویس پیامک. لطفاً تنظیمات را بررسی کنید.', 'clubcore'),
            self::ERROR_INVALID_PHONE => __('شماره موبایل نامعتبر است.', 'clubcore'),
            self::ERROR_INVALID_PATTERN => __('الگوی پیامک نامعتبر است یا تأیید نشده است.', 'clubcore'),
            self::ERROR_INSUFFICIENT_CREDIT => __('اعتبار سرویس پیامک کافی نیست.', 'clubcore'),
            self::ERROR_RATE_LIMITED => __('محدودیت تعداد ارسال. لطفاً کمی صبر کنید.', 'clubcore'),
            self::ERROR_TIMEOUT => __('زمان پاسخگویی سرویس پیامک به پایان رسید.', 'clubcore'),
            self::ERROR_VARIABLE_MISMATCH => __('متغیرهای ارسالی با الگوی پیامک مطابقت ندارد.', 'clubcore'),
            self::ERROR_MISSING_VARIABLE => __('مقدار متغیرهای الزامی الگوی پیامک ارسال نشده است.', 'clubcore'),
            self::ERROR_SEND_TIME => __('ارسال پیامک در این ساعت امکان‌پذیر نیست. (ساعت مجاز: ۷ صبح تا ۱۰ شب)', 'clubcore'),
            self::ERROR_IP_BLOCKED => __('آدرس IP سرور مسدود شده است. لطفاً با پشتیبانی تماس بگیرید.', 'clubcore'),
            self::ERROR_LINK_IN_VARIABLES => __('متغیرهای ارسالی نباید حاوی لینک باشند.', 'clubcore'),
            default => __('خطا در ارسال پیامک. لطفاً دوباره تلاش کنید.', 'clubcore'),
        };
    }

    /**
     * Whether this error is transient and can be retried.
     *
     * @return bool
     */
    public function isRetryable(): bool
    {
        return in_array($this->errorType, [
            self::ERROR_TIMEOUT,
            self::ERROR_RATE_LIMITED,
            self::ERROR_PROVIDER,
        ], true);
    }

    /**
     * Factory: authentication error.
     */
    public static function authenticationError(string $providerCode = '', string $providerMessage = ''): self
    {
        return new self(self::ERROR_AUTHENTICATION, 'SMS provider authentication failed', $providerCode, $providerMessage);
    }

    /**
     * Factory: invalid phone.
     */
    public static function invalidPhone(string $providerCode = '', string $providerMessage = ''): self
    {
        return new self(self::ERROR_INVALID_PHONE, 'Invalid phone number', $providerCode, $providerMessage);
    }

    /**
     * Factory: invalid pattern.
     */
    public static function invalidPattern(string $providerCode = '', string $providerMessage = ''): self
    {
        return new self(self::ERROR_INVALID_PATTERN, 'Invalid or unapproved pattern', $providerCode, $providerMessage);
    }

    /**
     * Factory: insufficient credit.
     */
    public static function insufficientCredit(string $providerCode = '', string $providerMessage = ''): self
    {
        return new self(self::ERROR_INSUFFICIENT_CREDIT, 'Insufficient SMS credit', $providerCode, $providerMessage);
    }

    /**
     * Factory: rate limited.
     */
    public static function rateLimited(string $providerCode = '', string $providerMessage = ''): self
    {
        return new self(self::ERROR_RATE_LIMITED, 'SMS rate limit exceeded', $providerCode, $providerMessage);
    }

    /**
     * Factory: timeout.
     */
    public static function timeout(): self
    {
        return new self(self::ERROR_TIMEOUT, 'SMS provider request timeout');
    }

    /**
     * Factory: provider error.
     */
    public static function providerError(string $providerCode = '', string $providerMessage = ''): self
    {
        return new self(self::ERROR_PROVIDER, 'SMS provider error', $providerCode, $providerMessage);
    }

    /**
     * Factory: variable mismatch.
     */
    public static function variableMismatch(string $details = ''): self
    {
        return new self(self::ERROR_VARIABLE_MISMATCH, 'Pattern variable mismatch: ' . $details);
    }

    /**
     * Factory: missing required variable.
     */
    public static function missingVariable(string $variableName): self
    {
        return new self(
            self::ERROR_MISSING_VARIABLE,
            sprintf('Required pattern variable missing: %s', $variableName),
        );
    }
}
