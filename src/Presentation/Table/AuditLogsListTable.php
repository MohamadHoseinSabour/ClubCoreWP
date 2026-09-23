<?php

declare(strict_types=1);

namespace ClubCore\Presentation\Table;

use ClubCore\Infrastructure\WordPress\AuditLogRepository;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List Table for System Audit Logs.
 *
 * @package ClubCore\Presentation\Table
 */
class AuditLogsListTable extends \WP_List_Table
{
    private AuditLogRepository $repository;

    public function __construct()
    {
        parent::__construct([
            'singular' => 'audit_log',
            'plural'   => 'audit_logs',
            'ajax'     => false,
        ]);
        $this->repository = new AuditLogRepository();
    }

    public function get_columns(): array
    {
        return [
            'cb'          => '<input type="checkbox" />',
            'created_at'  => __('زمان', 'clubcore'),
            'actor'       => __('کاربر عامل', 'clubcore'),
            'action'      => __('عملیات', 'clubcore'),
            'object_type' => __('نوع رکورد', 'clubcore'),
            'result'      => __('نتیجه', 'clubcore'),
            'context'     => __('جزئیات', 'clubcore'),
            'ip_hash'     => __('شناسه IP (هش)', 'clubcore'),
        ];
    }

    protected function get_sortable_columns(): array
    {
        return [
            'created_at' => ['created_at', true],
            'action'     => ['action', false],
            'result'     => ['result', false],
        ];
    }

    public function prepare_items(): void
    {
        $perPage = 20;
        $currentPage = $this->get_pagenum();

        $filters = [];
        if (!empty($_REQUEST['action_type'])) {
            $filters['action'] = sanitize_text_field(wp_unslash($_REQUEST['action_type']));
        }
        if (!empty($_REQUEST['result'])) {
            $filters['result'] = sanitize_text_field(wp_unslash($_REQUEST['result']));
        }

        $orderby = !empty($_REQUEST['orderby']) ? sanitize_text_field(wp_unslash($_REQUEST['orderby'])) : 'created_at';
        $order = !empty($_REQUEST['order']) ? sanitize_text_field(wp_unslash($_REQUEST['order'])) : 'DESC';

        $totalItems = $this->repository->count($filters);

        $this->items = $this->repository->list([
            'per_page' => $perPage,
            'page'     => $currentPage,
            'orderby'  => $orderby,
            'order'    => $order,
            'filters'  => $filters,
        ]);

        $this->set_pagination_args([
            'total_items' => $totalItems,
            'per_page'    => $perPage,
            'total_pages' => ceil($totalItems / $perPage),
        ]);
    }

    protected function column_cb($item): string
    {
        return sprintf('<input type="checkbox" name="log_id[]" value="%d" />', $item->getId());
    }

    protected function column_default($item, $column_name): string
    {
        return match ($column_name) {
            'created_at'  => esc_html($item->getCreatedAt()),
            'actor'       => $this->renderActor($item->getActorUserId()),
            'action'      => '<strong>' . esc_html($item->getActionLabel()) . '</strong>',
            'object_type' => esc_html($item->getObjectType() ?: '-'),
            'result'      => $this->renderResultBadge($item->getResult()),
            'context'     => $this->renderContext($item->getContext()),
            'ip_hash'     => '<code style="font-size:11px;">' . esc_html(substr($item->getIpHash(), 0, 10)) . '...</code>',
            default       => '',
        };
    }

    private function renderActor(int $userId): string
    {
        if ($userId === 0) {
            return '<em>' . esc_html__('سیستم', 'clubcore') . '</em>';
        }
        $user = get_userdata($userId);
        return $user ? esc_html($user->display_name) : sprintf('#%d', $userId);
    }

    private function renderResultBadge(string $result): string
    {
        $class = match ($result) {
            'success' => 'clubcore-badge-success',
            'failure' => 'clubcore-badge-error',
            default   => 'clubcore-badge-neutral',
        };
        $label = match ($result) {
            'success' => __('موفق', 'clubcore'),
            'failure' => __('ناموفق', 'clubcore'),
            default   => $result,
        };
        return sprintf('<span class="clubcore-badge %s">%s</span>', esc_attr($class), esc_html($label));
    }

    private function renderContext(?string $context): string
    {
        if (empty($context)) {
            return '-';
        }
        $decoded = json_decode($context, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $formatted = [];
            foreach ($decoded as $k => $v) {
                if (is_scalar($v)) {
                    $formatted[] = esc_html($k) . ': ' . esc_html((string)$v);
                }
            }
            return implode(' | ', $formatted);
        }
        return esc_html($context);
    }

    protected function extra_tablenav($which): void
    {
        if ($which !== 'top') {
            return;
        }

        $currentResult = sanitize_text_field(wp_unslash($_REQUEST['result'] ?? ''));
        ?>
        <div class="alignleft actions">
            <select name="result">
                <option value=""><?php esc_html_e('همه نتایج', 'clubcore'); ?></option>
                <option value="success" <?php selected($currentResult, 'success'); ?>><?php esc_html_e('موفق', 'clubcore'); ?></option>
                <option value="failure" <?php selected($currentResult, 'failure'); ?>><?php esc_html_e('ناموفق', 'clubcore'); ?></option>
            </select>

            <?php submit_button(__('فیلتر', 'clubcore'), '', 'filter_action', false); ?>
        </div>
        <?php
    }
}
