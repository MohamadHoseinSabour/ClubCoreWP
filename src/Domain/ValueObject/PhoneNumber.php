<?php

declare(strict_types=1);

namespace ClubCore\Domain\ValueObject;

use ClubCore\Domain\Exception\InvalidPhoneException;

/**
 * Phone Number value object.
 *
 * Handles normalization, validation, and conversion of Iranian mobile numbers.
 * Supports Persian digits (۰-۹), Arabic digits (٠-٩), and various country code formats.
 *
 * Canonical storage format: 09XXXXXXXXX (11 digits, starting with 09)
 *
 * @package ClubCore\Domain\ValueObject
 */
final class PhoneNumber
{
    /**
     * Persian digit map (۰-۹ → 0-9).
     */
    private const PERSIAN_DIGITS = [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
    ];

    /**
     * Arabic digit map (٠-٩ → 0-9).
     */
    private const ARABIC_DIGITS = [
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ];

    /**
     * Valid Iran mobile prefixes (after 09).
     */
    private const VALID_PREFIXES = [
        '10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
        '20', '21', '22',
        '30', '31', '32', '33', '34', '35', '36', '37', '38', '39',
        '90', '91', '92', '93', '94',
        '99',
        '01', '02', '03', '04', '05',
    ];

    /**
     * Normalized phone number in canonical format (09XXXXXXXXX).
     */
    private readonly string $normalized;

    /**
     * Original display format (as entered by user, after basic cleanup).
     */
    private readonly string $display;

    /**
     * Create a PhoneNumber from raw input.
     *
     * @param string $raw Raw phone number input.
     *
     * @throws InvalidPhoneException If the phone number is invalid.
     */
    public function __construct(string $raw)
    {
        $cleaned = $this->clean($raw);
        $this->display = $cleaned;
        $this->normalized = $this->normalize($cleaned);
        $this->validate($this->normalized);
    }

    /**
     * Create a PhoneNumber from raw input.
     *
     * @param string $raw Raw phone number input.
     *
     * @return self
     *
     * @throws InvalidPhoneException If the phone number is invalid.
     */
    public static function fromString(string $raw): self
    {
        return new self($raw);
    }

    /**
     * Try to create a PhoneNumber, returning null if invalid.
     *
     * @param string $raw Raw phone number input.
     *
     * @return self|null
     */
    public static function tryFromString(string $raw): ?self
    {
        try {
            return new self($raw);
        } catch (InvalidPhoneException) {
            return null;
        }
    }

    /**
     * Get the canonical normalized form (09XXXXXXXXX).
     *
     * @return string
     */
    public function getNormalized(): string
    {
        return $this->normalized;
    }

    /**
     * Get the display form (cleaned version of original input).
     *
     * @return string
     */
    public function getDisplay(): string
    {
        return $this->display;
    }

    /**
     * Get the international format (+98XXXXXXXXX).
     *
     * @return string
     */
    public function getInternational(): string
    {
        return '+98' . substr($this->normalized, 1);
    }

    /**
     * Compare two phone numbers by their canonical form.
     *
     * @param self $other Other phone number.
     *
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->normalized === $other->normalized;
    }

    /**
     * Get the canonical string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->normalized;
    }

    /**
     * Clean raw input: convert digits, remove separators and whitespace.
     *
     * @param string $raw Raw input.
     *
     * @return string Cleaned string with only Latin digits and + prefix if present.
     */
    private function clean(string $raw): string
    {
        $raw = trim($raw);

        // Convert Persian digits to Latin.
        $raw = strtr($raw, self::PERSIAN_DIGITS);

        // Convert Arabic digits to Latin.
        $raw = strtr($raw, self::ARABIC_DIGITS);

        // Remove spaces, dashes, parentheses, dots.
        $raw = preg_replace('/[\s\-\(\)\.\x{200C}\x{200F}\x{200E}]+/u', '', $raw);

        return $raw;
    }

    /**
     * Normalize to canonical format (09XXXXXXXXX).
     *
     * Handles:
     * - 09XXXXXXXXX → 09XXXXXXXXX (already canonical)
     * - +989XXXXXXXXX → 09XXXXXXXXX
     * - 989XXXXXXXXX → 09XXXXXXXXX
     * - 00989XXXXXXXXX → 09XXXXXXXXX
     * - 9XXXXXXXXX → 09XXXXXXXXX
     *
     * @param string $cleaned Cleaned phone number.
     *
     * @return string Canonical format.
     *
     * @throws InvalidPhoneException If format is unrecognizable.
     */
    private function normalize(string $cleaned): string
    {
        // Remove leading + if present.
        if (str_starts_with($cleaned, '+')) {
            $cleaned = substr($cleaned, 1);
        }

        // Remove leading 0098.
        if (str_starts_with($cleaned, '0098')) {
            $cleaned = '0' . substr($cleaned, 4);
        }

        // Remove leading 98 (but not 09).
        if (str_starts_with($cleaned, '98') && strlen($cleaned) === 12) {
            $cleaned = '0' . substr($cleaned, 2);
        }

        // If starts with 9 and is 10 digits, add leading 0.
        if (str_starts_with($cleaned, '9') && strlen($cleaned) === 10) {
            $cleaned = '0' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Validate the normalized phone number.
     *
     * @param string $normalized Normalized phone number.
     *
     * @throws InvalidPhoneException If validation fails.
     */
    private function validate(string $normalized): void
    {
        // Must be exactly 11 digits.
        if (!preg_match('/^\d{11}$/', $normalized)) {
            throw InvalidPhoneException::invalidFormat($this->display);
        }

        // Must start with 09.
        if (!str_starts_with($normalized, '09')) {
            throw InvalidPhoneException::invalidPrefix($this->display);
        }
    }
}
