<?php
/**
 * Settings - General Template
 *
 * @package ClubCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<form method="post" action="options.php">
    <?php
    settings_fields( 'clubcore_general_options' );
    do_settings_sections( 'clubcore_general_options' );
    ?>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="clubcore_plugin_name"><?php esc_html_e( 'نام افزونه', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_plugin_name" type="text" id="clubcore_plugin_name" class="regular-text" value="باشگاه مشتریان">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_menu_title"><?php esc_html_e( 'عنوان منوی ادمین', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_menu_title" type="text" id="clubcore_menu_title" class="regular-text" value="باشگاه مشتریان">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_page_slug"><?php esc_html_e( 'اسلاگ صفحه (Slug)', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_page_slug" type="text" id="clubcore_page_slug" class="regular-text ltr" dir="ltr" value="clubcore">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_menu_icon"><?php esc_html_e( 'آیکون منو', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_menu_icon" type="text" id="clubcore_menu_icon" class="regular-text ltr" dir="ltr" value="dashicons-groups">
                <p class="description"><?php esc_html_e( 'یکی از آیکون‌های Dashicons را وارد کنید.', 'clubcore' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_menu_position"><?php esc_html_e( 'موقعیت منو', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_menu_position" type="number" id="clubcore_menu_position" class="small-text ltr" dir="ltr" value="30">
            </td>
        </tr>
    </table>
    <?php submit_button(); ?>
</form>
