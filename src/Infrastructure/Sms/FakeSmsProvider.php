<?php
declare(strict_types=1);

namespace ClubCore\Infrastructure\Sms;

use ClubCore\Domain\Contract\SmsProviderInterface;
use ClubCore\Domain\Contract\SmsResult;

class FakeSmsProvider implements SmsProviderInterface
{
    private ?SmsResult $nextResult = null;
    private bool $shouldFail = false;
    private string $errorType = 'unknown';
    
    /** @var array<int, array> */
    private array $sentMessages = [];

    public function setNextResult(SmsResult $result): void
    {
        $this->nextResult = $result;
    }

    public function setShouldFail(bool $fail, string $errorType = 'unknown'): void
    {
        $this->shouldFail = $fail;
        $this->errorType = $errorType;
    }

    public function sendPattern(string $to, string|int $patternCode, array $variables = []): SmsResult
    {
        $this->sentMessages[] = [
            'to' => $to,
            'patternCode' => (string) $patternCode,
            'variables' => $variables,
            'time' => time(),
        ];

        if ($this->shouldFail) {
            return SmsResult::failure('Simulated failure', '', $this->errorType);
        }

        if ($this->nextResult !== null) {
            $result = $this->nextResult;
            $this->nextResult = null;
            return $result;
        }

        return SmsResult::success('fake_rec_' . uniqid());
    }

    public function getCredit(): float
    {
        return 999999.0;
    }

    public function getName(): string
    {
        return 'fake';
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function getSentMessages(): array
    {
        return $this->sentMessages;
    }

    public function getLastSentMessage(): ?array
    {
        $count = count($this->sentMessages);
        return $count > 0 ? $this->sentMessages[$count - 1] : null;
    }

    public function resetSentMessages(): void
    {
        $this->sentMessages = [];
        $this->nextResult = null;
        $this->shouldFail = false;
    }
}
