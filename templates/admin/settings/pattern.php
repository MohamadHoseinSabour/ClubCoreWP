<?php
/**
 * Settings - Pattern Template
 *
 * @package ClubCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$bodyId   = get_option('clubcore_pattern_body_id', '');
$template = get_option('clubcore_pattern_template', '');
?>
<form method="post" action="options.php">
    <?php
    settings_fields( 'clubcore_pattern_settings' );
    do_settings_sections( 'clubcore_pattern_settings' );
    ?>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="clubcore_pattern_body_id"><?php esc_html_e( 'کد الگو (Pattern Body ID)', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_pattern_body_id" type="text" id="clubcore_pattern_body_id" class="regular-text ltr" dir="ltr" value="<?php echo esc_attr($bodyId); ?>" placeholder="مثال: 123456">
                <p class="description"><?php esc_html_e('کد الگوی تایید شده در پنل پیامک ملی‌پیامک برای ارسال پیامک خوش‌آمدگویی و اعتبارسنجی.', 'clubcore'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_pattern_template"><?php esc_html_e( 'متن الگو (فقط برای نمایش)', 'clubcore' ); ?></label></th>
            <td>
                <textarea name="clubcore_pattern_template" id="clubcore_pattern_template" class="large-text" rows="4" dir="rtl" placeholder="مشتری گرامی {name} به باشگاه مشتریان خوش آمدید."><?php echo esc_textarea($template); ?></textarea>
                <p class="description"><?php esc_html_e( 'متن الگو برای بررسی متغیرها نمایش داده می‌شود.', 'clubcore' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e( 'متغیرهای تشخیص داده شده', 'clubcore' ); ?></th>
            <td>
                <div id="clubcore-detected-vars" class="clubcore-vars-box">
                    <code>{name}</code>
                    <code>{first_name}</code>
                    <code>{last_name}</code>
                    <code>{phone}</code>
                </div>
                <div id="clubcore-pattern-validation" class="clubcore-validation-result"></div>
            </td>
        </tr>
    </table>
    
    <?php submit_button(); ?>
</form>

<hr>
<h3><?php esc_html_e( 'متغیرهای پشتیبانی شده', 'clubcore' ); ?></h3>
<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th><?php esc_html_e( 'متغیر', 'clubcore' ); ?></th>
            <th><?php esc_html_e( 'توضیحات', 'clubcore' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td dir="ltr"><code>{first_name}</code></td>
            <td><?php esc_html_e( 'نام مشتری', 'clubcore' ); ?></td>
        </tr>
        <tr>
            <td dir="ltr"><code>{last_name}</code></td>
            <td><?php esc_html_e( 'نام خانوادگی مشتری', 'clubcore' ); ?></td>
        </tr>
        <tr>
            <td dir="ltr"><code>{name}</code></td>
            <td><?php esc_html_e( 'نام کامل مشتری', 'clubcore' ); ?></td>
        </tr>
        <tr>
            <td dir="ltr"><code>{phone}</code></td>
            <td><?php esc_html_e( 'شماره موبایل مشتری', 'clubcore' ); ?></td>
        </tr>
    </tbody>
</table>
