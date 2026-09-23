<?php

declare(strict_types=1);

namespace ClubCore\Presentation\Table;

use ClubCore\Domain\Contract\MemberRepositoryInterface;
use ClubCore\Infrastructure\WordPress\MemberRepository;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Members List Table
 * 
 * Extends WP_List_Table to display club members.
 *
 * @package ClubCore\Presentation\Table
 */
class MembersListTable extends \WP_List_Table
{
    private MemberRepositoryInterface $member_repository;

    public function __construct()
    {
        parent::__construct([
            'singular' => __('عضو', 'clubcore'),
            'plural'   => __('اعضا', 'clubcore'),
            'ajax'     => false,
        ]);
        $this->member_repository = new MemberRepository();
    }

    public function get_columns(): array
    {
        return [
            'cb'         => '<input type="checkbox" />',
            'name'       => __('نام و نام خانوادگی', 'clubcore'),
            'phone'      => __('شماره موبایل', 'clubcore'),
            'created_at' => __('تاریخ عضویت', 'clubcore'),
            'source'     => __('منبع', 'clubcore'),
            'status'     => __('وضعیت', 'clubcore'),
            'last_sms'   => __('آخرین پیامک', 'clubcore'),
            'wc'         => __('ووکامرس', 'clubcore'),
            'actions'    => __('عملیات', 'clubcore'),
        ];
    }

    protected function get_sortable_columns(): array
    {
        return [
            'name'       => ['first_name', false],
            'phone'      => ['phone_normalized', false],
            'created_at' => ['membership_created_at', true],
        ];
    }

    public function prepare_items(): void
    {
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $search = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';

        $filters = [];
        if (!empty($_REQUEST['membership_status'])) {
            $filters['membership_status'] = sanitize_text_field(wp_unslash($_REQUEST['membership_status']));
        }
        if (!empty($_REQUEST['membership_source'])) {
            $filters['membership_source'] = sanitize_text_field(wp_unslash($_REQUEST['membership_source']));
        }

        $orderby = !empty($_REQUEST['orderby']) ? sanitize_text_field(wp_unslash($_REQUEST['orderby'])) : 'membership_created_at';
        $order = !empty($_REQUEST['order']) ? sanitize_text_field(wp_unslash($_REQUEST['order'])) : 'DESC';

        $args = [
            'per_page' => $per_page,
            'page'     => $current_page,
            'search'   => $search,
            'orderby'  => $orderby,
            'order'    => $order,
            'filters'  => $filters,
        ];

        $total_items = $this->member_repository->count($filters);
        $this->items = $this->member_repository->list($args);

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    protected function column_cb($item): string
    {
        return sprintf('<input type="checkbox" name="member_id[]" value="%d" />', $item->getId());
    }

    public function column_default($item, $column_name): string
    {
        return match ($column_name) {
            'created_at' => esc_html($item->getMembershipCreatedAt()),
            'source'     => esc_html($item->getMembershipSource()->label()),
            'status'     => sprintf('<span class="clubcore-badge clubcore-badge-success">%s</span>', esc_html($item->getMembershipStatus())),
            'last_sms'   => esc_html($item->getLastSmsStatus() ?: '-'),
            'wc'         => $item->isWoocommerceLinked() ? __('بله', 'clubcore') : __('خیر', 'clubcore'),
            'actions'    => $this->column_actions($item),
            default      => '-',
        };
    }

    public function column_name($item): string
    {
        $name = trim($item->getFirstName() . ' ' . $item->getLastName());
        if ($name === '') {
            $name = __('بدون نام', 'clubcore');
        }

        $viewUrl = add_query_arg([
            'page'      => \ClubCore\Presentation\Admin\MenuManager::getMenuSlug() . '-members',
            'action'    => 'view',
            'member_id' => $item->getId(),
            '_wpnonce'  => wp_create_nonce('clubcore_view_member_' . $item->getId()),
        ], admin_url('admin.php'));

        $actions = [
            'view' => sprintf('<a href="%s">%s</a>', esc_url($viewUrl), esc_html__('مشاهده جزئیات', 'clubcore')),
        ];

        return sprintf('<strong><a href="%s">%s</a></strong> %s', esc_url($viewUrl), esc_html($name), $this->row_actions($actions));
    }

    public function column_phone($item): string
    {
        return sprintf('<span dir="ltr"><code>%s</code></span>', esc_html($item->getPhoneDisplay()));
    }

    private function column_actions($item): string
    {
        $nonce = wp_create_nonce('clubcore_resend_sms');
        return sprintf(
            '<button type="button" class="button button-small clubcore-resend-btn" data-member-id="%d" data-nonce="%s">%s</button>',
            $item->getId(),
            esc_attr($nonce),
            esc_html__('ارسال پیامک', 'clubcore')
        );
    }
}
