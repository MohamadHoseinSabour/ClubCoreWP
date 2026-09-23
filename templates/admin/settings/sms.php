<?php
/**
 * Settings - SMS Template
 *
 * @package ClubCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<form method="post" action="options.php">
    <?php
    settings_fields( 'clubcore_sms_options' );
    do_settings_sections( 'clubcore_sms_options' );
    ?>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="clubcore_sms_provider"><?php esc_html_e( 'سرویس‌دهنده پیامک', 'clubcore' ); ?></label></th>
            <td>
                <select name="clubcore_sms_provider" id="clubcore_sms_provider">
                    <option value="melipayamak" selected><?php esc_html_e( 'ملی پیامک (Melipayamak)', 'clubcore' ); ?></option>
                </select>
                <span class="clubcore-status-indicator status-ok"><?php esc_html_e( 'متصل', 'clubcore' ); ?></span>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_sms_auth_mode"><?php esc_html_e( 'نوع احراز هویت', 'clubcore' ); ?></label></th>
            <td>
                <select name="clubcore_sms_auth_mode" id="clubcore_sms_auth_mode">
                    <option value="classic"><?php esc_html_e( 'کلاسیک (نام کاربری و رمز عبور)', 'clubcore' ); ?></option>
                    <option value="console"><?php esc_html_e( 'کنسول (توکن API)', 'clubcore' ); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_sms_username"><?php esc_html_e( 'نام کاربری', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_sms_username" type="text" id="clubcore_sms_username" class="regular-text ltr" dir="ltr">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_sms_password"><?php esc_html_e( 'رمز عبور', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_sms_password" type="password" id="clubcore_sms_password" class="regular-text ltr" dir="ltr" autocomplete="new-password">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_sms_api_token"><?php esc_html_e( 'توکن API', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_sms_api_token" type="password" id="clubcore_sms_api_token" class="regular-text ltr" dir="ltr" autocomplete="new-password">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_sms_timeout"><?php esc_html_e( 'Timeout (ثانیه)', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_sms_timeout" type="number" id="clubcore_sms_timeout" class="small-text ltr" dir="ltr" value="10">
            </td>
        </tr>
    </table>
    
    <p class="submit">
        <?php submit_button( __( 'ذخیره تنظیمات', 'clubcore' ), 'primary', 'submit', false ); ?>
        <button type="button" class="button" id="clubcore-check-credit"><?php esc_html_e( 'بررسی موجودی اعتبار', 'clubcore' ); ?></button>
    </p>
</form>

<hr>
<h3><?php esc_html_e( 'تست ارسال پیامک', 'clubcore' ); ?></h3>
<div class="clubcore-card" style="max-width: 600px;">
    <table class="form-table">
        <tr>
            <th scope="row"><label for="test_phone"><?php esc_html_e( 'شماره موبایل', 'clubcore' ); ?></label></th>
            <td><input type="tel" id="test_phone" class="regular-text ltr" dir="ltr"></td>
        </tr>
        <tr>
            <th scope="row"><label for="test_first_name"><?php esc_html_e( 'نام', 'clubcore' ); ?></label></th>
            <td><input type="text" id="test_first_name" class="regular-text"></td>
        </tr>
        <tr>
            <th scope="row"><label for="test_last_name"><?php esc_html_e( 'نام خانوادگی', 'clubcore' ); ?></label></th>
            <td><input type="text" id="test_last_name" class="regular-text"></td>
        </tr>
    </table>
    <p>
        <button type="button" class="button button-secondary" id="clubcore-test-sms"><?php esc_html_e( 'ارسال پیامک آزمایشی', 'clubcore' ); ?></button>
    </p>
</div>
