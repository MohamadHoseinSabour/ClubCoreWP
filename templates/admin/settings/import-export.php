<?php
if (!defined('ABSPATH')) exit;

$chunkSize = (int) get_option('clubcore_import_chunk_size', 500);
$defaultMode = get_option('clubcore_import_default_mode', 'skip_duplicates');
?>
<div class="clubcore-card">
    <div class="clubcore-card-header"><?php esc_html_e('تنظیمات واردسازی و خروجی', 'clubcore'); ?></div>
    <form method="post" action="options.php">
        <?php settings_fields('clubcore_import_export_settings'); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="clubcore_import_chunk_size"><?php esc_html_e('تعداد ردیف در هر بسته پردازش (Batch Size)', 'clubcore'); ?></label></th>
                <td>
                    <input type="number" min="50" max="2000" step="50" id="clubcore_import_chunk_size" name="clubcore_import_chunk_size" value="<?php echo esc_attr($chunkSize); ?>">
                    <p class="description"><?php esc_html_e('پردازش دسته‌ای برای جلوگیری از اتمام حافظه RAM سرور در فایل‌های با بیش از ۱۰،۰۰۰ ردیف (پیش‌فرض: ۵۰۰).', 'clubcore'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('حالت پیش‌فرض برخورد با اعضای تکراری', 'clubcore'); ?></th>
                <td>
                    <label><input type="radio" name="clubcore_import_default_mode" value="skip_duplicates" <?php checked($defaultMode, 'skip_duplicates'); ?>> <?php esc_html_e('رد کردن شماره‌های تکراری (پیش‌فرض امن)', 'clubcore'); ?></label><br>
                    <label><input type="radio" name="clubcore_import_default_mode" value="update_existing" <?php checked($defaultMode, 'update_existing'); ?>> <?php esc_html_e('بروزرسانی مشخصات عضو موجود با اطلاعات جدید', 'clubcore'); ?></label><br>
                    <label><input type="radio" name="clubcore_import_default_mode" value="create_new_only" <?php checked($defaultMode, 'create_new_only'); ?>> <?php esc_html_e('فقط ایجاد اعضای جدید (ثبت خطا برای تکراری‌ها)', 'clubcore'); ?></label>
                </td>
            </tr>
        </table>
        <?php submit_button(__('ذخیره تنظیمات', 'clubcore')); ?>
    </form>
</div>
