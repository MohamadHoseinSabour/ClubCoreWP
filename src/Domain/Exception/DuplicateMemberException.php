<?php

declare(strict_types=1);

namespace ClubCore\Domain\Exception;

/**
 * Exception for duplicate member detection.
 *
 * @package ClubCore\Domain\Exception
 */
class DuplicateMemberException extends \RuntimeException
{
    /**
     * Member already exists with this phone number.
     *
     * @param string $phone  The phone number.
     * @param int    $memberId The existing member ID.
     *
     * @return self
     */
    public static function phoneExists(string $phone, int $memberId): self
    {
        return new self(
            sprintf(
                /* translators: 1: phone number, 2: member ID */
                __('مشتری با شماره "%1$s" قبلاً در باشگاه ثبت شده است. (شناسه عضو: %2$d)', 'clubcore'),
                $phone,
                $memberId
            ),
            0,
            null
        );
    }

    /**
     * WordPress user already exists with this phone.
     *
     * @param string $phone  The phone number.
     * @param int    $userId The existing user ID.
     *
     * @return self
     */
    public static function userExists(string $phone, int $userId): self
    {
        return new self(
            sprintf(
                /* translators: 1: phone number, 2: user ID */
                __('کاربر وردپرس با شماره "%1$s" قبلاً وجود دارد. (شناسه کاربر: %2$d)', 'clubcore'),
                $phone,
                $userId
            )
        );
    }
}
