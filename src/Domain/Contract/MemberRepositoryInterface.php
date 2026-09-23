<?php

declare(strict_types=1);

namespace ClubCore\Domain\Contract;

use ClubCore\Domain\Entity\Member;

/**
 * Member repository contract.
 *
 * Defines the persistence boundary for Member entities.
 * Implementations handle actual database operations.
 *
 * @package ClubCore\Domain\Contract
 */
interface MemberRepositoryInterface
{
    /**
     * Find a member by ID.
     *
     * @param int $id Member ID.
     *
     * @return Member|null
     */
    public function findById(int $id): ?Member;

    /**
     * Find a member by normalized phone number.
     *
     * @param string $phoneNormalized Canonical phone format (09XXXXXXXXX).
     *
     * @return Member|null
     */
    public function findByPhone(string $phoneNormalized): ?Member;

    /**
     * Find a member by WordPress user ID.
     *
     * @param int $userId WordPress user ID.
     *
     * @return Member|null
     */
    public function findByUserId(int $userId): ?Member;

    /**
     * Save a new member (insert).
     *
     * @param Member $member The member to create.
     *
     * @return int The new member ID.
     */
    public function create(Member $member): int;

    /**
     * Update an existing member.
     *
     * @param Member $member The member to update.
     *
     * @return bool True if updated successfully.
     */
    public function update(Member $member): bool;

    /**
     * Delete a member by ID.
     *
     * @param int $id Member ID.
     *
     * @return bool True if deleted successfully.
     */
    public function delete(int $id): bool;

    /**
     * Count members matching criteria.
     *
     * @param array<string, mixed> $filters Optional filters.
     *
     * @return int
     */
    public function count(array $filters = []): int;

    /**
     * List members with pagination, search, and filters.
     *
     * @param array<string, mixed> $args {
     *     @type int    $per_page  Items per page. Default 20.
     *     @type int    $page      Current page. Default 1.
     *     @type string $search    Search term (phone, name, email).
     *     @type string $orderby   Column to order by. Default 'id'.
     *     @type string $order     ASC or DESC. Default 'DESC'.
     *     @type array  $filters   Key-value filters (status, source, etc.).
     * }
     *
     * @return Member[]
     */
    public function list(array $args = []): array;

    /**
     * Check if a member exists with the given phone number.
     *
     * @param string $phoneNormalized Canonical phone format.
     *
     * @return bool
     */
    public function existsByPhone(string $phoneNormalized): bool;
}
