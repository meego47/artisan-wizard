<?php

declare(strict_types=1);

namespace Antcode\ArtisanWizard\Console;

/**
 * A positional argument of an artisan command.
 */
class Argument extends Field
{
    public function needsValuePrompt(): bool
    {
        return true;
    }

    public function inputKey(): string
    {
        return $this->name;
    }

    public function summaryLine(mixed $value): string
    {
        return "Argument: {$this->name}={$this->asList($value)}";
    }

    public function commandTokens(mixed $value): array
    {
        // A variadic argument holds several values; list each one positionally.
        if (is_array($value)) {
            return array_map(static fn ($item): string => (string) $item, array_values($value));
        }

        return [(string) $value];
    }

    protected function menuVerb(): string
    {
        return $this->isArray ? 'Add value to argument' : 'Fill argument';
    }

    protected function promptVerb(): string
    {
        return 'Fill argument';
    }
}
