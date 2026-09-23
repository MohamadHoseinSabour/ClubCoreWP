<?php
declare(strict_types=1);

namespace ClubCore\Infrastructure\Pattern;

use ClubCore\Domain\Exception\SmsException;

class PatternParser
{
    public function __construct(
        private readonly VariableRegistry $registry
    ) {}

    public function parse(string $template): array
    {
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $template, $matches);
        return $matches[1] ?? [];
    }

    public function validate(string $template): PatternValidationResult
    {
        $variables = $this->parse($template);
        $errors = [];
        $unknownVariables = [];
        $duplicateVariables = [];

        $counts = array_count_values($variables);

        foreach ($counts as $var => $count) {
            if (!$this->registry->isKnown((string) $var)) {
                $unknownVariables[] = $var;
                $errors[] = sprintf(__('متغیر نامعتبر است: %s', 'clubcore'), $var);
            }
            if ($count > 1) {
                $duplicateVariables[] = $var;
                $errors[] = sprintf(__('متغیر تکراری است: %s', 'clubcore'), $var);
            }
        }

        $openCount = substr_count($template, '{');
        $closeCount = substr_count($template, '}');
        
        if ($openCount !== $closeCount) {
            $errors[] = __('ساختار الگو نامعتبر است (آکولاد باز یا بسته نشده).', 'clubcore');
        }

        return new PatternValidationResult(
            empty($errors),
            $variables,
            $errors,
            $unknownVariables,
            $duplicateVariables
        );
    }

    public function resolveValues(array $variableNames, array $data): array
    {
        $resolved = [];
        
        foreach ($variableNames as $var) {
            $value = $this->registry->resolve((string) $var, $data);
            if ($value === null || $value === '') {
                if ($this->registry->isRequired((string) $var)) {
                    throw SmsException::missingVariable((string) $var);
                }
                $value = '-';
            }
            $resolved[$var] = (string) $value;
        }

        return $resolved; // Maintains order of $variableNames
    }
}
