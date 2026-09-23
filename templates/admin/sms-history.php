<?php
/**
 * SMS History Admin Template
 *
 * @package ClubCore
 */

use ClubCore\Presentation\Table\SmsLogsListTable;

if (!defined('ABSPATH')) {
    exit;
}

$list_table = new SmsLogsListTable();
$list_table->prepare_items();
?>
<div class="wrap clubcore-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('تاریخچه پیامک‌های ارسالی', 'clubcore'); ?></h1>
    <hr class="wp-header-end">

    <form id="clubcore-sms-history-filter" method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page'] ?? 'customer-club-sms-history'); ?>" />
        <?php
        $list_table->search_box(esc_html__('جستجو بر اساس شماره', 'clubcore'), 'clubcore-search');
        $list_table->display();
        ?>
    </form>
</div>
