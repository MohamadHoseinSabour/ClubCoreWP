<?php

declare(strict_types=1);

namespace ClubCore\Infrastructure\Import;

use ClubCore\Application\Dto\CreateMemberRequest;
use ClubCore\Application\Dto\ImportRowDto;
use ClubCore\Application\UseCase\CreateMemberUseCase;
use ClubCore\Domain\Contract\AuditLoggerInterface;
use ClubCore\Domain\Contract\MemberRepositoryInterface;
use ClubCore\Domain\Entity\ImportJob;
use ClubCore\Domain\Entity\Member;
use ClubCore\Domain\ValueObject\MembershipSource;

/**
 * Handles batch-processing, preview, dry-run, and row resilience for imports.
 *
 * @package ClubCore\Infrastructure\Import
 */
class ImportProcessor
{
    public function __construct(
        private readonly MemberRepositoryInterface $memberRepository,
        private readonly CreateMemberUseCase $createMemberUseCase,
        private readonly AuditLoggerInterface $auditLogger
    ) {}

    /**
     * Preview raw rows and detect column mapping.
     *
     * @param array<int, array<string>> $rawRows
     * @param array<string, int> $columnMapping [ 'phone' => col_index, 'first_name' => col_index, ... ]
     * @return array{
     *     total_rows: int,
     *     sample_rows: array,
     *     valid_count: int,
     *     invalid_count: int,
     *     duplicate_count: int,
     *     mapping_detected: array
     * }
     */
    public function preview(array $rawRows, array $columnMapping = []): array
    {
        if (empty($rawRows)) {
            return [
                'total_rows' => 0,
                'sample_rows' => [],
                'valid_count' => 0,
                'invalid_count' => 0,
                'duplicate_count' => 0,
                'mapping_detected' => [],
            ];
        }

        // If no mapping provided, guess from header row
        $hasHeader = true;
        if (empty($columnMapping)) {
            $columnMapping = $this->guessMapping($rawRows[0]);
        }

        $dataRows = $hasHeader ? array_slice($rawRows, 1) : $rawRows;
        $total = count($dataRows);
        $valid = 0;
        $invalid = 0;
        $duplicates = 0;
        $sample = [];

        $seenInFile = [];

        foreach ($dataRows as $idx => $row) {
            $rowDto = $this->mapRow($idx + 1, $row, $columnMapping);

            if (!$rowDto->isValid) {
                $invalid++;
            } else {
                $phoneNorm = $rowDto->phone->getNormalized();
                if (isset($seenInFile[$phoneNorm]) || $this->memberRepository->existsByPhone($phoneNorm)) {
                    $duplicates++;
                } else {
                    $valid++;
                }
                $seenInFile[$phoneNorm] = true;
            }

            if (count($sample) < 5) {
                $sample[] = [
                    'row' => $idx + 1,
                    'phone' => $rowDto->rawPhone,
                    'first_name' => $rowDto->firstName,
                    'last_name' => $rowDto->lastName,
                    'email' => $rowDto->email,
                    'is_valid' => $rowDto->isValid,
                    'error' => $rowDto->errorMessage,
                ];
            }
        }

        return [
            'total_rows' => $total,
            'sample_rows' => $sample,
            'valid_count' => $valid,
            'invalid_count' => $invalid,
            'duplicate_count' => $duplicates,
            'mapping_detected' => $columnMapping,
        ];
    }

    /**
     * Process an import batch.
     *
     * @param array<int, array<string>> $dataRows
     * @param array<string, int> $columnMapping
     * @param string $mode (skip_duplicates, update_existing, create_new_only)
     * @param bool $dryRun
     * @param bool $sendSms
     * @return array{
     *     processed: int,
     *     success: int,
     *     failed: int,
     *     skipped: int,
     *     errors: array
     * }
     */
    public function processBatch(
        array $dataRows,
        array $columnMapping,
        string $mode = ImportJob::MODE_SKIP_DUPLICATES,
        bool $dryRun = false,
        bool $sendSms = false
    ): array {
        $stats = [
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        foreach ($dataRows as $idx => $row) {
            $stats['processed']++;
            $rowDto = $this->mapRow($idx + 1, $row, $columnMapping);

            if (!$rowDto->isValid) {
                $stats['failed']++;
                $stats['errors'][] = [
                    'row' => $rowDto->rowNumber,
                    'phone' => $rowDto->rawPhone,
                    'error' => $rowDto->errorMessage,
                    'suggested_fix' => __('بررسی فرمت شماره موبایل یا ایمیل', 'clubcore'),
                ];
                continue;
            }

            $phoneNorm = $rowDto->phone->getNormalized();
            $existing = $this->memberRepository->findByPhone($phoneNorm);

            if ($existing !== null) {
                if ($mode === ImportJob::MODE_SKIP_DUPLICATES) {
                    $stats['skipped']++;
                    continue;
                }

                if ($mode === ImportJob::MODE_UPDATE_EXISTING) {
                    if (!$dryRun) {
                        if ($rowDto->firstName !== '') $existing->setFirstName($rowDto->firstName);
                        if ($rowDto->lastName !== '') $existing->setLastName($rowDto->lastName);
                        if ($rowDto->email !== '') $existing->setEmail($rowDto->email);
                        $existing->touchUpdated();
                        $this->memberRepository->update($existing);
                    }
                    $stats['success']++;
                    continue;
                }

                if ($mode === ImportJob::MODE_CREATE_NEW_ONLY) {
                    $stats['skipped']++;
                    $stats['errors'][] = [
                        'row' => $rowDto->rowNumber,
                        'phone' => $rowDto->rawPhone,
                        'error' => __('این شماره قبلاً در سیستم ثبت شده است.', 'clubcore'),
                        'suggested_fix' => __('انتخاب حالت بروزرسانی یا نادیده‌گرفتن تکراری‌ها', 'clubcore'),
                    ];
                    continue;
                }
            }

            // Create new member
            if ($dryRun) {
                $stats['success']++;
            } else {
                $req = new CreateMemberRequest(
                    phone: $rowDto->rawPhone,
                    firstName: $rowDto->firstName,
                    lastName: $rowDto->lastName,
                    email: $rowDto->email,
                    source: MembershipSource::Import,
                    sendSms: $sendSms,
                    requestType: 'import'
                );

                $res = $this->createMemberUseCase->execute($req);
                if ($res->status === 'created') {
                    $stats['success']++;
                } else {
                    $stats['failed']++;
                    $stats['errors'][] = [
                        'row' => $rowDto->rowNumber,
                        'phone' => $rowDto->rawPhone,
                        'error' => $res->message,
                        'suggested_fix' => __('بررسی اطلاعات کاربر', 'clubcore'),
                    ];
                }
            }
        }

        return $stats;
    }

    /**
     * Map a raw row array to an ImportRowDto based on index mapping.
     */
    private function mapRow(int $rowNum, array $row, array $mapping): ImportRowDto
    {
        $phone = isset($mapping['phone']) && isset($row[$mapping['phone']]) ? (string)$row[$mapping['phone']] : '';
        $firstName = isset($mapping['first_name']) && isset($row[$mapping['first_name']]) ? (string)$row[$mapping['first_name']] : '';
        $lastName = isset($mapping['last_name']) && isset($row[$mapping['last_name']]) ? (string)$row[$mapping['last_name']] : '';
        $email = isset($mapping['email']) && isset($row[$mapping['email']]) ? (string)$row[$mapping['email']] : '';

        return new ImportRowDto($rowNum, $phone, $firstName, $lastName, $email, $row);
    }

    /**
     * Heuristically guess column indexes from first row headers.
     */
    private function guessMapping(array $headers): array
    {
        $mapping = [];

        foreach ($headers as $idx => $head) {
            $h = mb_strtolower(trim((string)$head));

            if (in_array($h, ['phone', 'mobile', 'شماره', 'موبایل', 'تلفن', 'cellphone'], true)) {
                $mapping['phone'] = $idx;
            } elseif (in_array($h, ['first_name', 'name', 'نام', 'fname', 'first name'], true)) {
                $mapping['first_name'] = $idx;
            } elseif (in_array($h, ['last_name', 'family', 'نام خانوادگی', 'lname', 'فامیل', 'last name'], true)) {
                $mapping['last_name'] = $idx;
            } elseif (in_array($h, ['email', 'e-mail', 'ایمیل', 'پست الکترونیک', 'mail'], true)) {
                $mapping['email'] = $idx;
            }
        }

        // Fallbacks if not recognized by names
        if (!isset($mapping['phone']) && count($headers) > 0) {
            $mapping['phone'] = 0; // Assume first column is phone
        }
        if (!isset($mapping['first_name']) && count($headers) > 1) {
            $mapping['first_name'] = 1;
        }
        if (!isset($mapping['last_name']) && count($headers) > 2) {
            $mapping['last_name'] = 2;
        }

        return $mapping;
    }
}
