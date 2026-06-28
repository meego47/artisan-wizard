<?php

declare(strict_types=1);

namespace Antcode\ArtisanWizard\Console;

/**
 * The values the user has filled in for the command being configured.
 *
 * Stores one entry per field (keyed by field name); the value is a string, a
 * list of strings (for array fields), or `true` (for boolean flags). All
 * per-field formatting is delegated to the {@see Field} objects.
 */
class CollectedInput
{
    /** @var array<string, string|array<int, string>|bool> */
    private array $values = [];

    /**
     * Record a value for a field, appending for array fields and replacing
     * otherwise.
     */
    public function fill(Field $field, string|bool $value): void
    {
        if ($field->acceptsMultipleValues() && is_string($value)) {
            $this->values[$field->name()][] = $value;

            return;
        }

        $this->values[$field->name()] = $value;
    }

    public function has(Field $field): bool
    {
        return array_key_exists($field->name(), $this->values);
    }

    /**
     * Whether a required field has a usable value. An empty string (the user
     * just pressing Enter) does not count as filled.
     */
    public function isFilled(Field $field): bool
    {
        $value = $this->values[$field->name()] ?? null;

        if (is_array($value)) {
            return $value !== [];
        }

        if (is_string($value)) {
            return $value !== '';
        }

        return $value !== null;
    }

    /**
     * The fields still offered in the menu: everything not yet set, plus array
     * fields (which accept more values), keyed by name => menu label.
     *
     * @return array<string, string>
     */
    public function remainingFields(CommandSchema $schema): array
    {
        $remaining = [];

        foreach ($schema->fields() as $name => $field) {
            if ($this->has($field) && ! $field->acceptsMultipleValues()) {
                continue;
            }

            $remaining[$name] = $field->menuLabel();
        }

        return $remaining;
    }

    public function allRequiredFilled(CommandSchema $schema): bool
    {
        foreach ($schema->requiredFields() as $field) {
            if (! $this->isFilled($field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * One summary line per filled field, in schema order.
     *
     * @return array<int, string>
     */
    public function summaryLines(CommandSchema $schema): array
    {
        $lines = [];

        foreach ($schema->fields() as $name => $field) {
            if ($this->has($field)) {
                $lines[] = $field->summaryLine($this->values[$name]);
            }
        }

        return $lines;
    }

    /**
     * The input array for Artisan::call(), with ANSI output forced on.
     *
     * @return array<string, mixed>
     */
    public function toArtisanInput(CommandSchema $schema): array
    {
        $input = [];

        foreach ($schema->fields() as $name => $field) {
            if ($this->has($field)) {
                $input[$field->inputKey()] = $this->values[$name];
            }
        }

        $input['--ansi'] = true;

        return $input;
    }

    /**
     * A human-readable rendering of the command that will be run.
     */
    public function describe(string $commandName, CommandSchema $schema): string
    {
        $parts = [$commandName];

        foreach ($schema->fields() as $name => $field) {
            if ($this->has($field)) {
                foreach ($field->commandTokens($this->values[$name]) as $token) {
                    $parts[] = $token;
                }
            }
        }

        $parts[] = '--ansi';

        return implode(' ', $parts);
    }
}
