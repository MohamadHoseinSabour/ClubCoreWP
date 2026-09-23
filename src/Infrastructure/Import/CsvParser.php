<?php

declare(strict_types=1);

namespace ClubCore\Infrastructure\Import;

/**
 * CSV Parser supporting UTF-8, BOM removal, auto delimiter detection (comma, semicolon, tab).
 *
 * @package ClubCore\Infrastructure\Import
 */
class CsvParser
{
    /**
     * Parse CSV file into rows (array of strings).
     *
     * @param string $filePath
     * @param int $maxRows 0 for unlimited
     * @return array<int, array<string>>
     * @throws \RuntimeException
     */
    public function parse(string $filePath, int $maxRows = 0): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException(__('فایل انتخابی یافت نشد یا قابل خواندن نیست.', 'clubcore'));
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException(__('امکان باز کردن فایل وجود ندارد.', 'clubcore'));
        }

        // Read first chunk to detect delimiter and handle BOM
        $sample = fread($handle, 4096);
        rewind($handle);

        // Strip UTF-8 BOM if present
        $bom = pack('H*', 'EFBBBF');
        $firstBytes = fread($handle, 3);
        if ($firstBytes !== $bom) {
            rewind($handle);
        }

        $delimiter = $this->detectDelimiter($sample);
        $rows = [];
        $count = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            // Check for empty row
            if (count($row) === 1 && $row[0] === null) {
                continue;
            }
            // Trim all fields
            $rows[] = array_map(fn($val) => trim((string)$val), $row);
            $count++;

            if ($maxRows > 0 && $count >= $maxRows) {
                break;
            }
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Auto-detect CSV delimiter based on frequency in first chunk.
     *
     * @param string $sample
     * @return string
     */
    private function detectDelimiter(string $sample): string
    {
        $delimiters = [',', ';', "\t", '|'];
        $counts = [];

        foreach ($delimiters as $del) {
            $counts[$del] = substr_count($sample, $del);
        }

        arsort($counts);
        $best = array_key_first($counts);

        return ($counts[$best] > 0) ? $best : ',';
    }
}
