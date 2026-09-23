<?php
declare(strict_types=1);

namespace ClubCore\Infrastructure\Sms;

use ClubCore\Domain\Contract\SmsProviderInterface;
use ClubCore\Domain\Contract\SmsResult;
use ClubCore\Domain\Exception\SmsException;

class MelipayamakProvider implements SmsProviderInterface
{
    private const CLASSIC_API_URL = 'https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber';
    private const CONSOLE_API_URL = 'https://console.melipayamak.com/api/send/shared/';
    private const GET_CREDIT_URL = 'https://rest.payamak-panel.com/api/SendSMS/GetCredit';
    
    private SmsErrorMapper $errorMapper;

    public function __construct(
        private readonly string $username = '',
        private readonly string $password = '',
        private readonly string $apiToken = '',
        private readonly int $timeout = 30
    ) {
        $this->errorMapper = new SmsErrorMapper();
    }

    public function sendPattern(string $to, string $patternCode, array $variables = []): SmsResult
    {
        $startTime = microtime(true);
        try {
            if ($this->apiToken !== '') {
                return $this->sendConsole($to, $patternCode, $variables, $startTime);
            }
            return $this->sendClassic($to, $patternCode, $variables, $startTime);
        } catch (SmsException $e) {
            return SmsResult::failure($e->getMessage(), $e->getProviderErrorCode(), $e->getType());
        }
    }

    public function getCredit(): float
    {
        $body = [
            'username' => $this->username,
            'password' => $this->password,
        ];

        $response = wp_remote_post(self::GET_CREDIT_URL, [
            'body' => wp_json_encode($body),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => $this->timeout,
        ]);

        $parsed = $this->parseResponse($response);
        return (float) ($parsed['Value'] ?? 0);
    }

    public function getName(): string
    {
        return 'melipayamak';
    }

    public function isConfigured(): bool
    {
        return ($this->username !== '' && $this->password !== '') || $this->apiToken !== '';
    }

    private function sendClassic(string $to, string $patternCode, array $variables, float $startTime): SmsResult
    {
        $text = implode(';', array_values($variables));
        
        $body = [
            'username' => $this->username,
            'password' => $this->password,
            'to' => $to,
            'bodyId' => $patternCode,
            'text' => $text,
        ];

        $response = wp_remote_post(self::CLASSIC_API_URL, [
            'body' => wp_json_encode($body),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => $this->timeout,
        ]);

        $parsed = $this->parseResponse($response);
        
        $status = (string) ($parsed['RetStatus'] ?? '');
        if ($status !== '1') {
            throw $this->mapError($status, true);
        }

        $duration = microtime(true) - $startTime;
        return SmsResult::success((string) ($parsed['Value'] ?? ''));
    }

    private function sendConsole(string $to, string $patternCode, array $variables, float $startTime): SmsResult
    {
        $body = [
            'to' => $to,
            'bodyId' => $patternCode,
            'args' => array_values($variables),
        ];

        $url = self::CONSOLE_API_URL . $this->apiToken;

        $response = wp_remote_post($url, [
            'body' => wp_json_encode($body),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => $this->timeout,
        ]);

        $parsed = $this->parseResponse($response);
        
        $status = (string) ($parsed['RetStatus'] ?? '');
        if ($status !== '1') {
            throw $this->mapError($status, false);
        }

        $duration = microtime(true) - $startTime;
        return SmsResult::success((string) ($parsed['Value'] ?? ''));
    }

    private function parseResponse($response): array
    {
        if (is_wp_error($response)) {
            throw SmsException::providerError('connection_failed', $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw SmsException::providerError('invalid_json', 'Failed to parse provider response.');
        }

        return $decoded;
    }

    private function mapError(string $code, bool $isClassic): SmsException
    {
        if ((int) $code < 0 || $isClassic) {
            return $this->errorMapper->mapClassicError($code);
        }
        
        return $this->errorMapper->mapGeneralError($code);
    }
}
