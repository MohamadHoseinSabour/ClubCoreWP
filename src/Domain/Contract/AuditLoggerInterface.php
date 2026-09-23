<?php

declare(strict_types=1);

namespace ClubCore\Domain\Contract;

/**
 * Audit Logger contract.
 *
 * Used for logging important domain events and changes.
 *
 * @package ClubCore\Domain\Contract
 */
interface AuditLoggerInterface
{
    /**
     * Log an action.
     *
     * @param string $action     The action performed.
     * @param string $objectType The type of object affected.
     * @param int    $objectId   The ID of the object.
     * @param string $result     The result of the action (e.g., success, error).
     * @param string $context    Additional context as JSON or text.
     *
     * @return void
     */
    public function log(string $action, string $objectType, int $objectId, string $result, string $context): void;
}
