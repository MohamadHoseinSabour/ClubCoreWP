<?php

declare(strict_types=1);

namespace ClubCore\Tests\Unit;

use ClubCore\Infrastructure\Pattern\PatternParser;
use ClubCore\Infrastructure\Pattern\VariableRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Tests for PatternParser & VariableRegistry.
 */
class PatternParserTest extends TestCase
{
    private PatternParser $parser;

    protected function setUp(): void
    {
        $registry = new VariableRegistry();
        $this->parser = new PatternParser($registry);
    }

    public function testParsesValidVariables(): void
    {
        $template = 'سلام {first_name} {last_name} عزیز، به باشگاه خوش آمدید.';
        $vars = $this->parser->parse($template);

        $this->assertSame(['first_name', 'last_name'], $vars);
    }

    public function testDetectsDuplicateVariables(): void
    {
        $template = 'سلام {first_name}، نام شما {first_name} است.';
        $result = $this->parser->validate($template);

        $this->assertFalse($result->isValid);
        $this->assertContains('first_name', $result->duplicateVariables);
    }

    public function testDetectsUnknownVariables(): void
    {
        $template = 'سلام {first_name}، تخفیف شما: {unknown_code}';
        $result = $this->parser->validate($template);

        $this->assertFalse($result->isValid);
        $this->assertContains('unknown_code', $result->unknownVariables);
    }

    public function testResolveValuesMatchesOrder(): void
    {
        $vars = ['first_name', 'last_name'];
        $data = [
            'first_name' => 'رضا',
            'last_name' => 'صادقی',
            'phone' => '09121112233',
        ];

        $resolved = $this->parser->resolveValues($vars, $data);
        $this->assertSame(['رضا', 'صادقی'], $resolved);
    }
}
