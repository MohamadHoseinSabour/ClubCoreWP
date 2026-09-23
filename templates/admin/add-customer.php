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

    <div class="clubcore-card">
        <form id="clubcore-add-customer-form" method="post" action="">
            <?php wp_nonce_field( 'clubcore_add_customer', 'clubcore_nonce' ); ?>
            
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
                <button type="submit" name="submit" id="submit" class="button button-primary button-hero">
                    <?php esc_html_e( 'ثبت مشتری و ارسال پیامک', 'clubcore' ); ?>
                </button>
                <span class="spinner" id="clubcore-add-spinner"></span>
            </p>
        </form>
    </div>

    <div id="clubcore-result" aria-live="polite" class="clubcore-result-container hidden">
        <!-- AJAX responses will be displayed here -->
    </div>
</div>
