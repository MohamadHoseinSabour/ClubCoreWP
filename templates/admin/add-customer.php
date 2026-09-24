<?php
/**
 * Add Customer Admin Template
 *
 * @package ClubCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div class="wrap clubcore-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'ثبت مشتری جدید', 'clubcore' ); ?></h1>
    <hr class="wp-header-end">

    <?php
    $terminalUrl = \ClubCore\Plugin::init()->getTerminalManager()->getTerminalUrl();
    ?>
    <div style="background: linear-gradient(135deg, #064e3b 0%, #065f46 100%); color: #fff; padding: 16px 20px; margin: 16px 0 24px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 12px rgba(6, 95, 70, 0.25);">
        <div>
            <strong style="font-size: 15px; display: flex; align-items: center; gap: 8px;">
                <span><?php esc_html_e('دستگاه لمسی ثبت مشتری (کیوسک اختصاصی ویژه موبایل و تبلت)', 'clubcore'); ?></span>
            </strong>
            <p style="margin: 6px 0 0; color: #a7f3d0; font-size: 13px;">
                <?php esc_html_e('برای صندوقدار، تبلت یا موبایل فروشگاه، از دستگاه ثبت مشتری لمسی استفاده کنید (شبیه دستگاه‌های پوز / بدون ورود به پیشخوان وردپرس).', 'clubcore'); ?>
            </p>
        </div>
        <a href="<?php echo esc_url($terminalUrl); ?>" target="_blank" class="button button-primary" style="background: #10b981; border-color: #34d399; font-weight: 700; padding: 8px 20px; height: auto; font-size: 14px; border-radius: 8px; white-space: nowrap; color: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
            <?php esc_html_e('باز کردن دستگاه ثبت مشتری ↗', 'clubcore'); ?>
        </a>
    </div>

    <div class="clubcore-card">
        <form id="clubcore-add-customer-form" method="post" action="">
            <?php wp_nonce_field( 'clubcore_create_member', 'clubcore_nonce' ); ?>
            
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="clubcore-phone"><?php esc_html_e( 'شماره موبایل (الزامی)', 'clubcore' ); ?></label>
                        </th>
                        <td>
                            <input name="phone" type="tel" id="clubcore-phone" class="regular-text ltr" dir="ltr" required>
                            <p class="description"><?php esc_html_e( 'شماره موبایل مشتری را وارد کنید (مانند: 09123456789)', 'clubcore' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="clubcore-first-name"><?php esc_html_e( 'نام', 'clubcore' ); ?></label>
                        </th>
                        <td>
                            <input name="first_name" type="text" id="clubcore-first-name" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="clubcore-last-name"><?php esc_html_e( 'نام خانوادگی', 'clubcore' ); ?></label>
                        </th>
                        <td>
                            <input name="last_name" type="text" id="clubcore-last-name" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="clubcore-email"><?php esc_html_e( 'ایمیل (اختیاری)', 'clubcore' ); ?></label>
                        </th>
                        <td>
                            <input name="email" type="email" id="clubcore-email" class="regular-text ltr" dir="ltr">
                        </td>
                    </tr>
                </tbody>
            </table>

            <p class="submit">
                <button type="submit" name="submit" id="clubcore-submit-btn" class="button button-primary button-hero">
                    <?php esc_html_e( 'ثبت مشتری و ارسال پیامک', 'clubcore' ); ?>
                </button>
                <span class="spinner" id="clubcore-spinner"></span>
            </p>
        </form>
    </div>

    <div id="clubcore-result" aria-live="polite" class="clubcore-result-container hidden">
        <!-- AJAX responses will be displayed here -->
    </div>
</div>
