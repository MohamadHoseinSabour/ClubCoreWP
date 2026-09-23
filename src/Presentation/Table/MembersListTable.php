<?php
namespace ClubCore\Presentation\Table;

use WP_List_Table;
use ClubCore\Domain\Repository\MemberRepositoryInterface;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Members List Table
 * 
 * Extends WP_List_Table to display club members.
 */
class MembersListTable extends WP_List_Table {

    /**
     * @var MemberRepositoryInterface
     */
    private $member_repository;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct( [
            'singular' => __( 'Member', 'clubcore' ),
            'plural'   => __( 'Members', 'clubcore' ),
            'ajax'     => false
        ] );
    }

    /**
     * Get table columns
     *
     * @return array
     */
    public function get_columns(): array {
        return [
            'cb'         => '<input type="checkbox" />',
            'name'       => __( 'نام و نام خانوادگی', 'clubcore' ),
            'phone'      => __( 'شماره موبایل', 'clubcore' ),
            'created_at' => __( 'تاریخ عضویت', 'clubcore' ),
            'source'     => __( 'منبع', 'clubcore' ),
            'status'     => __( 'وضعیت', 'clubcore' ),
            'last_sms'   => __( 'آخرین پیامک', 'clubcore' ),
            'wc'         => __( 'ووکامرس', 'clubcore' ),
            'actions'    => __( 'عملیات', 'clubcore' ),
        ];
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    public function get_sortable_columns(): array {
        return [
            'name'       => [ 'first_name', false ],
            'phone'      => [ 'phone', false ],
            'created_at' => [ 'created_at', true ],
        ];
    }

    /**
     * Prepare items for table
     */
    public function prepare_items() {
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [ $columns, $hidden, $sortable ];

        $per_page = 20;
        $current_page = $this->get_pagenum();
        $search = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';

        // Dummy data for now since repository isn't wired in this class directly yet.
        $total_items = 0;
        $this->items = [];

        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page )
        ] );
    }

    /**
     * Default column render
     *
     * @param array|object $item
     * @param string $column_name
     * @return string
     */
    public function column_default( $item, $column_name ): string {
        return isset( $item->$column_name ) ? esc_html( $item->$column_name ) : '-';
    }

    /**
     * Checkbox column
     *
     * @param object $item
     * @return string
     */
    public function column_cb( $item ): string {
        return sprintf(
            '<input type="checkbox" name="member[]" value="%d" />',
            $item->id ?? 0
        );
    }

    /**
     * Name column
     *
     * @param object $item
     * @return string
     */
    public function column_name( $item ): string {
        $first = isset( $item->first_name ) ? $item->first_name : '';
        $last  = isset( $item->last_name ) ? $item->last_name : '';
        $name  = trim( $first . ' ' . $last );
        
        if ( empty( $name ) ) {
            $name = __( 'بدون نام', 'clubcore' );
        }

        return esc_html( $name );
    }

    /**
     * Phone column
     *
     * @param object $item
     * @return string
     */
    public function column_phone( $item ): string {
        $phone = isset( $item->phone ) ? $item->phone : '-';
        return '<span class="ltr" dir="ltr">' . esc_html( $phone ) . '</span>';
    }

    /**
     * Actions column
     *
     * @param object $item
     * @return string
     */
    public function column_actions( $item ): string {
        $id = $item->id ?? 0;
        $view_url = wp_nonce_url( admin_url( 'admin.php?page=clubcore-member&id=' . $id ), 'view_member_' . $id );
        
        return sprintf(
            '<a href="%s" class="button button-small">%s</a>',
            esc_url( $view_url ),
            esc_html__( 'مشاهده', 'clubcore' )
        );
    }

    /**
     * Bulk actions
     *
     * @return array
     */
    public function get_bulk_actions(): array {
        return [
            'delete' => __( 'حذف', 'clubcore' ),
            'export' => __( 'برون‌بری انتخاب شده‌ها', 'clubcore' ),
        ];
    }

    /**
     * Extra table navigation (filters)
     *
     * @param string $which
     */
    protected function extra_tablenav( $which ) {
        if ( $which !== 'top' ) {
            return;
        }
        ?>
        <div class="alignleft actions">
            <select name="filter_status">
                <option value=""><?php esc_html_e( 'همه وضعیت‌ها', 'clubcore' ); ?></option>
                <option value="active"><?php esc_html_e( 'فعال', 'clubcore' ); ?></option>
            </select>
            <select name="filter_source">
                <option value=""><?php esc_html_e( 'همه منابع', 'clubcore' ); ?></option>
            </select>
            <?php submit_button( __( 'فیلتر', 'clubcore' ), '', 'filter_action', false ); ?>
        </div>
        <?php
    }

    /**
     * Search box
     *
     * @param string $text
     * @param string $input_id
     */
    public function search_box( $text, $input_id ) {
        parent::search_box( $text, $input_id );
        // Override placeholder via JS or HTML modification if needed.
        // WP default search_box doesn't support placeholders easily, 
        // but we can add JS or extend it.
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var searchInput = document.getElementById('<?php echo esc_js( $input_id ); ?>');
                if (searchInput) {
                    searchInput.placeholder = '<?php echo esc_js( __( 'جستجو بر اساس شماره، نام یا ایمیل...', 'clubcore' ) ); ?>';
                }
            });
        </script>
        <?php
    }
}
