<?php
if (!defined('ABSPATH')) exit;

$smsRetention = (int) get_option('clubcore_sms_log_retention_days', 90);
$auditRetention = (int) get_option('clubcore_audit_log_retention_days', 180);
$anonymizeOnUserDelete = get_option('clubcore_anonymize_on_user_delete', '1');
?>
<div class="clubcore-card">
    <div class="clubcore-card-header"><?php esc_html_e('حریم خصوصی و نگهداری داده‌ها (Privacy & Data Retention)', 'clubcore'); ?></div>
    <form method="post" action="options.php">
        <?php settings_fields('clubcore_privacy_settings'); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="clubcore_sms_log_retention_days"><?php esc_html_e('مدت نگهداری لاگ‌های پیامک (روز)', 'clubcore'); ?></label></th>
                <td>
                    <input type="number" min="7" max="365" id="clubcore_sms_log_retention_days" name="clubcore_sms_log_retention_days" value="<?php echo esc_attr($smsRetention); ?>">
                    <p class="description"><?php esc_html_e('پیامک‌های قدیمی‌تر از این تعداد روز به صورت خودکار پاک‌سازی می‌شوند (پیش‌فرض: ۹۰ روز).', 'clubcore'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="clubcore_audit_log_retention_days"><?php esc_html_e('مدت نگهداری گزارشات عملیات (روز)', 'clubcore'); ?></label></th>
                <td>
                    <input type="number" min="30" max="730" id="clubcore_audit_log_retention_days" name="clubcore_audit_log_retention_days" value="<?php echo esc_attr($auditRetention); ?>">
                    <p class="description"><?php esc_html_e('گزارش‌های عملیات سیستمی پس از این مدت زمان حذف خواهند شد (پیش‌فرض: ۱۸۰ روز).', 'clubcore'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('رفتار در زمان حذف کاربر وردپرس', 'clubcore'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="clubcore_anonymize_on_user_delete" value="1" <?php checked($anonymizeOnUserDelete, '1'); ?>>
                        <?php esc_html_e('گمنام‌سازی عضو باشگاه در صورت حذف کاربر وردپرس (حفظ رکورد بدون نام و ایمیل)', 'clubcore'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php submit_button(__('ذخیره تنظیمات حریم خصوصی', 'clubcore')); ?>
    </form>
</div>
