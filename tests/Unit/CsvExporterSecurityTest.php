<?php

declare(strict_types=1);

namespace ClubCore\Tests\Unit;

use ClubCore\Domain\Entity\Member;
use ClubCore\Domain\ValueObject\MembershipSource;
use ClubCore\Domain\ValueObject\PhoneNumber;
use ClubCore\Infrastructure\Export\CsvExporter;
use PHPUnit\Framework\TestCase;

/**
 * Security tests for CSV Formula Injection mitigation.
 */
class CsvExporterSecurityTest extends TestCase
{
    public function testSanitizesDangerousFormulaPrefixes(): void
    {
        $member = new Member(new PhoneNumber('09121112233'), MembershipSource::Admin);
        $member->setFirstName('=cmd|"/C calc"!A0');
        $member->setLastName('+123456');
        $member->setEmail('@malicious.com');

        $exporter = new CsvExporter();
        $stream = fopen('php://memory', 'r+');
        $exporter->export([$member], $stream);

        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        // Assert that dangerous characters are prepended with single-quote escaping
        $this->assertStringContainsString("''=cmd", $content);
        $this->assertStringContainsString("'+123456", $content);
        $this->assertStringContainsString("'@malicious.com", $content);
    }
}
