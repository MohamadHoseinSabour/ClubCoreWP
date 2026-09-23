<?php

declare(strict_types=1);

namespace ClubCore\Application\Service;

use ClubCore\Domain\Contract\AuditLoggerInterface;
use ClubCore\Domain\Contract\MemberRepositoryInterface;
use ClubCore\Domain\Entity\AuditEntry;
use ClubCore\Domain\Entity\ImportJob;
use ClubCore\Infrastructure\Export\CsvExporter;
use ClubCore\Infrastructure\Export\XlsxExporter;
use ClubCore\Infrastructure\Import\ClipboardParser;
use ClubCore\Infrastructure\Import\CsvParser;
use ClubCore\Infrastructure\Import\ImportProcessor;
use ClubCore\Infrastructure\Import\XlsxParser;

/**
 * High-level Import/Export Application Service.
 *
 * @package ClubCore\Application\Service
 */
class ImportService
{
    private CsvParser $csvParser;
    private ClipboardParser $clipboardParser;
    private XlsxParser $xlsxParser;

    public function __construct(
        private readonly ImportProcessor $importProcessor,
        private readonly MemberRepositoryInterface $memberRepository,
        private readonly AuditLoggerInterface $auditLogger
    ) {
        $this->csvParser = new CsvParser();
        $this->clipboardParser = new ClipboardParser();
        $this->xlsxParser = new XlsxParser();
    }

    /**
     * Preview uploaded file or clipboard text.
     */
    public function preview(string $sourceType, ?string $filePath = null, ?string $rawText = null): array
    {
        $rows = $this->loadRows($sourceType, $filePath, $rawText, 50); // limit preview parsing to 50 rows
        return $this->importProcessor->preview($rows);
    }

    /**
     * Execute full import.
     */
    public function execute(
        string $sourceType,
        ?string $filePath,
        ?string $rawText,
        array $mapping,
        string $mode,
        bool $dryRun,
        bool $sendSms
    ): array {
        $this->auditLogger->log(
            AuditEntry::ACTION_IMPORT_STARTED,
            AuditEntry::OBJECT_IMPORT,
            0,
            AuditEntry::RESULT_SUCCESS,
            wp_json_encode(['source' => $sourceType, 'mode' => $mode, 'dry_run' => $dryRun])
        );

        $rows = $this->loadRows($sourceType, $filePath, $rawText, 0); // all rows
        if (empty($rows)) {
            return [
                'success' => false,
                'message' => __('هیچ داده‌ای برای وارد کردن یافت نشد.', 'clubcore'),
            ];
        }

        // Exclude header row if it exists
        $dataRows = array_slice($rows, 1);

        $result = $this->importProcessor->processBatch(
            $dataRows,
            $mapping,
            $mode,
            $dryRun,
            $sendSms
        );

        $this->auditLogger->log(
            AuditEntry::ACTION_IMPORT_COMPLETED,
            AuditEntry::OBJECT_IMPORT,
            0,
            AuditEntry::RESULT_SUCCESS,
            wp_json_encode([
                'processed' => $result['processed'],
                'success' => $result['success'],
                'failed' => $result['failed'],
                'skipped' => $result['skipped'],
            ])
        );

        // Clean up temp file if exists
        if ($filePath && file_exists($filePath) && str_starts_with($filePath, sys_get_temp_dir())) {
            @unlink($filePath);
        }

        return [
            'success' => true,
            'stats' => $result,
        ];
    }

    /**
     * Export members based on current filters.
     */
    public function export(string $format = 'csv', array $filters = []): void
    {
        $members = $this->memberRepository->list([
            'per_page' => 10000,
            'filters' => $filters,
        ]);

        $this->auditLogger->log(
            AuditEntry::ACTION_EXPORT_CREATED,
            AuditEntry::OBJECT_EXPORT,
            0,
            AuditEntry::RESULT_SUCCESS,
            wp_json_encode(['format' => $format, 'count' => count($members)])
        );

        if ($format === 'xlsx') {
            $exporter = new XlsxExporter();
            $exporter->export($members);
        } else {
            $filename = 'customer-club-members-' . gmdate('Y-m-d') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $exporter = new CsvExporter();
            $exporter->export($members);
            exit;
        }
    }

    /**
     * Download CSV template.
     */
    public function downloadTemplate(): void
    {
        $filename = 'customer-club-import-template.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $stream = fopen('php://output', 'w');
        fputs($stream, "\xEF\xBB\xBF"); // UTF-8 BOM
        fputcsv($stream, ['phone', 'first_name', 'last_name', 'email']);
        fputcsv($stream, ['09121234567', 'علی', 'محمدی', 'ali@example.com']);
        fputcsv($stream, ['09359876543', 'سارا', 'رضایی', '']);
        fclose($stream);
        exit;
    }

    private function loadRows(string $sourceType, ?string $filePath, ?string $rawText, int $limit): array
    {
        return match ($sourceType) {
            'clipboard' => $this->clipboardParser->parse((string)$rawText, $limit),
            'xlsx' => $filePath ? $this->xlsxParser->parse($filePath, $limit) : [],
            default => $filePath ? $this->csvParser->parse($filePath, $limit) : [],
        };
    }
}
