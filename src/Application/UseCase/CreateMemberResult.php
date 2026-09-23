<?php

declare(strict_types=1);

namespace ClubCore\Application\UseCase;

use ClubCore\Domain\Entity\Member;

/**
 * Result object for creating a member.
 *
 * @package ClubCore\Application\UseCase
 */
readonly class CreateMemberResult
{
    /**
     * @param string      $status  Status of the operation (created, already_exists, validation_error, error).
     * @param Member|null $member  The member entity if successful or already exists.
     * @param int|null    $userId  The WordPress user ID if applicable.
     * @param string      $message A descriptive message.
     * @param array       $errors  Array of error messages.
     */
    public function __construct(
        public string $status,
        public ?Member $member,
        public ?int $userId,
        public string $message,
        public array $errors = []
    ) {
    }
}
