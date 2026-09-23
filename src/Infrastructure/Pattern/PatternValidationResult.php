<?php
declare(strict_types=1);

namespace ClubCore\Infrastructure\Pattern;

readonly class PatternValidationResult
{
    public function __construct(
        public bool $valid,
        public array $variables,
        public array $errors,
        public array $unknownVariables,
        public array $duplicateVariables
    ) {}
}
