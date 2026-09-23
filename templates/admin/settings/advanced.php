<?php
if (!defined('ABSPATH')) exit;

$deleteOnUninstall = get_option('clubcore_delete_data_on_uninstall', '0');
$debugLogging = get_option('clubcore_debug_logging', '0');
?>
<div class="clubcore-card">
    <div class="clubcore-card-header"><?php esc_html_e('تنظیمات پیشرفته و حذف افزونه', 'clubcore'); ?></div>
    <form method="post" action="options.php">
        <?php settings_fields('clubcore_advanced_settings'); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('لاگ‌گیری جهت عیب‌یابی (Debug Log)', 'clubcore'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="clubcore_debug_logging" value="1" <?php checked($debugLogging, '1'); ?>>
                        <?php esc_html_e('فعال‌سازی لاگ دیباگ برای ثبت جزئیات درخواست‌ها و پاسخ‌های API (اطلاعات حساس و پسوردها هرگز ذخیره نمی‌شوند).', 'clubcore'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('حذف کامل داده‌ها هنگام پاک کردن افزونه', 'clubcore'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="clubcore_delete_data_on_uninstall" value="1" <?php checked($deleteOnUninstall, '1'); ?>>
                        <strong style="color:var(--cc-error);"><?php esc_html_e('در صورت فعال بودن، در زمان Uninstall افزونه، تمام جداول اعضا، لاگ‌های پیامک و گزارشات به طور کامل حذف خواهند شد.', 'clubcore'); ?></strong>
                    </label>
                    <p class="description"><?php esc_html_e('غیرفعال بودن این گزینه باعث نگهداری امن داده‌ها در صورت نصب مجدد در آینده می‌شود.', 'clubcore'); ?></p>
                </td>
            </tr>
        </table>
        <?php submit_button(__('ذخیره تنظیمات پیشرفته', 'clubcore')); ?>
    </form>
</div>
