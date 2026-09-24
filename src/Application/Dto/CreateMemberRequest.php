<?php

declare(strict_types=1);

namespace ClubCore\Application\Dto;

use ClubCore\Domain\ValueObject\MembershipSource;

/**
 * DTO for creating a member.
 *
 * @package ClubCore\Application\Dto
 */
class CreateMemberRequest
{
    /**
     * @param string           $phone       Raw input phone number.
     * @param string           $firstName   First name.
     * @param string           $lastName    Last name.
     * @param string           $email       Email address.
     * @param MembershipSource $source      Source of membership.
     * @param bool             $sendSms     Whether to send welcome SMS.
     * @param string           $requestType Log request type (e.g., 'welcome').
     */
    public function __construct(
        public readonly string $phone,
        public readonly string $firstName = '',
        public readonly string $lastName = '',
        public readonly string $email = '',
        public readonly MembershipSource $source = MembershipSource::Admin,
        public readonly bool $sendSms = true,
        public readonly string $requestType = 'welcome'
    ) {
    }
}
