<?php

declare(strict_types=1);

namespace ClubCore\Domain\Contract;

/**
 * SMS provider contract.
 *
 * Defines the boundary between the application and external SMS services.
 * Each provider implementation handles its own authentication, API requests,
 * response parsing, and error mapping.
 *
 * @package ClubCore\Domain\Contract
 */
interface SmsProviderInterface
{
    /**
     * Send a pattern-based SMS.
     *
     * @param string               $to       Recipient phone number (canonical format).
     * @param int|string           $bodyId   Pattern/template ID.
     * @param array<string, string> $variables Variable name-value pairs.
     *
     * @return SmsResult
     *
     * @throws \ClubCore\Domain\Exception\SmsException On send failure.
     */
    public function sendPattern(string $to, int|string $bodyId, array $variables): SmsResult;

    /**
     * Check account credit/balance.
     *
     * @return float|null Credit amount, or null if unavailable.
     */
    public function getCredit(): ?float;

    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Check if the provider is properly configured and ready.
     *
     * @return bool
     */
    public function isConfigured(): bool;
}
