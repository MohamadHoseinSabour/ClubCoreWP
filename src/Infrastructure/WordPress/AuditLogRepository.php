<?php

declare(strict_types=1);

namespace ClubCore\Infrastructure\WordPress;

use ClubCore\Domain\Contract\AuditLoggerInterface;
use ClubCore\Domain\Entity\AuditEntry;

/**
 * WordPress DB implementation of Audit Logger & Repository.
 *
 * @package ClubCore\Infrastructure\WordPress
 */
class AuditLogRepository implements AuditLoggerInterface
{
    private function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'clubcore_audit_logs';
    }

    /**
     * Log an action (Implements AuditLoggerInterface).
     */
    public function log(string $action, string $objectType = '', int $objectId = 0, string $result = 'success', string $context = ''): void
    {
        global $wpdb;

        $entry = new AuditEntry($action, $objectType, $objectId, $result, $context);
        $data = $entry->toArray();

        $wpdb->insert(
            $this->getTableName(),
            $data,
            ['%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s']
        );
    }

    /**
     * Count audit log entries matching filters.
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

        if (!empty($filters['action'])) {
            $where[] = 'action = %s';
            $params[] = $filters['action'];
        }
        if (!empty($filters['object_type'])) {
            $where[] = 'object_type = %s';
            $params[] = $filters['object_type'];
        }
        if (!empty($filters['actor_user_id'])) {
            $where[] = 'actor_user_id = %d';
            $params[] = (int) $filters['actor_user_id'];
        }
        if (!empty($filters['result'])) {
            $where[] = 'result = %s';
            $params[] = $filters['result'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= %s';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'created_at <= %s';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$whereClause}";

        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, ...$params);
        }

        return (int) $wpdb->get_var($sql);
    }

    /**
     * List audit log entries with pagination and filters.
     *
     * @param array<string, mixed> $args
     * @return AuditEntry[]
     */
    public function list(array $args = []): array
    {
        global $wpdb;
        $table = $this->getTableName();

        $perPage = max(1, (int) ($args['per_page'] ?? 20));
        $page = max(1, (int) ($args['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $orderby = in_array($args['orderby'] ?? '', ['id', 'action', 'created_at', 'result'], true) ? $args['orderby'] : 'id';
        $order = strtoupper($args['order'] ?? '') === 'ASC' ? 'ASC' : 'DESC';

        $where = ['1=1'];
        $params = [];

        $filters = $args['filters'] ?? [];
        if (!empty($filters['action'])) {
            $where[] = 'action = %s';
            $params[] = $filters['action'];
        }
        if (!empty($filters['object_type'])) {
            $where[] = 'object_type = %s';
            $params[] = $filters['object_type'];
        }
        if (!empty($filters['actor_user_id'])) {
            $where[] = 'actor_user_id = %d';
            $params[] = (int) $filters['actor_user_id'];
        }
        if (!empty($filters['result'])) {
            $where[] = 'result = %s';
            $params[] = $filters['result'];
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT * FROM {$table} WHERE {$whereClause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $params[] = $perPage;
        $params[] = $offset;

        $rows = $wpdb->get_results($wpdb->prepare($sql, ...$params));
        if (empty($rows)) {
            return [];
        }

        return array_map(fn($row) => AuditEntry::fromRow($row), $rows);
    }

    /**
     * Purge logs older than X days.
     *
     * @param int $days
     * @return int Number of deleted rows.
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
