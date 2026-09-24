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
use ClubCore\Domain\Entity\Member;

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
        // Use the unified pattern settings stored in clubcore_pattern_body_id / clubcore_pattern_template
        $patternCode = get_option('clubcore_pattern_body_id', '');
        $template = get_option('clubcore_pattern_template', '');

        if (!$patternCode) {
            throw SmsException::providerError('missing_pattern', 'Pattern code is not configured.');
        }

        $variableNames = $this->parser->parse($template);
        
        $phone = method_exists($member, 'getPhoneNormalized') ? $member->getPhoneNormalized() : ($member->phone ?? '');
        $firstName = method_exists($member, 'getFirstName') ? $member->getFirstName() : ($member->first_name ?? '');
        $lastName = method_exists($member, 'getLastName') ? $member->getLastName() : ($member->last_name ?? '');
        $memberId = method_exists($member, 'getId') ? $member->getId() : ($member->id ?? 0);

        $memberData = [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'name'       => trim($firstName . ' ' . $lastName),
            'phone'      => $phone,
        ];

        $resolvedValues = $this->parser->resolveValues($variableNames, $memberData);

        $result = $this->provider->sendPattern(
            $phone,
            $patternCode,
            $resolvedValues
        );

        $this->logRepository->create([
            'member_id'     => $memberId,
            'phone'         => $phone,
            'type'          => $requestType,
            'status'        => $result->isSuccess() ? 'success' : 'failure',
            'provider_id'   => $result->getProviderId(),
            'error_message' => $result->getErrorMessage(),
            'sent_at'       => current_time('mysql', true),
        ]);

        if ($memberId > 0) {
            update_user_meta($memberId, '_clubcore_last_sms_status', $result->isSuccess() ? 'success' : 'failure');
            update_user_meta($memberId, '_clubcore_last_sms_sent_at', current_time('mysql', true));
        }

        $this->auditLogger->log('sms_sent', [
            'member_id' => $memberId,
            'type'      => $requestType,
            'status'    => $result->isSuccess() ? 'success' : 'failure',
        ]);

        return $result;
    }

    public function sendTestSms(string $phone, string $firstName, string $lastName): SmsResult
    {
        if (empty($phone)) {
            throw new \InvalidArgumentException('Phone number is required.');
        }

        $patternCode = get_option('clubcore_pattern_body_id', '');
        if (!$patternCode) {
            throw SmsException::providerError('missing_pattern', __('کد الگو (Pattern Body ID) تنظیم نشده است. لطفاً در تنظیمات > الگوی پیامک آن را وارد کنید.', 'clubcore'));
        }

        $memberData = [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'phone'      => $phone,
        ];

        $template = get_option('clubcore_pattern_template', '');

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
