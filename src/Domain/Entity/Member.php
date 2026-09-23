<?php

declare(strict_types=1);

namespace ClubCore\Domain\Entity;

use ClubCore\Domain\ValueObject\MembershipSource;
use ClubCore\Domain\ValueObject\PhoneNumber;

/**
 * Member entity representing a Customer Club member.
 *
 * A Member is the core business entity of the Customer Club.
 * It is separate from WordPress User (identity) — a Member references a User
 * but they are not the same concept.
 *
 * @package ClubCore\Domain\Entity
 */
class Member
{
    private int $id = 0;
    private int $userId = 0;
    private string $phoneNormalized;
    private string $phoneDisplay;
    private string $firstName = '';
    private string $lastName = '';
    private string $email = '';
    private string $membershipStatus = 'active';
    private MembershipSource $membershipSource;
    private string $membershipCreatedAt;
    private string $membershipCreatedAtGmt;
    private int $createdBy = 0;
    private string $updatedAt;
    private string $updatedAtGmt;
    private string $lastSmsStatus = '';
    private ?string $lastSmsSentAt = null;
    private bool $woocommerceLinked = false;

    /**
     * Create a new Member instance.
     *
     * @param PhoneNumber      $phone  The member's phone number.
     * @param MembershipSource $source How the member was enrolled.
     */
    public function __construct(PhoneNumber $phone, MembershipSource $source = MembershipSource::Admin)
    {
        $this->phoneNormalized = $phone->getNormalized();
        $this->phoneDisplay = $phone->getDisplay();
        $this->membershipSource = $source;
        $now = current_time('mysql');
        $nowGmt = current_time('mysql', true);
        $this->membershipCreatedAt = $now;
        $this->membershipCreatedAtGmt = $nowGmt;
        $this->updatedAt = $now;
        $this->updatedAtGmt = $nowGmt;
    }

    /**
     * Hydrate a Member from database row data.
     *
     * @param object|array $data Database row (object or associative array).
     *
     * @return self
     */
    public static function fromRow(object|array $data): self
    {
        $data = (object) $data;

        $phone = PhoneNumber::tryFromString($data->phone_normalized ?? '');
        $member = new self(
            $phone ?? PhoneNumber::fromString('09000000000'), // fallback, overridden below
            MembershipSource::tryFrom($data->membership_source ?? 'admin') ?? MembershipSource::Admin,
        );

        $member->id = (int) ($data->id ?? 0);
        $member->userId = (int) ($data->user_id ?? 0);
        $member->phoneNormalized = $data->phone_normalized ?? '';
        $member->phoneDisplay = $data->phone_display ?? '';
        $member->firstName = $data->first_name ?? '';
        $member->lastName = $data->last_name ?? '';
        $member->email = $data->email ?? '';
        $member->membershipStatus = $data->membership_status ?? 'active';
        $member->membershipCreatedAt = $data->membership_created_at ?? '';
        $member->membershipCreatedAtGmt = $data->membership_created_at_gmt ?? '';
        $member->createdBy = (int) ($data->created_by ?? 0);
        $member->updatedAt = $data->updated_at ?? '';
        $member->updatedAtGmt = $data->updated_at_gmt ?? '';
        $member->lastSmsStatus = $data->last_sms_status ?? '';
        $member->lastSmsSentAt = $data->last_sms_sent_at ?? null;
        $member->woocommerceLinked = (bool) ($data->woocommerce_linked ?? false);

        return $member;
    }

    /**
     * Convert Member to array for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'phone_normalized' => $this->phoneNormalized,
            'phone_display' => $this->phoneDisplay,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'membership_status' => $this->membershipStatus,
            'membership_source' => $this->membershipSource->value,
            'membership_created_at' => $this->membershipCreatedAt,
            'membership_created_at_gmt' => $this->membershipCreatedAtGmt,
            'created_by' => $this->createdBy,
            'updated_at' => $this->updatedAt,
            'updated_at_gmt' => $this->updatedAtGmt,
            'last_sms_status' => $this->lastSmsStatus,
            'last_sms_sent_at' => $this->lastSmsSentAt,
            'woocommerce_linked' => $this->woocommerceLinked ? 1 : 0,
        ];
    }

    // ─── Getters ───────────────────────────────────────────

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getPhoneNormalized(): string
    {
        return $this->phoneNormalized;
    }

    public function getPhoneDisplay(): string
    {
        return $this->phoneDisplay;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getMembershipStatus(): string
    {
        return $this->membershipStatus;
    }

    public function getMembershipSource(): MembershipSource
    {
        return $this->membershipSource;
    }

    public function getMembershipCreatedAt(): string
    {
        return $this->membershipCreatedAt;
    }

    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    public function getLastSmsStatus(): string
    {
        return $this->lastSmsStatus;
    }

    public function getLastSmsSentAt(): ?string
    {
        return $this->lastSmsSentAt;
    }

    public function isWoocommerceLinked(): bool
    {
        return $this->woocommerceLinked;
    }

    public function isActive(): bool
    {
        return $this->membershipStatus === 'active';
    }

    // ─── Setters ───────────────────────────────────────────

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function setMembershipStatus(string $status): self
    {
        $this->membershipStatus = $status;
        return $this;
    }

    public function setCreatedBy(int $userId): self
    {
        $this->createdBy = $userId;
        return $this;
    }

    public function setLastSmsStatus(string $status): self
    {
        $this->lastSmsStatus = $status;
        return $this;
    }

    public function setLastSmsSentAt(?string $dateTime): self
    {
        $this->lastSmsSentAt = $dateTime;
        return $this;
    }

    public function setWoocommerceLinked(bool $linked): self
    {
        $this->woocommerceLinked = $linked;
        return $this;
    }

    /**
     * Mark the updated timestamps.
     */
    public function touchUpdated(): self
    {
        $this->updatedAt = current_time('mysql');
        $this->updatedAtGmt = current_time('mysql', true);
        return $this;
    }
}
