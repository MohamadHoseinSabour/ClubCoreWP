<?php
/**
 * Member Detail Admin Template
 *
 * @package ClubCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Variables available in scope: $memberId (from MenuManager)
// Assuming we fetch member info here or it's passed down.
// For template purpose, using dummy checks.
$member_id = isset( $memberId ) ? absint( $memberId ) : 0;
$back_url = admin_url( 'admin.php?page=clubcore' );

?>
<div class="wrap clubcore-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'جزئیات عضو', 'clubcore' ); ?></h1>
    <a href="<?php echo esc_url( $back_url ); ?>" class="page-title-action"><?php esc_html_e( 'بازگشت به لیست', 'clubcore' ); ?></a>
    <hr class="wp-header-end">

    <?php if ( ! $member_id ) : ?>
        <div class="notice notice-error"><p><?php esc_html_e( 'عضو یافت نشد.', 'clubcore' ); ?></p></div>
    <?php else : ?>
        <div class="clubcore-dashboard-grid">
            
            <div class="clubcore-card">
                <h3><?php esc_html_e( 'اطلاعات مشتری', 'clubcore' ); ?></h3>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'نام و نام خانوادگی', 'clubcore' ); ?></th>
                            <td>-</td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'شماره موبایل', 'clubcore' ); ?></th>
                            <td class="ltr" dir="ltr">-</td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'ایمیل', 'clubcore' ); ?></th>
                            <td class="ltr" dir="ltr">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="clubcore-card">
                <h3><?php esc_html_e( 'اطلاعات عضویت', 'clubcore' ); ?></h3>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'وضعیت', 'clubcore' ); ?></th>
                            <td>-</td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'منبع ثبت نام', 'clubcore' ); ?></th>
                            <td>-</td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'تاریخ عضویت', 'clubcore' ); ?></th>
                            <td class="ltr" dir="ltr">-</td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'ثبت کننده', 'clubcore' ); ?></th>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="clubcore-card">
                <h3><?php esc_html_e( 'اطلاعات وردپرس', 'clubcore' ); ?></h3>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'شناسه کاربری', 'clubcore' ); ?></th>
                            <td>-</td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'نام کاربری', 'clubcore' ); ?></th>
                            <td class="ltr" dir="ltr">-</td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'نقش کاربری', 'clubcore' ); ?></th>
                            <td>-</td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'پروفایل', 'clubcore' ); ?></th>
                            <td><a href="#"><?php esc_html_e( 'مشاهده پروفایل', 'clubcore' ); ?></a></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <?php if ( class_exists( 'WooCommerce' ) ) : ?>
            <div class="clubcore-card">
                <h3><?php esc_html_e( 'اطلاعات ووکامرس', 'clubcore' ); ?></h3>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'تعداد سفارشات', 'clubcore' ); ?></th>
                            <td>-</td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'مجموع خرید', 'clubcore' ); ?></th>
                            <td>-</td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'آخرین سفارش', 'clubcore' ); ?></th>
                            <td class="ltr" dir="ltr">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

        </div>

        <div class="clubcore-card clubcore-mt-4">
            <h3><?php esc_html_e( 'تاریخچه پیامک', 'clubcore' ); ?></h3>
            <!-- SMS Logs table will be here -->
            <p><?php esc_html_e( '۱۰ پیامک اخیر به این عضو', 'clubcore' ); ?></p>
        </div>

        <div class="clubcore-actions-bar clubcore-mt-4">
            <a href="#" class="button button-primary"><?php esc_html_e( 'ویرایش اطلاعات', 'clubcore' ); ?></a>
            <a href="#" class="button"><?php esc_html_e( 'ارسال پیامک', 'clubcore' ); ?></a>
            <a href="#" class="button"><?php esc_html_e( 'مشاهده پروفایل کاربری', 'clubcore' ); ?></a>
            <?php if ( class_exists( 'WooCommerce' ) ) : ?>
                <a href="#" class="button"><?php esc_html_e( 'مشاهده سفارشات', 'clubcore' ); ?></a>
            <?php endif; ?>
            <a href="#" class="button"><?php esc_html_e( 'خروجی', 'clubcore' ); ?></a>
        </div>
    <?php endif; ?>
</div>
