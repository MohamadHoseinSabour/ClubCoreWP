<?php
/**
 * Settings - Pattern Template
 *
 * @package ClubCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<form method="post" action="options.php">
    <?php
    settings_fields( 'clubcore_pattern_options' );
    do_settings_sections( 'clubcore_pattern_options' );
    ?>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="clubcore_pattern_body_id"><?php esc_html_e( 'کد الگو (Pattern Body ID)', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_pattern_body_id" type="text" id="clubcore_pattern_body_id" class="regular-text ltr" dir="ltr">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_pattern_template"><?php esc_html_e( 'متن الگو (فقط برای نمایش)', 'clubcore' ); ?></label></th>
            <td>
                <textarea id="clubcore_pattern_template" class="large-text" rows="5" readonly dir="rtl" placeholder="مشتری گرامی {name} به باشگاه مشتریان خوش آمدید."></textarea>
                <p class="description"><?php esc_html_e( 'متن الگو برای بررسی متغیرها نمایش داده می‌شود.', 'clubcore' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e( 'متغیرهای تشخیص داده شده', 'clubcore' ); ?></th>
            <td>
                <div id="clubcore-detected-vars" class="clubcore-vars-box">
                    <code>{name}</code>
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
