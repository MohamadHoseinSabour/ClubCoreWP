<?php
/**
 * Import/Export Admin Template
 *
 * @package ClubCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap clubcore-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'وارد / خروجی', 'clubcore' ); ?></h1>
    <hr class="wp-header-end">

    <div class="clubcore-dashboard-grid">
        <div class="clubcore-card">
            <h2><?php esc_html_e( 'درون‌ریزی (Import)', 'clubcore' ); ?></h2>
            
            <form id="clubcore-import-form" method="post" enctype="multipart/form-data">
                <?php wp_nonce_field( 'clubcore_import', 'clubcore_nonce' ); ?>
                
                <h3 class="nav-tab-wrapper" id="clubcore-import-tabs">
                    <a href="#upload" class="nav-tab nav-tab-active"><?php esc_html_e( 'آپلود فایل (CSV/XLSX)', 'clubcore' ); ?></a>
                    <a href="#clipboard" class="nav-tab"><?php esc_html_e( 'جایگذاری از کلیپ‌بورد', 'clubcore' ); ?></a>
                </h3>

                <div id="tab-upload" class="clubcore-tab-content active">
                    <p>
                        <input type="file" name="import_file" id="import_file" accept=".csv, .xlsx">
                    </p>
                    <p>
                        <a href="#" class="button"><?php esc_html_e( 'دانلود قالب CSV', 'clubcore' ); ?></a>
                        <a href="#" class="button"><?php esc_html_e( 'دانلود قالب XLSX', 'clubcore' ); ?></a>
                    </p>
                </div>

                <div id="tab-clipboard" class="clubcore-tab-content" style="display: none;">
                    <p>
                        <textarea name="import_text" id="import_text" rows="5" class="large-text" placeholder="<?php esc_attr_e( 'اطلاعات را اینجا paste کنید...', 'clubcore' ); ?>"></textarea>
                    </p>
                </div>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'حالت درون‌ریزی', 'clubcore' ); ?></th>
                        <td>
                            <fieldset>
                                <label><input type="radio" name="import_mode" value="skip" checked> <?php esc_html_e( 'رد کردن تکراری‌ها (Skip Duplicates)', 'clubcore' ); ?></label><br>
                                <label><input type="radio" name="import_mode" value="update"> <?php esc_html_e( 'بروزرسانی اطلاعات موجود (Update Existing)', 'clubcore' ); ?></label><br>
                                <label><input type="radio" name="import_mode" value="create_only"> <?php esc_html_e( 'فقط ایجاد جدید (Create New Only)', 'clubcore' ); ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'تنظیمات پیامک', 'clubcore' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="send_sms" value="1">
                                <?php esc_html_e( 'ارسال پیامک خوش‌آمدگویی به مشتریان وارد شده', 'clubcore' ); ?>
                            </label>
                            <p class="description warning"><?php esc_html_e( 'هشدار: در صورت انتخاب، ممکن است هزینه زیادی برای پیامک کسر شود.', 'clubcore' ); ?></p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary"><?php esc_html_e( 'شروع درون‌ریزی', 'clubcore' ); ?></button>
                </p>
            </form>
            
            <div id="clubcore-import-preview"></div>
            <div id="clubcore-import-progress" class="hidden"></div>
        </div>

        <div class="clubcore-card">
            <h2><?php esc_html_e( 'برون‌بری (Export)', 'clubcore' ); ?></h2>
            <form id="clubcore-export-form" method="post">
                <?php wp_nonce_field( 'clubcore_export', 'clubcore_nonce' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'محدوده برون‌بری', 'clubcore' ); ?></th>
                        <td>
                            <select name="export_scope">
                                <option value="all"><?php esc_html_e( 'همه اعضا', 'clubcore' ); ?></option>
                                <option value="search"><?php esc_html_e( 'نتیجه جستجوی فعلی', 'clubcore' ); ?></option>
                                <option value="filters"><?php esc_html_e( 'فیلترهای فعلی', 'clubcore' ); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" name="export_format" value="csv" class="button button-primary"><?php esc_html_e( 'دریافت خروجی CSV', 'clubcore' ); ?></button>
                    <button type="submit" name="export_format" value="xlsx" class="button button-primary"><?php esc_html_e( 'دریافت خروجی XLSX', 'clubcore' ); ?></button>
                </p>
            </form>
        </div>
    </div>
</div>
