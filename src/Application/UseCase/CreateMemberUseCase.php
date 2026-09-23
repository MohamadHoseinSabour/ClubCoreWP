<?php

declare(strict_types=1);

namespace ClubCore\Application\UseCase;

use ClubCore\Application\Dto\CreateMemberRequest;
use ClubCore\Domain\Contract\AuditLoggerInterface;
use ClubCore\Domain\Contract\MemberRepositoryInterface;
use ClubCore\Domain\Entity\Member;
use ClubCore\Domain\Exception\InvalidPhoneException;
use ClubCore\Domain\ValueObject\PhoneNumber;

/**
 * Use case to create a new member.
 *
 * @package ClubCore\Application\UseCase
 */
class CreateMemberUseCase
{
    public function __construct(
        private readonly MemberRepositoryInterface $memberRepository,
        private readonly AuditLoggerInterface $auditLogger
    ) {
    }

    /**
     * Execute the use case.
     *
     * @param CreateMemberRequest $request
     *
     * @return CreateMemberResult
     */
    public function execute(CreateMemberRequest $request): CreateMemberResult
    {
        try {
            $phone = PhoneNumber::fromString($request->phone);
        } catch (InvalidPhoneException $e) {
            return new CreateMemberResult(
                'validation_error',
                null,
                null,
                __('شماره موبایل نامعتبر است.', 'clubcore'),
                ['phone' => $e->getMessage()]
            );
        }

        $phoneNormalized = $phone->getNormalized();

        if ($this->memberRepository->existsByPhone($phoneNormalized)) {
            $existingMember = $this->memberRepository->findByPhone($phoneNormalized);
            return new CreateMemberResult(
                'already_exists',
                $existingMember,
                $existingMember?->getUserId(),
                __('این عضو قبلا ثبت شده است.', 'clubcore')
            );
        }

        $userId = $this->findExistingUser($phoneNormalized);

        if (!$userId) {
            $username = 'clubcore_' . $phoneNormalized;
            if (function_exists('wp_unique_username')) {
                $username = wp_unique_username($username);
            }

            $role = class_exists('WooCommerce') ? 'customer' : 'subscriber';

            $userData = [
                'user_login' => $username,
                'user_pass'  => wp_generate_password(24, true, true),
                'first_name' => $request->firstName,
                'last_name'  => $request->lastName,
                'role'       => $role,
            ];

            if (!empty($request->email)) {
                $userData['user_email'] = $request->email;
            }

            $userId = wp_insert_user($userData);

            if (is_wp_error($userId)) {
                $this->auditLogger->log(
                    'create_member_user_failed',
                    'user',
                    0,
                    'error',
                    wp_json_encode(['error' => $userId->get_error_message()])
                );
                
                return new CreateMemberResult(
                    'error',
                    null,
                    null,
                    __('خطا در ساخت کاربر وردپرس.', 'clubcore'),
                    ['wp_error' => $userId->get_error_message()]
                );
            }
            
            update_user_meta($userId, 'billing_phone', $phoneNormalized);
        }

        $member = new Member($phone, $request->source);
        $member->setUserId($userId);
        $member->setFirstName($request->firstName);
        $member->setLastName($request->lastName);
        $member->setEmail($request->email);
        $member->setCreatedBy(get_current_user_id());
        $member->setWoocommerceLinked(class_exists('WooCommerce'));

        $memberId = $this->memberRepository->create($member);
        $member->setId($memberId);

        $this->auditLogger->log(
            'member_created',
            'member',
            $memberId,
            'success',
            wp_json_encode([
                'phone' => $phoneNormalized,
                'source' => $request->source->value,
                'user_id' => $userId
            ])
        );

        do_action('clubcore_member_created', $member);

        return new CreateMemberResult(
            'created',
            $member,
            $userId,
            __('عضو با موفقیت ثبت شد.', 'clubcore')
        );
    }

    /**
     * Check if a WordPress user already exists by phone.
     *
     * @param string $phoneNormalized
     *
     * @return int|null
     */
    private function findExistingUser(string $phoneNormalized): ?int
    {
        global $wpdb;

        // Check user meta billing_phone
        $queryMeta = $wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'billing_phone' AND meta_value = %s LIMIT 1",
            $phoneNormalized
        );
        $userId = $wpdb->get_var($queryMeta);

        if ($userId) {
            return (int) $userId;
        }

        // Check username pattern
        $username = 'clubcore_' . $phoneNormalized;
        $queryUser = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->users} WHERE user_login = %s LIMIT 1",
            $username
        );
        $userId = $wpdb->get_var($queryUser);

        if ($userId) {
            return (int) $userId;
        }

        return null;
    }
}
