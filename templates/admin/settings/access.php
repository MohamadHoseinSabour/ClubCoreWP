<?php
if (!defined('ABSPATH')) exit;
$roles = wp_roles()->get_names();
?>
<div class="clubcore-card">
    <div class="clubcore-card-header"><?php esc_html_e('مدیریت دسترسی نقش‌های کاربری', 'clubcore'); ?></div>
    <p class="description"><?php esc_html_e('تعیین کنید کدام نقش‌های کاربری وردپرس به بخش‌های مختلف باشگاه مشتریان دسترسی داشته باشند.', 'clubcore'); ?></p>

    <form method="post" action="options.php">
        <?php settings_fields('clubcore_access_settings'); ?>
        <table class="widefat striped" style="margin-top:15px;">
            <thead>
                <tr>
                    <th><?php esc_html_e('نقش کاربری', 'clubcore'); ?></th>
                    <th><?php esc_html_e('ثبت مشتری', 'clubcore'); ?></th>
                    <th><?php esc_html_e('مشاهده اعضا', 'clubcore'); ?></th>
                    <th><?php esc_html_e('تاریخچه پیامک', 'clubcore'); ?></th>
                    <th><?php esc_html_e('وارد / خروجی', 'clubcore'); ?></th>
                    <th><?php esc_html_e('تنظیمات', 'clubcore'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $roleKey => $roleName): 
                    if ($roleKey === 'administrator') continue; // Administrator always has all capabilities
                    $roleObj = get_role($roleKey);
                ?>
                <tr>
                    <td><strong><?php echo esc_html($roleName); ?></strong> (<code><?php echo esc_html($roleKey); ?></code>)</td>
                    <td><input type="checkbox" name="clubcore_role_caps[<?php echo esc_attr($roleKey); ?>][clubcore_add_members]" value="1" <?php checked($roleObj && $roleObj->has_cap('clubcore_add_members')); ?>></td>
                    <td><input type="checkbox" name="clubcore_role_caps[<?php echo esc_attr($roleKey); ?>][clubcore_view_members]" value="1" <?php checked($roleObj && $roleObj->has_cap('clubcore_view_members')); ?>></td>
                    <td><input type="checkbox" name="clubcore_role_caps[<?php echo esc_attr($roleKey); ?>][clubcore_view_sms_logs]" value="1" <?php checked($roleObj && $roleObj->has_cap('clubcore_view_sms_logs')); ?>></td>
                    <td><input type="checkbox" name="clubcore_role_caps[<?php echo esc_attr($roleKey); ?>][clubcore_import_members]" value="1" <?php checked($roleObj && $roleObj->has_cap('clubcore_import_members')); ?>></td>
                    <td><input type="checkbox" name="clubcore_role_caps[<?php echo esc_attr($roleKey); ?>][clubcore_manage_settings]" value="1" <?php checked($roleObj && $roleObj->has_cap('clubcore_manage_settings')); ?>></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p style="margin-top:15px;"><?php submit_button(__('ذخیره دسترسی‌ها', 'clubcore'), 'primary', 'submit_access'); ?></p>
    </form>
</div>
