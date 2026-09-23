<?php
if (!defined('ABSPATH')) exit;

$primary = get_option('clubcore_appearance_primary', '#2271b1');
$secondary = get_option('clubcore_appearance_secondary', '#135e96');
$cardBg = get_option('clubcore_appearance_card', '#ffffff');
$textColor = get_option('clubcore_appearance_text', '#1d2327');
$buttonColor = get_option('clubcore_appearance_button', '#2271b1');
$radius = get_option('clubcore_appearance_radius', '6px');
?>
<div class="clubcore-card">
    <div class="clubcore-card-header"><?php esc_html_e('شخصی‌سازی ظاهر و رنگ‌بندی (RTL)', 'clubcore'); ?></div>
    
    <div id="clubcore-contrast-warning" class="clubcore-notice clubcore-notice-warning" style="display:none;"></div>

    <form method="post" action="options.php">
        <?php settings_fields('clubcore_appearance_settings'); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="clubcore_appearance_primary"><?php esc_html_e('رنگ اصلی (Primary)', 'clubcore'); ?></label></th>
                <td><input type="text" id="clubcore_appearance_primary" name="clubcore_appearance_primary" value="<?php echo esc_attr($primary); ?>" class="clubcore-color-picker"></td>
            </tr>
            <tr>
                <th scope="row"><label for="clubcore_appearance_secondary"><?php esc_html_e('رنگ ثانویه (Secondary)', 'clubcore'); ?></label></th>
                <td><input type="text" id="clubcore_appearance_secondary" name="clubcore_appearance_secondary" value="<?php echo esc_attr($secondary); ?>" class="clubcore-color-picker"></td>
            </tr>
            <tr>
                <th scope="row"><label for="clubcore_appearance_card"><?php esc_html_e('پس‌زمینه کارت‌ها (Card)', 'clubcore'); ?></label></th>
                <td><input type="text" id="clubcore_appearance_card" name="clubcore_appearance_card" value="<?php echo esc_attr($cardBg); ?>" class="clubcore-color-picker"></td>
            </tr>
            <tr>
                <th scope="row"><label for="clubcore_appearance_text"><?php esc_html_e('رنگ متون (Text)', 'clubcore'); ?></label></th>
                <td><input type="text" id="clubcore_appearance_text" name="clubcore_appearance_text" value="<?php echo esc_attr($textColor); ?>" class="clubcore-color-picker"></td>
            </tr>
            <tr>
                <th scope="row"><label for="clubcore_appearance_button"><?php esc_html_e('رنگ دکمه‌ها (Button)', 'clubcore'); ?></label></th>
                <td><input type="text" id="clubcore_appearance_button" name="clubcore_appearance_button" value="<?php echo esc_attr($buttonColor); ?>" class="clubcore-color-picker"></td>
            </tr>
            <tr>
                <th scope="row"><label for="clubcore_appearance_radius"><?php esc_html_e('گردی لبه‌ها (Border Radius)', 'clubcore'); ?></label></th>
                <td>
                    <select id="clubcore_appearance_radius" name="clubcore_appearance_radius">
                        <option value="0px" <?php selected($radius, '0px'); ?>><?php esc_html_e('تیز (0px)', 'clubcore'); ?></option>
                        <option value="4px" <?php selected($radius, '4px'); ?>><?php esc_html_e('کمی گرد (4px)', 'clubcore'); ?></option>
                        <option value="6px" <?php selected($radius, '6px'); ?>><?php esc_html_e('استاندارد (6px)', 'clubcore'); ?></option>
                        <option value="10px" <?php selected($radius, '10px'); ?>><?php esc_html_e('بسیار گرد (10px)', 'clubcore'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <?php submit_button(__('ذخیره ظاهر', 'clubcore')); ?>
    </form>
</div>
