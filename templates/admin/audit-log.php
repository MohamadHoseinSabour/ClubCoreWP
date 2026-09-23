<?php
/**
 * Audit Log Admin Template
 *
 * @package ClubCore
 */

use ClubCore\Presentation\Table\AuditLogsListTable;

if (!defined('ABSPATH')) {
    exit;
}

$list_table = new AuditLogsListTable();
$list_table->prepare_items();
?>
<div class="wrap clubcore-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('گزارش عملیات سیستمی (Audit Log)', 'clubcore'); ?></h1>
    <hr class="wp-header-end">

    <form id="clubcore-audit-log-filter" method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page'] ?? 'customer-club-audit-log'); ?>" />
        <?php
        $list_table->display();
        ?>
    </form>
</div>
