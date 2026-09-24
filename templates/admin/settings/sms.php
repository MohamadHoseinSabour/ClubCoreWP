<?php
/**
 * Settings - SMS Template
 *
 * @package ClubCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$provider = get_option('clubcore_sms_provider', 'melipayamak');
$authMode = get_option('clubcore_sms_auth_mode', 'classic');
$username = get_option('clubcore_sms_username', '');
$password = get_option('clubcore_sms_password', '');
$apiToken = get_option('clubcore_sms_api_token', '');
$timeout  = (int) get_option('clubcore_sms_timeout', 30);
?>
<form method="post" action="options.php">
    <?php
    settings_fields( 'clubcore_sms_settings' );
    do_settings_sections( 'clubcore_sms_settings' );
    ?>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="clubcore_sms_provider"><?php esc_html_e( 'سرویس‌دهنده پیامک', 'clubcore' ); ?></label></th>
            <td>
                <select name="clubcore_sms_provider" id="clubcore_sms_provider">
                    <option value="melipayamak" <?php selected($provider, 'melipayamak'); ?>><?php esc_html_e( 'ملی پیامک (Melipayamak)', 'clubcore' ); ?></option>
                </select>
                <span class="clubcore-status-indicator status-ok"><?php esc_html_e( 'متصل', 'clubcore' ); ?></span>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_sms_auth_mode"><?php esc_html_e( 'نوع احراز هویت', 'clubcore' ); ?></label></th>
            <td>
                <select name="clubcore_sms_auth_mode" id="clubcore_sms_auth_mode">
                    <option value="classic" <?php selected($authMode, 'classic'); ?>><?php esc_html_e( 'کلاسیک (نام کاربری و رمز عبور)', 'clubcore' ); ?></option>
                    <option value="console" <?php selected($authMode, 'console'); ?>><?php esc_html_e( 'کنسول (توکن API)', 'clubcore' ); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_sms_username"><?php esc_html_e( 'نام کاربری', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_sms_username" type="text" id="clubcore_sms_username" class="regular-text ltr" dir="ltr" value="<?php echo esc_attr($username); ?>">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_sms_password"><?php esc_html_e( 'رمز عبور', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_sms_password" type="password" id="clubcore_sms_password" class="regular-text ltr" dir="ltr" autocomplete="new-password" value="<?php echo !empty($password) ? '********' : ''; ?>" placeholder="<?php echo !empty($password) ? '••••••••' : ''; ?>">
                <p class="description"><?php esc_html_e('در صورت عدم تغییر، فیلد را خالی بگذارید.', 'clubcore'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_sms_api_token"><?php esc_html_e( 'توکن API', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_sms_api_token" type="password" id="clubcore_sms_api_token" class="regular-text ltr" dir="ltr" autocomplete="new-password" value="<?php echo !empty($apiToken) ? '********' : ''; ?>" placeholder="<?php echo !empty($apiToken) ? '••••••••' : ''; ?>">
                <p class="description"><?php esc_html_e('در صورت عدم تغییر، فیلد را خالی بگذارید.', 'clubcore'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_sms_timeout"><?php esc_html_e( 'Timeout (ثانیه)', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_sms_timeout" type="number" id="clubcore_sms_timeout" class="small-text ltr" dir="ltr" value="<?php echo esc_attr($timeout); ?>">
            </td>
        </tr>
    </table>
    
    <?php wp_nonce_field( 'clubcore_check_sms_connection', 'clubcore_check_sms_connection_nonce' ); ?>
    <p class="submit">
        <?php submit_button( __( 'ذخیره تنظیمات', 'clubcore' ), 'primary', 'submit', false ); ?>
        <button type="button" class="button button-secondary" id="clubcore-check-credit">
            🔍 <?php esc_html_e( 'بررسی ارتباط و موجودی اعتبار', 'clubcore' ); ?>
        </button>
        <span id="clubcore-credit-result" style="margin-right: 12px; font-weight: 600; display: inline-block; vertical-align: middle;"></span>
    </p>
</form>

<hr>
<h3><?php esc_html_e( 'تست ارسال پیامک', 'clubcore' ); ?></h3>
<div class="clubcore-card" style="max-width: 600px;">
    <?php wp_nonce_field( 'clubcore_test_sms', 'clubcore_test_sms_nonce' ); ?>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="clubcore-test-phone"><?php esc_html_e( 'شماره موبایل', 'clubcore' ); ?></label></th>
            <td><input type="tel" id="clubcore-test-phone" name="test_phone" class="regular-text ltr" dir="ltr" placeholder="09123456789"></td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore-test-fname"><?php esc_html_e( 'نام', 'clubcore' ); ?></label></th>
            <td><input type="text" id="clubcore-test-fname" name="test_first_name" class="regular-text" placeholder="مثال: علی"></td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore-test-lname"><?php esc_html_e( 'نام خانوادگی', 'clubcore' ); ?></label></th>
            <td><input type="text" id="clubcore-test-lname" name="test_last_name" class="regular-text" placeholder="مثال: محمدی"></td>
        </tr>
    </table>
    <p>
        <button type="button" class="button button-secondary" id="clubcore-test-sms-btn">
            📨 <?php esc_html_e( 'ارسال پیامک آزمایشی', 'clubcore' ); ?>
        </button>
    </p>
    <div id="clubcore-test-sms-result" style="margin-top: 12px;"></div>
</div>
