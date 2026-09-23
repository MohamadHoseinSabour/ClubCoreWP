<?php

declare(strict_types=1);

namespace ClubCore\Infrastructure\WordPress;

use ClubCore\Domain\Contract\SmsLogRepositoryInterface;
use ClubCore\Domain\Entity\SmsLog;

/**
 * WordPress DB implementation of SmsLogRepositoryInterface.
 *
 * @package ClubCore\Infrastructure\WordPress
 */
class SmsLogRepository implements SmsLogRepositoryInterface
{
    private function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'clubcore_sms_logs';
    }

    /**
     * Insert a new SMS log.
     *
     * @param array<string, mixed> $data
     * @return int Inserted ID.
     */
    public function create(array $data): int
    {
        global $wpdb;
        $table = $this->getTableName();

        $wpdb->insert(
            $table,
            [
                'member_id' => (int) ($data['member_id'] ?? 0),
                'user_id' => (int) ($data['user_id'] ?? 0),
                'phone' => sanitize_text_field($data['phone'] ?? ''),
                'pattern_id' => sanitize_text_field($data['pattern_id'] ?? ''),
                'provider' => sanitize_text_field($data['provider'] ?? ''),
                'request_type' => sanitize_text_field($data['request_type'] ?? 'welcome'),
                'status' => sanitize_text_field($data['status'] ?? 'pending'),
                'provider_reference' => sanitize_text_field($data['provider_reference'] ?? ''),
                'provider_error_code' => sanitize_text_field($data['provider_error_code'] ?? ''),
                'provider_error_message' => isset($data['provider_error_message']) ? sanitize_textarea_field((string)$data['provider_error_message']) : null,
                'created_at' => current_time('mysql', true),
                'created_by' => get_current_user_id(),
            ],
            ['%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d']
        );

        return (int) $wpdb->insert_id;
    }

    /**
     * Find SMS logs for a specific member.
     *
     * @param int $memberId
     * @param array<string, mixed> $args
     * @return SmsLog[]
     */
    public function findByMember(int $memberId, array $args = []): array
    {
        $args['filters']['member_id'] = $memberId;
        return $this->list($args);
    }

    /**
     * Count SMS logs matching criteria.
     *
     * @param array<string, mixed> $filters
     * @return int
     */
    public function count(array $filters = []): int
    {
        global $wpdb;
        $table = $this->getTableName();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['member_id'])) {
            $where[] = 'member_id = %d';
            $params[] = (int) $filters['member_id'];
        }
        if (!empty($filters['phone'])) {
            $where[] = 'phone LIKE %s';
            $params[] = '%' . $wpdb->esc_like($filters['phone']) . '%';
        }
        if (!empty($filters['status'])) {
            $where[] = 'status = %s';
            $params[] = $filters['status'];
        }
        if (!empty($filters['request_type'])) {
            $where[] = 'request_type = %s';
            $params[] = $filters['request_type'];
        }
        if (!empty($filters['provider'])) {
            $where[] = 'provider = %s';
            $params[] = $filters['provider'];
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$whereClause}";

        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, ...$params);
        }

        return (int) $wpdb->get_var($sql);
    }

    /**
     * List SMS logs with search, filter, and pagination.
     *
     * @param array<string, mixed> $args
     * @return SmsLog[]
     */
    public function list(array $args = []): array
    {
        global $wpdb;
        $table = $this->getTableName();

        $perPage = max(1, (int) ($args['per_page'] ?? 20));
        $page = max(1, (int) ($args['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $orderby = in_array($args['orderby'] ?? '', ['id', 'created_at', 'status', 'request_type'], true) ? $args['orderby'] : 'id';
        $order = strtoupper($args['order'] ?? '') === 'ASC' ? 'ASC' : 'DESC';

        $where = ['1=1'];
        $params = [];

        $filters = $args['filters'] ?? [];
        if (!empty($filters['member_id'])) {
            $where[] = 'member_id = %d';
            $params[] = (int) $filters['member_id'];
        }
        if (!empty($filters['phone'])) {
            $where[] = 'phone LIKE %s';
            $params[] = '%' . $wpdb->esc_like($filters['phone']) . '%';
        }
        if (!empty($filters['status'])) {
            $where[] = 'status = %s';
            $params[] = $filters['status'];
        }
        if (!empty($filters['request_type'])) {
            $where[] = 'request_type = %s';
            $params[] = $filters['request_type'];
        }
        if (!empty($filters['provider'])) {
            $where[] = 'provider = %s';
            $params[] = $filters['provider'];
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT * FROM {$table} WHERE {$whereClause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $params[] = $perPage;
        $params[] = $offset;

        $rows = $wpdb->get_results($wpdb->prepare($sql, ...$params));
        if (empty($rows)) {
            return [];
        }

        return array_map(fn($row) => SmsLog::fromRow($row), $rows);
    }

    /**
     * Purge SMS logs older than X days.
     */
    public function purgeOlderThan(int $days): int
    {
        global $wpdb;
        $table = $this->getTableName();
        $date = gmdate('Y-m-d H:i:s', time() - ($days * DAY_IN_SECONDS));

        return (int) $wpdb->query(
            $wpdb->prepare("DELETE FROM {$table} WHERE created_at < %s", $date)
        );
    }
}
