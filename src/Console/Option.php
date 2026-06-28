<?php

declare(strict_types=1);

namespace Antcode\ArtisanWizard\Console;

/**
 * An option ("--name") of an artisan command. May be a value option or a
 * boolean flag (which is stored as `true` and rendered without a value).
 */
class Option extends Field
{
    public function __construct(
        string $name,
        string $description,
        bool $isRequired,
        bool $isArray,
        protected bool $needsValue,
    ) {
        parent::__construct($name, $description, $isRequired, $isArray);
    }

    public function needsValuePrompt(): bool
    {
        return $this->needsValue;
    }

    public function inputKey(): string
    {
        return "--{$this->name}";
    }

    public function summaryLine(mixed $value): string
    {
        if ($value === true) {
            return "Option: --{$this->name}";
        }

        return "Option: --{$this->name}={$this->asList($value)}";
    }

    public function commandTokens(mixed $value): array
    {
        if ($value === true) {
            return ["--{$this->name}"];
        }

        if (is_array($value)) {
            return array_map(fn ($item): string => "--{$this->name}={$item}", array_values($value));
        }

        return ["--{$this->name}={$value}"];
    }

    protected function menuVerb(): string
    {
        if (! $this->needsValue) {
            return 'Append option';
        }

        return $this->isArray ? 'Add value to option' : 'Fill option with value';
    }

    protected function promptVerb(): string
    {
        return 'Fill option with value';
    }
}
