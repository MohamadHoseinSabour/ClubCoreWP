<?php

declare(strict_types=1);

namespace ClubCore\Domain\ValueObject;

/**
 * Membership source enumeration.
 *
 * Indicates how a member was enrolled in the customer club.
 *
 * @package ClubCore\Domain\ValueObject
 */
enum MembershipSource: string
{
    case Admin = 'admin';
    case Import = 'import';
    case WooCommerce = 'woocommerce';
    case Api = 'api';

    /**
     * Get the human-readable label for this source.
     *
     * @return string Translated label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => __('مدیر', 'clubcore'),
            self::Import => __('وارد شده', 'clubcore'),
            self::WooCommerce => __('ووکامرس', 'clubcore'),
            self::Api => __('API', 'clubcore'),
        };
    }

    /**
     * Get all sources as key-label pairs for dropdowns.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }
}
