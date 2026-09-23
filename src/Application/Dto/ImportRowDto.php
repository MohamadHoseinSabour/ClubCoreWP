<?php

declare(strict_types=1);

namespace ClubCore\Application\Dto;

use ClubCore\Domain\ValueObject\PhoneNumber;

/**
 * Data Transfer Object representing a single parsed import row.
 *
 * @package ClubCore\Application\Dto
 */
class ImportRowDto
{
    public readonly int $rowNumber;
    public readonly ?PhoneNumber $phone;
    public readonly string $rawPhone;
    public readonly string $firstName;
    public readonly string $lastName;
    public readonly string $email;
    public readonly array $rawRow;
    public readonly bool $isValid;
    public readonly ?string $errorMessage;

    public function __construct(
        int $rowNumber,
        string $rawPhone,
        string $firstName = '',
        string $lastName = '',
        string $email = '',
        array $rawRow = []
    ) {
        $this->rowNumber = $rowNumber;
        $this->rawPhone = trim($rawPhone);
        $this->firstName = trim($firstName);
        $this->lastName = trim($lastName);
        $this->email = trim($email);
        $this->rawRow = $rawRow;

        $phoneObj = PhoneNumber::tryFromString($this->rawPhone);
        if ($phoneObj === null) {
            $this->phone = null;
            $this->isValid = false;
            $this->errorMessage = empty($this->rawPhone)
                ? __('شماره موبایل خالی است.', 'clubcore')
                : __('شماره موبایل نامعتبر است.', 'clubcore');
        } elseif (!empty($this->email) && !is_email($this->email)) {
            $this->phone = $phoneObj;
            $this->isValid = false;
            $this->errorMessage = __('فرمت ایمیل نامعتبر است.', 'clubcore');
        } else {
            $this->phone = $phoneObj;
            $this->isValid = true;
            $this->errorMessage = null;
        }
    }
}
