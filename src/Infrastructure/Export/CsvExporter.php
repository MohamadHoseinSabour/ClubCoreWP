<?php

declare(strict_types=1);

namespace ClubCore\Infrastructure\Export;

use ClubCore\Domain\Entity\Member;

/**
 * CSV Exporter with Formula Injection protection and UTF-8 BOM for Persian characters.
 *
 * @package ClubCore\Infrastructure\Export
 */
class CsvExporter
{
    /**
     * Export members to CSV output stream.
     *
     * @param Member[] $members
     * @param resource|null $outputStream Output stream (defaults to php://output)
     */
    public function export(array $members, $outputStream = null): void
    {
        $stream = $outputStream ?? fopen('php://output', 'w');

        // Write UTF-8 BOM for Excel Persian text support
        fputs($stream, "\xEF\xBB\xBF");

        // Headers
        $headers = [
            __('شناسه عضو', 'clubcore'),
            __('شناسه کاربر وردپرس', 'clubcore'),
            __('شماره موبایل', 'clubcore'),
            __('نام', 'clubcore'),
            __('نام خانوادگی', 'clubcore'),
            __('ایمیل', 'clubcore'),
            __('وضعیت عضویت', 'clubcore'),
            __('منبع عضویت', 'clubcore'),
            __('تاریخ عضویت', 'clubcore'),
            __('وضعیت آخرین پیامک', 'clubcore'),
            __('تاریخ آخرین پیامک', 'clubcore'),
            __('متصل به ووکامرس', 'clubcore'),
        ];
        fputcsv($stream, $headers);

        foreach ($members as $member) {
            $row = [
                $member->getId(),
                $member->getUserId() ?: '-',
                $this->sanitizeFormula($member->getPhoneDisplay()),
                $this->sanitizeFormula($member->getFirstName()),
                $this->sanitizeFormula($member->getLastName()),
                $this->sanitizeFormula($member->getEmail()),
                $member->getMembershipStatus(),
                $member->getMembershipSource()->label(),
                $member->getMembershipCreatedAt(),
                $member->getLastSmsStatus() ?: '-',
                $member->getLastSmsSentAt() ?: '-',
                $member->isWoocommerceLinked() ? __('بله', 'clubcore') : __('خیر', 'clubcore'),
            ];

            fputcsv($stream, $row);
        }

        if ($outputStream === null) {
            fclose($stream);
        }
    }

    /**
     * Sanitize cell value against CSV / Spreadsheet Formula Injection.
     * Cells starting with =, +, -, @, \t, \r could execute code in Excel.
     *
     * @param string $val
     * @return string
     */
    private function sanitizeFormula(string $val): string
    {
        $val = trim($val);
        if ($val === '') {
            return '';
        }

        $firstChar = substr($val, 0, 1);
        if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $val;
        }

        return $val;
    }
}
