<?php
declare(strict_types=1);

namespace ClubCore\Infrastructure\Pattern;

class VariableRegistry
{
    public const VARIABLES = [
        'first_name' => ['label' => 'نام', 'required' => false, 'source' => 'member'],
        'last_name' => ['label' => 'نام خانوادگی', 'required' => false, 'source' => 'member'],
        'full_name' => ['label' => 'نام کامل', 'required' => false, 'source' => 'member'],
        'phone' => ['label' => 'شماره موبایل', 'required' => false, 'source' => 'member'],
    ];

    public function getAll(): array
    {
        $variables = self::VARIABLES;
        
        /**
         * Filters the list of available SMS pattern variables.
         *
         * @param array $variables Registered variables.
         */
        return apply_filters('clubcore_pattern_variables', $variables);
    }

    public function isKnown(string $name): bool
    {
        return array_key_exists($name, $this->getAll());
    }

    public function isRequired(string $name): bool
    {
        $vars = $this->getAll();
        return !empty($vars[$name]['required']);
    }

    public function getLabel(string $name): string
    {
        $vars = $this->getAll();
        return $vars[$name]['label'] ?? $name;
    }

    public function resolve(string $name, array $memberData): ?string
    {
        if ($name === 'full_name') {
            $firstName = $memberData['first_name'] ?? '';
            $lastName = $memberData['last_name'] ?? '';
            return trim($firstName . ' ' . $lastName) ?: null;
        }

        return $memberData[$name] ?? null;
    }
}
