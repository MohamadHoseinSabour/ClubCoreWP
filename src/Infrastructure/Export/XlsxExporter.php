<?php

declare(strict_types=1);

namespace ClubCore\Infrastructure\Export;

use ClubCore\Domain\Entity\Member;

/**
 * XLSX Exporter using PhpSpreadsheet when available.
 *
 * @package ClubCore\Infrastructure\Export
 */
class XlsxExporter
{
    /**
     * Export members to an XLSX download stream or file.
     *
     * @param Member[] $members
     * @param string|null $savePath If null, outputs directly to browser.
     * @throws \RuntimeException
     */
    public function export(array $members, ?string $savePath = null): void
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            throw new \RuntimeException(__('کتابخانه اکسل (PhpSpreadsheet) روی هاست فعال نیست. لطفاً از خروجی CSV استفاده کنید.', 'clubcore'));
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('اعضای باشگاه', 'clubcore'));
        $sheet->setRightToLeft(true); // RTL support in Excel

        // Headers
        $headers = [
            'A1' => __('شناسه عضو', 'clubcore'),
            'B1' => __('شناسه کاربر', 'clubcore'),
            'C1' => __('شماره موبایل', 'clubcore'),
            'D1' => __('نام', 'clubcore'),
            'E1' => __('نام خانوادگی', 'clubcore'),
            'F1' => __('ایمیل', 'clubcore'),
            'G1' => __('وضعیت', 'clubcore'),
            'H1' => __('منبع', 'clubcore'),
            'I1' => __('تاریخ ثبت‌نام', 'clubcore'),
            'J1' => __('وضعیت پیامک', 'clubcore'),
            'K1' => __('تاریخ پیامک', 'clubcore'),
            'L1' => __('ووکامرس', 'clubcore'),
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        $rowIndex = 2;
        foreach ($members as $member) {
            $sheet->setCellValue('A' . $rowIndex, $member->getId());
            $sheet->setCellValue('B' . $rowIndex, $member->getUserId() ?: '-');
            $sheet->setCellValueExplicit('C' . $rowIndex, $member->getPhoneDisplay(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $rowIndex, $member->getFirstName());
            $sheet->setCellValue('E' . $rowIndex, $member->getLastName());
            $sheet->setCellValue('F' . $rowIndex, $member->getEmail());
            $sheet->setCellValue('G' . $rowIndex, $member->getMembershipStatus());
            $sheet->setCellValue('H' . $rowIndex, $member->getMembershipSource()->label());
            $sheet->setCellValue('I' . $rowIndex, $member->getMembershipCreatedAt());
            $sheet->setCellValue('J' . $rowIndex, $member->getLastSmsStatus() ?: '-');
            $sheet->setCellValue('K' . $rowIndex, $member->getLastSmsSentAt() ?: '-');
            $sheet->setCellValue('L' . $rowIndex, $member->isWoocommerceLinked() ? __('بله', 'clubcore') : __('خیر', 'clubcore'));

            $rowIndex++;
        }

        // Auto-fit columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        if ($savePath !== null) {
            $writer->save($savePath);
        } else {
            $filename = 'customer-club-members-' . gmdate('Y-m-d') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;
        }
    }
}
