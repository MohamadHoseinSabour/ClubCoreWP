# راهنمای نصب و راه‌اندازی ClubCore

## روش اول: نصب از طریق مخزن گیت و Composer

1. پوشه افزونه را در مسیر افزونه‌های وردپرس کلون کنید:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/MohamadHoseinSabour/ClubCoreWP.git clubcore
   ```
2. وابستگی‌های Composer را نصب نمایید:
   ```bash
   cd clubcore
   composer install --no-dev --optimize-autoloader
   ```
3. به پیشخوان وردپرس رفته و افزونه **ClubCore - Customer Club** را فعال نمایید.
4. جداول دیتابیس به صورت خودکار توسط MigrationManager ایجاد خواهند شد.

## روش دوم: نصب از طریق فایل فشرده (ZIP)

1. فایل پکیج شده `clubcore.zip` را دانلود کنید.
2. از منوی **افزونه‌ها > افزودن افزونه جدید > بارگذاری افزونه**، فایل ZIP را انتخاب و نصب نمایید.
3. افزونه را فعال کنید.
