<?php
declare(strict_types=1);

namespace ClubCore\Infrastructure\Pattern;

class PatternValidationResult
{
    public function __construct(
        public readonly bool $valid,
        public readonly array $variables,
        public readonly array $errors,
        public readonly array $unknownVariables,
        public readonly array $duplicateVariables
    ) {}
}
