<?php

declare(strict_types=1);

namespace ClubCore\Tests\Unit;

use ClubCore\Domain\Contract\SmsResult;
use ClubCore\Infrastructure\Sms\FakeSmsProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for FakeSmsProvider behavior.
 */
class FakeSmsProviderTest extends TestCase
{
    public function testDefaultSuccessSend(): void
    {
        $provider = new FakeSmsProvider();
        $this->assertTrue($provider->isConfigured());
        $this->assertSame('fake', $provider->getName());

        $result = $provider->sendPattern('09121234567', '12345', ['test']);
        $this->assertTrue($result->success);
        $this->assertNotEmpty($result->providerReference);
    }

    public function testConfiguredFailure(): void
    {
        $provider = new FakeSmsProvider();
        $provider->setShouldFail(true, 'INSUFFICIENT_CREDIT');

        $result = $provider->sendPattern('09121234567', '12345', ['test']);
        $this->assertFalse($result->success);
        $this->assertSame('INSUFFICIENT_CREDIT', $result->errorType);
    }

    public function testTracksSentMessagesInMemory(): void
    {
        $provider = new FakeSmsProvider();
        $provider->sendPattern('09121112233', '100', ['علی']);
        $provider->sendPattern('09352223344', '200', ['سارا']);

        $messages = $provider->getSentMessages();
        $this->assertCount(2, $messages);
        $this->assertSame('09121112233', $messages[0]['to']);
        $this->assertSame('09352223344', $messages[1]['to']);
    }
}
