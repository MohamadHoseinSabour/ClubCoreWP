<?php
declare(strict_types=1);

namespace ClubCore\Infrastructure\Sms;

use ClubCore\Domain\Exception\SmsException;

class SmsErrorMapper
{
    private const CLASSIC_ERRORS = [
        '-1' => 'disabled',
        '-2' => 'limit',
        '-3' => 'line_not_defined',
        '-4' => 'invalid_body_id',
        '-5' => 'variable_mismatch',
        '-6' => 'internal_error',
        '-7' => 'sender_error',
        '-8' => 'time_restriction',
        '-9' => 'expired_schedule',
        '-10' => 'link_in_variables',
        '-108' => 'ip_blocked',
        '-109' => 'ip_whitelist',
        '-110' => 'require_api_key',
    ];

    private const GENERAL_ERRORS = [
        '0' => 'invalid_auth',
        '2' => 'no_credit',
        '3' => 'daily_limit',
        '6' => 'maintenance',
        '7' => 'filtered_words',
        '10' => 'user_inactive',
        '11' => 'not_sent',
        '12' => 'docs_incomplete',
        '18' => 'invalid_recipient',
        '19' => 'hourly_limit',
    ];

    public function mapClassicError(string $code): SmsException
    {
        $type = self::CLASSIC_ERRORS[$code] ?? 'unknown_error';
        return SmsException::providerError($type, $this->getUserMessage($code), $code);
    }

    public function mapGeneralError(string $code): SmsException
    {
        $type = self::GENERAL_ERRORS[$code] ?? 'unknown_error';
        return SmsException::providerError($type, $this->getUserMessage($code), $code);
    }

    public function isRetryableCode(string $code): bool
    {
        return in_array($code, ['-2', '-6', '-8', '3', '6', '19'], true);
    }

    public function getUserMessage(string $code): string
    {
        return match($code) {
            '-1' => __('سرویس پیامک غیرفعال است.', 'clubcore'),
            '-2' => __('محدودیت در ارسال پیامک.', 'clubcore'),
            '-3' => __('خط ارسال پیامک تعریف نشده است.', 'clubcore'),
            '-4' => __('شناسه متن پیامک نامعتبر است.', 'clubcore'),
            '-5' => __('متغیرهای پیامک با الگو مطابقت ندارند.', 'clubcore'),
            '-6' => __('خطای داخلی سرور پیامک.', 'clubcore'),
            '-7' => __('خطای فرستنده پیامک.', 'clubcore'),
            '-8' => __('ارسال پیامک تنها بین ساعات 7 صبح تا 10 شب مجاز است.', 'clubcore'),
            '-9' => __('زمانبندی ارسال پیامک منقضی شده است.', 'clubcore'),
            '-10' => __('استفاده از لینک در متغیرهای پیامک مجاز نیست.', 'clubcore'),
            '-108' => __('آی‌پی سرور توسط پنل پیامک مسدود شده است.', 'clubcore'),
            '-109' => __('آی‌پی سرور در لیست سفید پنل پیامک نیست.', 'clubcore'),
            '-110' => __('ارسال نیازمند کلید API است.', 'clubcore'),
            '0' => __('اطلاعات ورود نامعتبر است.', 'clubcore'),
            '2' => __('اعتبار پنل پیامک کافی نیست.', 'clubcore'),
            '3' => __('محدودیت ارسال روزانه پیامک.', 'clubcore'),
            '6' => __('سرور پیامک در حال بروزرسانی است.', 'clubcore'),
            '7' => __('متن پیامک حاوی کلمات فیلتر شده است.', 'clubcore'),
            '10' => __('حساب کاربری پنل پیامک غیرفعال است.', 'clubcore'),
            '11' => __('پیامک ارسال نشد.', 'clubcore'),
            '12' => __('مدارک حساب کاربری پنل پیامک ناقص است.', 'clubcore'),
            '18' => __('شماره گیرنده نامعتبر است.', 'clubcore'),
            '19' => __('محدودیت ارسال ساعتی پیامک.', 'clubcore'),
            default => __('خطای نامشخص در ارسال پیامک.', 'clubcore'),
        };
    }
}
