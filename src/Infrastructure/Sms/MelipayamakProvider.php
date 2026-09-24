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

    public function sendPattern(string $to, string|int $patternCode, array $variables = []): SmsResult
    {
        $startTime = microtime(true);
        $patternCodeStr = (string) $patternCode;
        try {
            if ($this->apiToken !== '') {
                return $this->sendConsole($to, $patternCodeStr, $variables, $startTime);
            }
            return $this->sendClassic($to, $patternCodeStr, $variables, $startTime);
        } catch (SmsException $e) {
            return SmsResult::failure(
                $e->getErrorType(),
                $e->getUserMessage() ?: $e->getMessage(),
                $e->getProviderCode(),
                $e->getProviderMessage()
            );
        }
    }

    public function getCredit(): float
    {
        // Console API mode (API token)
        if ($this->apiToken !== '') {
            return $this->getCreditConsole();
        }

        // Classic API mode (username/password)
        $body = [
            'username' => $this->username,
            'password' => $this->password,
        ];

        $response = wp_remote_post(self::GET_CREDIT_URL, [
            'body'      => wp_json_encode($body),
            'headers'   => ['Content-Type' => 'application/json'],
            'timeout'   => $this->timeout,
            'sslverify' => $this->shouldVerifySsl(),
        ]);

        $parsed = $this->parseResponse($response);
        return (float) ($parsed['Value'] ?? 0);
    }

    private function getCreditConsole(): float
    {
        // Melipayamak console API credit endpoint
        $url = 'https://console.melipayamak.com/api/send/shared/' . $this->apiToken . '/credit';

        $response = wp_remote_get($url, [
            'headers'   => ['Content-Type' => 'application/json'],
            'timeout'   => $this->timeout,
            'sslverify' => $this->shouldVerifySsl(),
        ]);

        if (is_wp_error($response)) {
            throw SmsException::providerError('connection_failed', $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($decoded['credit'])) {
            return (float) $decoded['credit'];
        }

        // If credit endpoint not available, do a connection test instead
        // Return 0 but don't throw - this means "connected but credit unknown"
        return -1.0;
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
            'body'      => wp_json_encode($body),
            'headers'   => ['Content-Type' => 'application/json'],
            'timeout'   => $this->timeout,
            'sslverify' => $this->shouldVerifySsl(),
        ]);

        $parsed = $this->parseResponse($response);
        
        $status = (string) ($parsed['RetStatus'] ?? '');
        if ($status !== '1') {
            throw $this->mapError($status, true);
        }

        $duration = microtime(true) - $startTime;
        return SmsResult::success((string) ($parsed['Value'] ?? ''), $duration);
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
            'body'      => wp_json_encode($body),
            'headers'   => ['Content-Type' => 'application/json'],
            'timeout'   => $this->timeout,
            'sslverify' => $this->shouldVerifySsl(),
        ]);

        $parsed = $this->parseResponse($response);

        // Melipayamak console API returns {"status":"ارسال موفق","recId":"..."} on success
        // or {"status":"ارسال ناموفق"} / {"status":"..."} on failure
        $statusText = (string) ($parsed['status'] ?? '');
        $recId = (string) ($parsed['recId'] ?? '');

        // Success indicators from Melipayamak console API
        $successKeywords = ['موفق', 'ارسال شد', 'success', 'ok'];
        $isSuccess = false;
        foreach ($successKeywords as $keyword) {
            if (mb_stripos($statusText, $keyword) !== false) {
                $isSuccess = true;
                break;
            }
        }

        // Also check if recId is present and non-zero (indicates success)
        if (!$isSuccess && !empty($recId) && $recId !== '0') {
            $isSuccess = true;
        }

        if (!$isSuccess) {
            // Try legacy RetStatus format as fallback
            $retStatus = (string) ($parsed['RetStatus'] ?? '');
            if ($retStatus === '1') {
                $isSuccess = true;
                $recId = (string) ($parsed['Value'] ?? '');
            }
        }

        if (!$isSuccess) {
            throw $this->errorMapper->mapGeneralError('11'); // not_sent
        }

        $duration = microtime(true) - $startTime;
        return SmsResult::success($recId, $duration);
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

    /**
     * Determine if SSL certificate verification should be performed.
     * Disables verification on local development environments (localhost, 127.0.0.1).
     */
    private function shouldVerifySsl(): bool
    {
        if (defined('CLUBCORE_DISABLE_SSL_VERIFY') && CLUBCORE_DISABLE_SSL_VERIFY) {
            return false;
        }

        // Auto-detect local environment
        $siteUrl = function_exists('get_site_url') ? get_site_url() : ($_SERVER['HTTP_HOST'] ?? '');
        $isLocal = (
            str_contains((string) $siteUrl, 'localhost') ||
            str_contains((string) $siteUrl, '127.0.0.1') ||
            str_contains((string) $siteUrl, '.local') ||
            str_contains((string) $siteUrl, '.test')
        );

        return !$isLocal;
    }
}
