<?php

declare(strict_types=1);

namespace ClubCore\Domain\Exception;

/**
 * Exception for invalid phone number inputs.
 *
 * @package ClubCore\Domain\Exception
 */
class InvalidPhoneException extends \InvalidArgumentException
{
    /**
     * Invalid phone format.
     *
     * @param string $phone The phone number that failed validation.
     *
     * @return self
     */
    public static function invalidFormat(string $phone): self
    {
        return new self(
            sprintf(
                /* translators: %s: phone number */
                __('شماره موبایل "%s" معتبر نیست. شماره باید ۱۱ رقم باشد.', 'clubcore'),
                $phone
            )
        );
    }

    /**
     * Invalid prefix (not starting with 09).
     *
     * @param string $phone The phone number that failed validation.
     *
     * @return self
     */
    public static function invalidPrefix(string $phone): self
    {
        return new self(
            sprintf(
                /* translators: %s: phone number */
                __('شماره موبایل "%s" معتبر نیست. شماره باید با ۰۹ شروع شود.', 'clubcore'),
                $phone
            )
        );
    }

    /**
     * Empty phone number.
     *
     * @return self
     */
    public static function empty(): self
    {
        return new self(
            __('شماره موبایل نمی‌تواند خالی باشد.', 'clubcore')
        );
    }
}
