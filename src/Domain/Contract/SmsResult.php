<?php

declare(strict_types=1);

namespace ClubCore\Domain\Contract;

/**
 * SMS send result value object.
 *
 * Represents the outcome of an SMS send operation.
 *
 * @package ClubCore\Domain\Contract
 */
final class SmsResult
{
    /**
     * @param bool        $success           Whether the SMS was sent successfully.
     * @param string      $providerReference Provider's reference/transaction ID (recId).
     * @param string      $providerCode      Provider status code.
     * @param string      $providerMessage   Provider status message.
     * @param string      $errorType         Domain error type (from SmsException constants).
     * @param string      $userMessage       User-friendly error message.
     * @param float       $duration          API call duration in seconds.
     */
    public function __construct(
        public readonly bool $success,
        public readonly string $providerReference = '',
        public readonly string $providerCode = '',
        public readonly string $providerMessage = '',
        public readonly string $errorType = '',
        public readonly string $userMessage = '',
        public readonly float $duration = 0.0,
    ) {}

    /**
     * Create a success result.
     *
     * @param string $providerReference Provider's reference ID.
     * @param float  $duration          API call duration.
     *
     * @return self
     */
    public static function success(string $providerReference, float $duration = 0.0): self
    {
        return new self(
            success: true,
            providerReference: $providerReference,
            duration: $duration,
        );
    }

    /**
     * Create a failure result.
     *
     * @param string $errorType      Domain error type.
     * @param string $userMessage    User-friendly error message.
     * @param string $providerCode   Provider error code.
     * @param string $providerMessage Provider error message.
     * @param float  $duration       API call duration.
     *
     * @return self
     */
    public static function failure(
        string $errorType,
        string $userMessage,
        string $providerCode = '',
        string $providerMessage = '',
        float $duration = 0.0,
    ): self {
        return new self(
            success: false,
            providerCode: $providerCode,
            providerMessage: $providerMessage,
            errorType: $errorType,
            userMessage: $userMessage,
            duration: $duration,
        );
    }
}
