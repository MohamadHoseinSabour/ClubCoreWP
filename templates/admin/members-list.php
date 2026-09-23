<?php
/**
 * Members List Admin Template
 *
 * @package ClubCore
 */

use ClubCore\Presentation\Table\MembersListTable;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$list_table = new MembersListTable();
$list_table->prepare_items();

?>
<div class="wrap clubcore-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'اعضای باشگاه مشتریان', 'clubcore' ); ?></h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=clubcore-add' ) ); ?>" class="page-title-action"><?php esc_html_e( 'افزودن جدید', 'clubcore' ); ?></a>
    <hr class="wp-header-end">

    <form id="clubcore-members-filter" method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
        <?php wp_nonce_field( 'clubcore_members_list', 'clubcore_nonce' ); ?>
        
        <?php
        $list_table->search_box( esc_html__( 'جستجو', 'clubcore' ), 'clubcore-search' );
        $list_table->display();
        ?>
    </form>
</div>
