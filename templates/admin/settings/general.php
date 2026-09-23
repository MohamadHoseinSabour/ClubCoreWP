<?php
/**
 * Settings - General & Terminal Template
 *
 * @package ClubCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$menuTitle = get_option('clubcore_admin_menu_title', 'باشگاه مشتریان');
$pageSlug = get_option('clubcore_admin_page_slug', 'customer-club');
$menuIcon = get_option('clubcore_admin_menu_icon', 'dashicons-groups');
$menuPos = get_option('clubcore_admin_menu_position', 30);

$termTitle = get_option('clubcore_terminal_title', get_bloginfo('name') ?: 'باشگاه مشتریان');
$termSlug = get_option('clubcore_terminal_slug', 'club-terminal');
$termPin = get_option('clubcore_terminal_pin', '1234');
$termReqPin = get_option('clubcore_terminal_require_pin', '0');

$terminalUrl = \ClubCore\Plugin::init()->getTerminalManager()->getTerminalUrl();
?>
<form method="post" action="options.php">
    <?php
    settings_fields( 'clubcore_general_settings' );
    ?>

    <!-- Kiosk / Terminal Showcase Banner -->
    <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 1px solid #334155; padding: 20px; border-radius: 12px; margin-bottom: 24px; color: #fff; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
        <div>
            <h3 style="margin: 0 0 6px; font-size: 16px; color: #38bdf8; display: flex; align-items: center; gap: 8px;">
                <span>📱</span>
                <span><?php esc_html_e('دستگاه ثبت مشتری (کیوسک لمسی / شبیه دستگاه پوز)', 'clubcore'); ?></span>
            </h3>
            <p style="margin: 0; font-size: 13px; color: #94a3b8; line-height: 1.6;">
                <?php esc_html_e('آدرس دستگاه لمسی برای قرار دادن روی تبلت، موبایل یا سیستم صندوقدار بدون نیاز به دسترسی به پنل مدیریت وردپرس:', 'clubcore'); ?><br>
                <code style="color: #34d399; background: rgba(0,0,0,0.3); padding: 2px 8px; border-radius: 4px; font-size: 13px; display: inline-block; margin-top: 4px;" dir="ltr"><?php echo esc_html($terminalUrl); ?></code>
            </p>
        </div>
        <a href="<?php echo esc_url($terminalUrl); ?>" target="_blank" class="button button-primary button-hero" style="background: #10b981; border-color: #059669; font-weight: 700; border-radius: 8px; font-size: 14px; height: 44px; line-height: 42px;">
            <?php esc_html_e('مشاهده و آزمایش دستگاه لمسی ↗', 'clubcore'); ?>
        </a>
    </div>

    <h2 class="title"><?php esc_html_e('تنظیمات دستگاه لمسی ثبت مشتری (کیوسک / موبایل)', 'clubcore'); ?></h2>
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row"><label for="clubcore_terminal_title"><?php esc_html_e( 'عنوان نمایشگر دستگاه', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_terminal_title" type="text" id="clubcore_terminal_title" class="regular-text" value="<?php echo esc_attr($termTitle); ?>">
                <p class="description"><?php esc_html_e( 'نام فروشگاه یا باشگاه که بالای صفحه دستگاه نمایش داده می‌شود.', 'clubcore' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_terminal_slug"><?php esc_html_e( 'پیوند یکتای دستگاه (Slug)', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_terminal_slug" type="text" id="clubcore_terminal_slug" class="regular-text ltr" dir="ltr" value="<?php echo esc_attr($termSlug); ?>">
                <p class="description"><?php esc_html_e( 'مسیر آدرس دستگاه در سایت (مثال: club-terminal که آدرس site.com/club-terminal/ می‌شود).', 'clubcore' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_terminal_require_pin"><?php esc_html_e( 'قفل امنیتی پین (PIN)', 'clubcore' ); ?></label></th>
            <td>
                <label>
                    <input name="clubcore_terminal_require_pin" type="checkbox" id="clubcore_terminal_require_pin" value="1" <?php checked($termReqPin, '1'); ?>>
                    <?php esc_html_e( 'برای کاربرانی که لاگین نکرده‌اند پین امنیتی درخواست شود.', 'clubcore' ); ?>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_terminal_pin"><?php esc_html_e( 'کد پین ۴ رقمی صندوق', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_terminal_pin" type="password" id="clubcore_terminal_pin" class="small-text ltr" dir="ltr" maxlength="4" value="<?php echo esc_attr($termPin); ?>">
                <p class="description"><?php esc_html_e( 'پین پیش‌فرض: 1234 (اپراتور برای باز کردن دستگاه در صورت خروج از حساب این کد را وارد می‌کند).', 'clubcore' ); ?></p>
            </td>
        </tr>
    </table>

    <h2 class="title" style="margin-top: 30px;"><?php esc_html_e('تنظیمات منوی پیشخوان وردپرس', 'clubcore'); ?></h2>
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row"><label for="clubcore_admin_menu_title"><?php esc_html_e( 'عنوان منوی ادمین', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_admin_menu_title" type="text" id="clubcore_admin_menu_title" class="regular-text" value="<?php echo esc_attr($menuTitle); ?>">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_admin_page_slug"><?php esc_html_e( 'اسلاگ منوی مدیریت', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_admin_page_slug" type="text" id="clubcore_admin_page_slug" class="regular-text ltr" dir="ltr" value="<?php echo esc_attr($pageSlug); ?>">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_admin_menu_icon"><?php esc_html_e( 'آیکون منو', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_admin_menu_icon" type="text" id="clubcore_admin_menu_icon" class="regular-text ltr" dir="ltr" value="<?php echo esc_attr($menuIcon); ?>">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="clubcore_admin_menu_position"><?php esc_html_e( 'موقعیت منو', 'clubcore' ); ?></label></th>
            <td>
                <input name="clubcore_admin_menu_position" type="number" id="clubcore_admin_menu_position" class="small-text ltr" dir="ltr" value="<?php echo esc_attr($menuPos); ?>">
            </td>
        </tr>
    </table>

    <?php submit_button(); ?>
</form>
