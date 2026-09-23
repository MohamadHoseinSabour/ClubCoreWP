<?php

declare(strict_types=1);

namespace ClubCore\Domain\Entity;

/**
 * Entity representing an Import Job.
 *
 * @package ClubCore\Domain\Entity
 */
class ImportJob
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_COMPLETED_WITH_ERRORS = 'completed_with_errors';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public const MODE_SKIP_DUPLICATES = 'skip_duplicates';
    public const MODE_UPDATE_EXISTING = 'update_existing';
    public const MODE_CREATE_NEW_ONLY = 'create_new_only';

    private int $id = 0;
    private int $createdBy = 0;
    private string $fileName = '';
    private string $sourceType = 'csv';
    private string $status = self::STATUS_PENDING;
    private int $totalRows = 0;
    private int $processedRows = 0;
    private int $successRows = 0;
    private int $failedRows = 0;
    private int $skippedRows = 0;
    private bool $sendSms = false;
    private string $importMode = self::MODE_SKIP_DUPLICATES;
    private ?string $errorSummary = null;
    private ?string $startedAt = null;
    private ?string $completedAt = null;
    private string $createdAt;

    public function __construct(string $sourceType = 'csv', string $fileName = '')
    {
        $this->sourceType = $sourceType;
        $this->fileName = $fileName;
        $this->createdAt = current_time('mysql', true);
        $this->createdBy = get_current_user_id();
    }

    public static function fromRow(object|array $data): self
    {
        $data = (object) $data;
        $job = new self($data->source_type ?? 'csv', $data->file_name ?? '');
        $job->id = (int) ($data->id ?? 0);
        $job->createdBy = (int) ($data->created_by ?? 0);
        $job->status = $data->status ?? self::STATUS_PENDING;
        $job->totalRows = (int) ($data->total_rows ?? 0);
        $job->processedRows = (int) ($data->processed_rows ?? 0);
        $job->successRows = (int) ($data->success_rows ?? 0);
        $job->failedRows = (int) ($data->failed_rows ?? 0);
        $job->skippedRows = (int) ($data->skipped_rows ?? 0);
        $job->sendSms = (bool) ($data->send_sms ?? false);
        $job->importMode = $data->import_mode ?? self::MODE_SKIP_DUPLICATES;
        $job->errorSummary = $data->error_summary ?? null;
        $job->startedAt = $data->started_at ?? null;
        $job->completedAt = $data->completed_at ?? null;
        $job->createdAt = $data->created_at ?? '';
        return $job;
    }

    public function toArray(): array
    {
        return [
            'created_by' => $this->createdBy,
            'file_name' => $this->fileName,
            'source_type' => $this->sourceType,
            'status' => $this->status,
            'total_rows' => $this->totalRows,
            'processed_rows' => $this->processedRows,
            'success_rows' => $this->successRows,
            'failed_rows' => $this->failedRows,
            'skipped_rows' => $this->skippedRows,
            'send_sms' => $this->sendSms ? 1 : 0,
            'import_mode' => $this->importMode,
            'error_summary' => $this->errorSummary,
            'started_at' => $this->startedAt,
            'completed_at' => $this->completedAt,
            'created_at' => $this->createdAt,
        ];
    }

    // Getters & Setters
    public function getId(): int { return $this->id; }
    public function setId(int $id): self { $this->id = $id; return $this; }
    public function getCreatedBy(): int { return $this->createdBy; }
    public function getFileName(): string { return $this->fileName; }
    public function getSourceType(): string { return $this->sourceType; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getTotalRows(): int { return $this->totalRows; }
    public function setTotalRows(int $count): self { $this->totalRows = $count; return $this; }
    public function getProcessedRows(): int { return $this->processedRows; }
    public function setProcessedRows(int $count): self { $this->processedRows = $count; return $this; }
    public function getSuccessRows(): int { return $this->successRows; }
    public function setSuccessRows(int $count): self { $this->successRows = $count; return $this; }
    public function getFailedRows(): int { return $this->failedRows; }
    public function setFailedRows(int $count): self { $this->failedRows = $count; return $this; }
    public function getSkippedRows(): int { return $this->skippedRows; }
    public function setSkippedRows(int $count): self { $this->skippedRows = $count; return $this; }
    public function isSendSms(): bool { return $this->sendSms; }
    public function setSendSms(bool $send): self { $this->sendSms = $send; return $this; }
    public function getImportMode(): string { return $this->importMode; }
    public function setImportMode(string $mode): self { $this->importMode = $mode; return $this; }
    public function getErrorSummary(): ?string { return $this->errorSummary; }
    public function setErrorSummary(?string $summary): self { $this->errorSummary = $summary; return $this; }
    public function getStartedAt(): ?string { return $this->startedAt; }
    public function setStartedAt(?string $time): self { $this->startedAt = $time; return $this; }
    public function getCompletedAt(): ?string { return $this->completedAt; }
    public function setCompletedAt(?string $time): self { $this->completedAt = $time; return $this; }
    public function getCreatedAt(): string { return $this->createdAt; }
}
