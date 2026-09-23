<?php

declare(strict_types=1);

namespace ClubCore\Infrastructure\Import;

/**
 * Parses raw text pasted from clipboard (Excel, Google Sheets, TSV, CSV).
 *
 * @package ClubCore\Infrastructure\Import
 */
class ClipboardParser
{
    /**
     * Parse raw clipboard text into array of rows.
     *
     * @param string $rawText
     * @param int $maxRows
     * @return array<int, array<string>>
     */
    public function parse(string $rawText, int $maxRows = 0): array
    {
        $text = trim($rawText);
        if ($text === '') {
            return [];
        }

        // Normalize newlines
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = explode("\n", $text);

        if (empty($lines)) {
            return [];
        }

        // Sample first line to detect delimiter
        $firstLine = $lines[0];
        $delimiter = $this->detectDelimiter($firstLine);

        $rows = [];
        $count = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $cells = str_getcsv($line, $delimiter);
            $rows[] = array_map(fn($c) => trim((string)$c), $cells);
            $count++;

            if ($maxRows > 0 && $count >= $maxRows) {
                break;
            }
        }

        return $rows;
    }

    private function detectDelimiter(string $sample): string
    {
        $delimiters = ["\t", ',', ';', '|'];
        $counts = [];

        foreach ($delimiters as $del) {
            $counts[$del] = substr_count($sample, $del);
        }

        arsort($counts);
        $best = array_key_first($counts);

        return ($counts[$best] > 0) ? $best : "\t";
    }
}
