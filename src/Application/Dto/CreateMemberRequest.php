<?php

declare(strict_types=1);

namespace ClubCore\Application\Dto;

use ClubCore\Domain\ValueObject\MembershipSource;

/**
 * DTO for creating a member.
 *
 * @package ClubCore\Application\Dto
 */
readonly class CreateMemberRequest
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
        public string $phone,
        public string $firstName = '',
        public string $lastName = '',
        public string $email = '',
        public MembershipSource $source = MembershipSource::Admin,
        public bool $sendSms = true,
        public string $requestType = 'welcome'
    ) {
    }
}
