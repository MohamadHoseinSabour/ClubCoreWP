<?php
declare(strict_types=1);

namespace ClubCore\Application\Service;

use ClubCore\Domain\Contract\SmsProviderInterface;
use ClubCore\Domain\Contract\SmsLogRepositoryInterface;
use ClubCore\Domain\Contract\SmsResult;
use ClubCore\Domain\Exception\SmsException;
use ClubCore\Infrastructure\Pattern\PatternParser;
use ClubCore\Infrastructure\Pattern\VariableRegistry;
use ClubCore\Domain\Contract\AuditLoggerInterface;
use ClubCore\Domain\Model\Member;

class SmsService
{
    public function __construct(
        private readonly SmsProviderInterface $provider,
        private readonly PatternParser $parser,
        private readonly VariableRegistry $registry,
        private readonly SmsLogRepositoryInterface $logRepository,
        private readonly AuditLoggerInterface $auditLogger
    ) {}

    public function sendMemberSms(Member $member, string $requestType = 'welcome'): SmsResult
    {
        $patternCode = get_option("clubcore_sms_pattern_{$requestType}", '');
        $template = get_option("clubcore_sms_template_{$requestType}", '');

        if (!$patternCode) {
            throw SmsException::providerError('missing_pattern', 'Pattern code is not configured.');
        }

        $variableNames = $this->parser->parse($template);
        
        $memberData = [
            'first_name' => $member->first_name ?? '',
            'last_name' => $member->last_name ?? '',
            'phone' => $member->phone ?? '',
        ];

        $resolvedValues = $this->parser->resolveValues($variableNames, $memberData);

        $result = $this->provider->sendPattern(
            $member->phone ?? '',
            $patternCode,
            $resolvedValues
        );

        $this->logRepository->create([
            'member_id' => $member->id ?? 0,
            'phone' => $member->phone ?? '',
            'type' => $requestType,
            'status' => $result->isSuccess() ? 'success' : 'failure',
            'provider_id' => $result->getProviderId(),
            'error_message' => $result->getErrorMessage(),
            'sent_at' => current_time('mysql', true),
        ]);

        if (isset($member->id) && $member->id > 0) {
            update_user_meta($member->id, '_clubcore_last_sms_status', $result->isSuccess() ? 'success' : 'failure');
            update_user_meta($member->id, '_clubcore_last_sms_sent_at', current_time('mysql', true));
        }

        $this->auditLogger->log('sms_sent', [
            'member_id' => $member->id ?? 0,
            'type' => $requestType,
            'status' => $result->isSuccess() ? 'success' : 'failure',
        ]);

        return $result;
    }

    public function sendTestSms(string $phone, string $firstName, string $lastName): SmsResult
    {
        if (empty($phone)) {
            throw new \InvalidArgumentException('Phone number is required.');
        }

        $patternCode = get_option('clubcore_sms_pattern_test', '');
        if (!$patternCode) {
            throw SmsException::providerError('missing_pattern', 'Test pattern code is not configured.');
        }

        $memberData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
        ];

        $template = get_option('clubcore_sms_template_test', '');
        $variableNames = $this->parser->parse($template);
        
        $resolvedValues = $this->parser->resolveValues($variableNames, $memberData);

        $result = $this->provider->sendPattern($phone, $patternCode, $resolvedValues);

        $this->logRepository->create([
            'phone' => $phone,
            'type' => 'test',
            'status' => $result->isSuccess() ? 'success' : 'failure',
            'provider_id' => $result->getProviderId(),
            'error_message' => $result->getErrorMessage(),
            'sent_at' => current_time('mysql', true),
        ]);

        $this->auditLogger->log('test_sms_sent', [
            'phone' => $phone,
            'status' => $result->isSuccess() ? 'success' : 'failure',
        ]);

        return $result;
    }
}
