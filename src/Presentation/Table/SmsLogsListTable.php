<?php

declare(strict_types=1);

namespace ClubCore\Presentation\Table;

use ClubCore\Infrastructure\WordPress\SmsLogRepository;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List Table for SMS History.
 *
 * @package ClubCore\Presentation\Table
 */
class SmsLogsListTable extends \WP_List_Table
{
    private SmsLogRepository $repository;

    public function __construct()
    {
        parent::__construct([
            'singular' => 'sms_log',
            'plural'   => 'sms_logs',
            'ajax'     => false,
        ]);
        $this->repository = new SmsLogRepository();
    }

    public function get_columns(): array
    {
        return [
            'cb'          => '<input type="checkbox" />',
            'created_at'  => __('تاریخ', 'clubcore'),
            'phone'       => __('شماره گیرنده', 'clubcore'),
            'request_type'=> __('نوع پیامک', 'clubcore'),
            'pattern_id'  => __('کد الگو', 'clubcore'),
            'provider'    => __('سرویس‌دهنده', 'clubcore'),
            'status'      => __('وضعیت', 'clubcore'),
            'reference'   => __('کد رهگیری', 'clubcore'),
            'error'       => __('پیام خطا', 'clubcore'),
            'actions'     => __('عملیات', 'clubcore'),
        ];
    }

    protected function get_sortable_columns(): array
    {
        return [
            'created_at'   => ['created_at', true],
            'request_type' => ['request_type', false],
            'status'       => ['status', false],
        ];
    }

    public function prepare_items(): void
    {
        $perPage = 20;
        $currentPage = $this->get_pagenum();

        $filters = [];
        if (!empty($_REQUEST['s'])) {
            $filters['phone'] = sanitize_text_field(wp_unslash($_REQUEST['s']));
        }
        if (!empty($_REQUEST['status'])) {
            $filters['status'] = sanitize_text_field(wp_unslash($_REQUEST['status']));
        }
        if (!empty($_REQUEST['request_type'])) {
            $filters['request_type'] = sanitize_text_field(wp_unslash($_REQUEST['request_type']));
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
            'created_at'   => esc_html($item->getCreatedAt()),
            'phone'        => '<span dir="ltr">' . esc_html($item->getPhone()) . '</span>',
            'request_type' => esc_html($item->getTypeLabel()),
            'pattern_id'   => esc_html($item->getPatternId() ?: '-'),
            'provider'     => esc_html($item->getProvider()),
            'status'       => $this->renderStatusBadge($item),
            'reference'    => '<span dir="ltr">' . esc_html($item->getProviderReference() ?: '-') . '</span>',
            'error'        => esc_html($item->getProviderErrorMessage() ?: '-'),
            'actions'      => $this->renderActions($item),
            default        => '',
        };
    }

    private function renderStatusBadge($item): string
    {
        $status = $item->getStatus();
        $class = match ($status) {
            'sent' => 'clubcore-badge-success',
            'failed' => 'clubcore-badge-error',
            'queued' => 'clubcore-badge-warning',
            default => 'clubcore-badge-neutral',
        };
        return sprintf('<span class="clubcore-badge %s">%s</span>', esc_attr($class), esc_html($item->getStatusLabel()));
    }

    private function renderActions($item): string
    {
        if ($item->getMemberId() > 0 && current_user_can('clubcore_send_sms')) {
            $nonce = wp_create_nonce('clubcore_resend_sms');
            return sprintf(
                '<button type="button" class="button button-small clubcore-resend-btn" data-member-id="%d" data-nonce="%s">%s</button>',
                $item->getMemberId(),
                esc_attr($nonce),
                esc_html__('ارسال مجدد', 'clubcore')
            );
        }
        return '-';
    }

    protected function extra_tablenav($which): void
    {
        if ($which !== 'top') {
            return;
        }

        $currentStatus = sanitize_text_field(wp_unslash($_REQUEST['status'] ?? ''));
        $currentType = sanitize_text_field(wp_unslash($_REQUEST['request_type'] ?? ''));
        ?>
        <div class="alignleft actions">
            <select name="status">
                <option value=""><?php esc_html_e('همه وضعیت‌ها', 'clubcore'); ?></option>
                <option value="sent" <?php selected($currentStatus, 'sent'); ?>><?php esc_html_e('ارسال شده', 'clubcore'); ?></option>
                <option value="failed" <?php selected($currentStatus, 'failed'); ?>><?php esc_html_e('ناموفق', 'clubcore'); ?></option>
                <option value="pending" <?php selected($currentStatus, 'pending'); ?>><?php esc_html_e('در انتظار', 'clubcore'); ?></option>
            </select>

            <select name="request_type">
                <option value=""><?php esc_html_e('همه نوع‌ها', 'clubcore'); ?></option>
                <option value="welcome" <?php selected($currentType, 'welcome'); ?>><?php esc_html_e('خوش‌آمدگویی', 'clubcore'); ?></option>
                <option value="resend" <?php selected($currentType, 'resend'); ?>><?php esc_html_e('ارسال مجدد', 'clubcore'); ?></option>
                <option value="test" <?php selected($currentType, 'test'); ?>><?php esc_html_e('آزمایشی', 'clubcore'); ?></option>
                <option value="import" <?php selected($currentType, 'import'); ?>><?php esc_html_e('وارد کردن', 'clubcore'); ?></option>
                <option value="woocommerce" <?php selected($currentType, 'woocommerce'); ?>><?php esc_html_e('ووکامرس', 'clubcore'); ?></option>
            </select>

            <?php submit_button(__('فیلتر', 'clubcore'), '', 'filter_action', false); ?>
        </div>
        <?php
    }
}
