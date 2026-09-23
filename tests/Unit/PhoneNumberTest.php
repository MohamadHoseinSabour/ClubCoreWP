<?php

declare(strict_types=1);

namespace ClubCore\Tests\Unit;

use ClubCore\Domain\Exception\InvalidPhoneException;
use ClubCore\Domain\ValueObject\PhoneNumber;
use PHPUnit\Framework\TestCase;

/**
 * Tests for PhoneNumber Value Object.
 */
class PhoneNumberTest extends TestCase
{
    public function testValidCanonicalNumber(): void
    {
        $phone = new PhoneNumber('09123456789');
        $this->assertSame('09123456789', $phone->getNormalized());
        $this->assertSame('+989123456789', $phone->getInternational());
    }

    public function testPersianDigitsNormalization(): void
    {
        $phone = new PhoneNumber('۰۹۱۲۳۴۵۶۷۸۹');
        $this->assertSame('09123456789', $phone->getNormalized());
    }

    public function testArabicDigitsNormalization(): void
    {
        $phone = new PhoneNumber('٠٩١٢٣٤٥٦٧٨٩');
        $this->assertSame('09123456789', $phone->getNormalized());
    }

    public function testInternationalPlus98Prefix(): void
    {
        $phone = new PhoneNumber('+989123456789');
        $this->assertSame('09123456789', $phone->getNormalized());
    }

    public function testInternational0098Prefix(): void
    {
        $phone = new PhoneNumber('00989123456789');
        $this->assertSame('09123456789', $phone->getNormalized());
    }

    public function test98WithoutLeadingPlus(): void
    {
        $phone = new PhoneNumber('989123456789');
        $this->assertSame('09123456789', $phone->getNormalized());
    }

    public function testDashesSpacesAndParenthesesRemoval(): void
    {
        $phone = new PhoneNumber('0912-345 67 (89)');
        $this->assertSame('09123456789', $phone->getNormalized());
    }

    public function testThrowsOnInvalidLength(): void
    {
        $this->expectException(InvalidPhoneException::class);
        new PhoneNumber('0912345');
    }

    public function testThrowsOnNonZeroNinePrefix(): void
    {
        $this->expectException(InvalidPhoneException::class);
        new PhoneNumber('02123456789');
    }

    public function testEqualsMethod(): void
    {
        $p1 = new PhoneNumber('09123456789');
        $p2 = new PhoneNumber('+989123456789');
        $p3 = new PhoneNumber('09351112233');

        $this->assertTrue($p1->equals($p2));
        $this->assertFalse($p1->equals($p3));
    }
}
