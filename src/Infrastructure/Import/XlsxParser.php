<?php

declare(strict_types=1);

namespace ClubCore\Infrastructure\Import;

/**
 * XLSX Parser using PhpSpreadsheet when available, with security limits.
 *
 * @package ClubCore\Infrastructure\Import
 */
class XlsxParser
{
    /**
     * Parse an XLSX file into tabular rows.
     *
     * @param string $filePath
     * @param int $maxRows
     * @return array<int, array<string>>
     * @throws \RuntimeException
     */
    public function parse(string $filePath, int $maxRows = 0): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException(__('فایل اکسل یافت نشد.', 'clubcore'));
        }

        // Security check: Limit file size to 15MB to prevent memory exhaustion / zip bombs
        if (filesize($filePath) > 15 * 1024 * 1024) {
            throw new \RuntimeException(__('حجم فایل اکسل بیش از حد مجاز (حداکثر ۱۵ مگابایت) است.', 'clubcore'));
        }

        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new \RuntimeException(__('کتابخانه پردازش فایل اکسل (PhpSpreadsheet) روی سرور نصب نیست. لطفاً از فایل CSV استفاده کنید یا دستور composer install را اجرا کنید.', 'clubcore'));
        }

        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            $rows = [];
            $count = 0;

            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $rowCells = [];
                $hasData = false;

                foreach ($cellIterator as $cell) {
                    $val = trim((string) $cell->getValue());
                    if ($val !== '') {
                        $hasData = true;
                    }
                    $rowCells[] = $val;
                }

                if ($hasData) {
                    $rows[] = $rowCells;
                    $count++;

                    if ($maxRows > 0 && $count >= $maxRows) {
                        break;
                    }
                }
            }

            return $rows;
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                sprintf(
                    /* translators: %s: error message */
                    __('خطا در خواندن فایل اکسل: %s', 'clubcore'),
                    $e->getMessage()
                )
            );
        }
    }
}
