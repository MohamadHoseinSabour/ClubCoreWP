<?php
if (!defined('ABSPATH')) {
    exit;
}

$roles = wp_roles()->get_names();
$allowedTerminalRoles = (array) get_option('clubcore_terminal_allowed_roles', ['administrator', 'shop_manager']);
$guestMode = (string) get_option('clubcore_terminal_guest_mode', 'login_required');
?>
<div class="clubcore-card">
    <div class="clubcore-card-header"><?php esc_html_e('مدیریت دسترسی و امنیت نقش‌های کاربری', 'clubcore'); ?></div>
    
    <div style="background: #f0fdf4; border-right: 4px solid #16a34a; padding: 12px 16px; border-radius: 6px; margin: 15px 0 20px; color: #166534; font-size: 13.5px; line-height: 1.7;">
        <strong><?php esc_html_e('دسترسی همیشگی مدیر کل:', 'clubcore'); ?></strong>
        <?php esc_html_e('نقش مدیر کل (Administrator) به صورت خودکار و بدون هیچ‌گونه محدودیتی همواره به تمام بخش‌های مدیریت، ثبت و ویرایش اعضا، تنظیمات و دستگاه لمسی ثبت مشتری دسترسی کامل دارد.', 'clubcore'); ?>
    </div>

    <form method="post" action="options.php">
        <?php
        settings_fields('clubcore_access_settings');
        ?>

        <h3 style="font-size: 16px; margin: 25px 0 10px; color: #1e293b;">
            📱 <?php esc_html_e('دسترسی به صفحه دستگاه لمسی ثبت مشتری (کیوسک / موبایل)', 'clubcore'); ?>
        </h3>
        <p class="description" style="margin-bottom: 14px;">
            <?php esc_html_e('کاربران غیرمدیر (مانند مشترکین، مشتریان عادی ووکامرس یا مهمانان سایت) نباید بتوانند بدون اجازه به صفحه دستگاه ثبت مشتری دسترسی داشته باشند. در اینجا مشخص کنید چه نقش‌هایی مجاز به باز کردن این صفحه هستند:', 'clubcore'); ?>
        </p>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px 20px; margin-bottom: 25px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; margin-bottom: 18px;">
                <?php foreach ($roles as $roleKey => $roleName): 
                    $isAdmin = ($roleKey === 'administrator');
                    $isAllowed = $isAdmin || in_array($roleKey, $allowedTerminalRoles, true);
                ?>
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: <?php echo $isAdmin ? '700' : '500'; ?>; color: #334155; cursor: <?php echo $isAdmin ? 'default' : 'pointer'; ?>;">
                        <input type="checkbox" 
                               name="clubcore_terminal_allowed_roles[]" 
                               value="<?php echo esc_attr($roleKey); ?>" 
                               <?php checked($isAllowed); ?>
                               <?php disabled($isAdmin); ?>>
                        <span><?php echo esc_html($roleName); ?></span>
                        <code style="font-size: 11px; color: #64748b;">(<?php echo esc_html($roleKey); ?>)</code>
                        <?php if ($isAdmin): ?>
                            <span style="font-size: 11px; background: #e2e8f0; color: #475569; padding: 1px 6px; border-radius: 4px;"><?php esc_html_e('همیشه مجاز', 'clubcore'); ?></span>
                            <!-- Hidden input to guarantee administrator is always saved in POST -->
                            <input type="hidden" name="clubcore_terminal_allowed_roles[]" value="administrator">
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div style="border-top: 1px solid #e2e8f0; padding-top: 14px; margin-top: 10px;">
                <label style="font-weight: 600; color: #1e293b; display: block; margin-bottom: 8px;">
                    <?php esc_html_e('رفتار در صورت مراجعه کاربر لاگین‌نکرده به صفحه دستگاه:', 'clubcore'); ?>
                </label>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="radio" name="clubcore_terminal_guest_mode" value="login_required" <?php checked($guestMode, 'login_required'); ?>>
                        <span><?php esc_html_e('هدایت خودکار به صفحه ورود وردپرس (توصیه شده - امن‌ترین حالت)', 'clubcore'); ?></span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="radio" name="clubcore_terminal_guest_mode" value="allow_pin" <?php checked($guestMode, 'allow_pin'); ?>>
                        <span><?php esc_html_e('اجازه ورود با کد پین ۴ رقمی (برای استندها یا تبلت‌های اختصاصی صندوق فروشگاه)', 'clubcore'); ?></span>
                    </label>
                </div>
            </div>
        </div>

        <h3 style="font-size: 16px; margin: 30px 0 10px; color: #1e293b;">
            🛡️ <?php esc_html_e('دسترسی بخش‌های پیشخوان مدیریت وردپرس برای سایر نقش‌ها', 'clubcore'); ?>
        </h3>
        <p class="description">
            <?php esc_html_e('دسترسی نقش‌های مختلف کاربری به منوها و قابلیت‌های داخل پیشخوان وردپرس را فعال یا غیرفعال کنید.', 'clubcore'); ?>
        </p>

        <table class="widefat striped" style="margin-top:15px; border-radius: 8px; overflow: hidden;">
            <thead>
                <tr>
                    <th><?php esc_html_e('نقش کاربری', 'clubcore'); ?></th>
                    <th style="text-align: center;"><?php esc_html_e('ثبت مشتری', 'clubcore'); ?></th>
                    <th style="text-align: center;"><?php esc_html_e('مشاهده اعضا', 'clubcore'); ?></th>
                    <th style="text-align: center;"><?php esc_html_e('تاریخچه پیامک', 'clubcore'); ?></th>
                    <th style="text-align: center;"><?php esc_html_e('وارد / خروجی', 'clubcore'); ?></th>
                    <th style="text-align: center;"><?php esc_html_e('تنظیمات', 'clubcore'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $roleKey => $roleName): 
                    if ($roleKey === 'administrator') continue; // Administrator always has all capabilities
                    $roleObj = get_role($roleKey);
                ?>
                <tr>
                    <td><strong><?php echo esc_html($roleName); ?></strong> (<code><?php echo esc_html($roleKey); ?></code>)</td>
                    <td style="text-align: center;"><input type="checkbox" name="clubcore_role_caps[<?php echo esc_attr($roleKey); ?>][clubcore_add_members]" value="1" <?php checked($roleObj && $roleObj->has_cap('clubcore_add_members')); ?>></td>
                    <td style="text-align: center;"><input type="checkbox" name="clubcore_role_caps[<?php echo esc_attr($roleKey); ?>][clubcore_view_members]" value="1" <?php checked($roleObj && $roleObj->has_cap('clubcore_view_members')); ?>></td>
                    <td style="text-align: center;"><input type="checkbox" name="clubcore_role_caps[<?php echo esc_attr($roleKey); ?>][clubcore_view_sms_logs]" value="1" <?php checked($roleObj && $roleObj->has_cap('clubcore_view_sms_logs')); ?>></td>
                    <td style="text-align: center;"><input type="checkbox" name="clubcore_role_caps[<?php echo esc_attr($roleKey); ?>][clubcore_import_members]" value="1" <?php checked($roleObj && $roleObj->has_cap('clubcore_import_members')); ?>></td>
                    <td style="text-align: center;"><input type="checkbox" name="clubcore_role_caps[<?php echo esc_attr($roleKey); ?>][clubcore_manage_settings]" value="1" <?php checked($roleObj && $roleObj->has_cap('clubcore_manage_settings')); ?>></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p style="margin-top:20px;"><?php submit_button(__('ذخیره دسترسی‌ها', 'clubcore'), 'primary', 'submit'); ?></p>
    </form>
</div>
