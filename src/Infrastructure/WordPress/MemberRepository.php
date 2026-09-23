<?php

declare(strict_types=1);

namespace ClubCore\Infrastructure\WordPress;

use ClubCore\Domain\Contract\MemberRepositoryInterface;
use ClubCore\Domain\Entity\Member;
use wpdb;

/**
 * WordPress wpdb implementation of the MemberRepositoryInterface.
 *
 * @package ClubCore\Infrastructure\WordPress
 */
class MemberRepository implements MemberRepositoryInterface
{
    /**
     * Get the global wpdb object.
     *
     * @return wpdb
     */
    private function db(): wpdb
    {
        global $wpdb;
        return $wpdb;
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    private function getTableName(): string
    {
        return $this->db()->prefix . 'clubcore_members';
    }

    public function findById(int $id): ?Member
    {
        $table = $this->getTableName();
        $query = $this->db()->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id);
        $row = $this->db()->get_row($query);

        if (!$row) {
            return null;
        }

        return Member::fromRow($row);
    }

    public function findByPhone(string $phoneNormalized): ?Member
    {
        $table = $this->getTableName();
        $query = $this->db()->prepare("SELECT * FROM {$table} WHERE phone_normalized = %s LIMIT 1", $phoneNormalized);
        $row = $this->db()->get_row($query);

        if (!$row) {
            return null;
        }

        return Member::fromRow($row);
    }

    public function findByUserId(int $userId): ?Member
    {
        $table = $this->getTableName();
        $query = $this->db()->prepare("SELECT * FROM {$table} WHERE user_id = %d LIMIT 1", $userId);
        $row = $this->db()->get_row($query);

        if (!$row) {
            return null;
        }

        return Member::fromRow($row);
    }

    public function create(Member $member): int
    {
        $table = $this->getTableName();
        $data = $member->toArray();

        $this->db()->insert(
            $table,
            $data,
            [
                '%d', // user_id
                '%s', // phone_normalized
                '%s', // phone_display
                '%s', // first_name
                '%s', // last_name
                '%s', // email
                '%s', // membership_status
                '%s', // membership_source
                '%s', // membership_created_at
                '%s', // membership_created_at_gmt
                '%d', // created_by
                '%s', // updated_at
                '%s', // updated_at_gmt
                '%s', // last_sms_status
                '%s', // last_sms_sent_at
                '%d', // woocommerce_linked
            ]
        );

        return (int) $this->db()->insert_id;
    }

    public function update(Member $member): bool
    {
        $table = $this->getTableName();
        $data = $member->toArray();
        $id = $member->getId();

        $result = $this->db()->update(
            $table,
            $data,
            ['id' => $id],
            [
                '%d', // user_id
                '%s', // phone_normalized
                '%s', // phone_display
                '%s', // first_name
                '%s', // last_name
                '%s', // email
                '%s', // membership_status
                '%s', // membership_source
                '%s', // membership_created_at
                '%s', // membership_created_at_gmt
                '%d', // created_by
                '%s', // updated_at
                '%s', // updated_at_gmt
                '%s', // last_sms_status
                '%s', // last_sms_sent_at
                '%d', // woocommerce_linked
            ],
            ['%d']
        );

        return $result !== false;
    }

    public function delete(int $id): bool
    {
        $table = $this->getTableName();
        $result = $this->db()->delete($table, ['id' => $id], ['%d']);

        return $result !== false;
    }

    public function count(array $filters = []): int
    {
        $table = $this->getTableName();
        $where = '1=1';
        $params = [];

        if (!empty($filters['membership_status'])) {
            $where .= ' AND membership_status = %s';
            $params[] = $filters['membership_status'];
        }
        if (!empty($filters['membership_source'])) {
            $where .= ' AND membership_source = %s';
            $params[] = $filters['membership_source'];
        }
        if (isset($filters['woocommerce_linked'])) {
            $where .= ' AND woocommerce_linked = %d';
            $params[] = $filters['woocommerce_linked'] ? 1 : 0;
        }

        $query = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
        
        if (!empty($params)) {
            $query = $this->db()->prepare($query, ...$params);
        }

        return (int) $this->db()->get_var($query);
    }

    public function list(array $args = []): array
    {
        $table = $this->getTableName();
        
        $perPage = isset($args['per_page']) ? (int) $args['per_page'] : 20;
        $page = isset($args['page']) ? (int) $args['page'] : 1;
        $search = $args['search'] ?? '';
        $orderby = $args['orderby'] ?? 'id';
        $order = strtoupper($args['order'] ?? 'DESC');
        $filters = $args['filters'] ?? [];
        
        $allowedOrderby = ['id', 'user_id', 'phone_normalized', 'first_name', 'last_name', 'email', 'membership_status', 'membership_source', 'membership_created_at', 'updated_at'];
        if (!in_array($orderby, $allowedOrderby, true)) {
            $orderby = 'id';
        }
        
        if ($order !== 'ASC' && $order !== 'DESC') {
            $order = 'DESC';
        }

        $where = '1=1';
        $params = [];

        if ($search !== '') {
            $searchLike = '%' . $this->db()->esc_like($search) . '%';
            $where .= ' AND (phone_normalized LIKE %s OR first_name LIKE %s OR last_name LIKE %s OR email LIKE %s)';
            $params[] = $searchLike;
            $params[] = $searchLike;
            $params[] = $searchLike;
            $params[] = $searchLike;
        }

        if (!empty($filters['membership_status'])) {
            $where .= ' AND membership_status = %s';
            $params[] = $filters['membership_status'];
        }
        if (!empty($filters['membership_source'])) {
            $where .= ' AND membership_source = %s';
            $params[] = $filters['membership_source'];
        }
        if (isset($filters['woocommerce_linked'])) {
            $where .= ' AND woocommerce_linked = %d';
            $params[] = $filters['woocommerce_linked'] ? 1 : 0;
        }
        if (!empty($filters['last_sms_status'])) {
            $where .= ' AND last_sms_status = %s';
            $params[] = $filters['last_sms_status'];
        }

        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM {$table} WHERE {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $params[] = $perPage;
        $params[] = $offset;

        $query = $this->db()->prepare($sql, ...$params);
        
        $results = $this->db()->get_results($query);

        if (!is_array($results)) {
            return [];
        }

        $members = [];
        foreach ($results as $row) {
            $members[] = Member::fromRow($row);
        }

        return $members;
    }

    public function existsByPhone(string $phoneNormalized): bool
    {
        $table = $this->getTableName();
        $query = $this->db()->prepare("SELECT 1 FROM {$table} WHERE phone_normalized = %s LIMIT 1", $phoneNormalized);
        
        return (bool) $this->db()->get_var($query);
    }
}
