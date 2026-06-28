<?php

declare(strict_types=1);

namespace Antcode\ArtisanWizard\Console;

/**
 * A single input field of an artisan command (one argument or one option).
 *
 * Each field knows how to present itself in the wizard's menus and prompts and
 * how to turn a collected value into the pieces the wizard needs, so the
 * command never has to branch on "is this an argument or an option?".
 */
abstract class Field
{
    public function __construct(
        protected string $name,
        protected string $description,
        protected bool $isRequired,
        protected bool $isArray,
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * Array fields accept several values, so they stay selectable in the menu
     * even after the first value has been supplied.
     */
    public function acceptsMultipleValues(): bool
    {
        return $this->isArray;
    }

    /** Text shown for this field in the field-selection menu. */
    public function menuLabel(): string
    {
        $prefix = $this->isRequired ? '[Required] ' : '';

        return "{$prefix}{$this->menuVerb()}: {$this->name} - {$this->description}";
    }

    /** Text shown when prompting the user for this field's value. */
    public function promptLabel(): string
    {
        return "{$this->promptVerb()}: {$this->name} - {$this->description}";
    }

    /** Whether selecting this field should prompt the user for a value. */
    abstract public function needsValuePrompt(): bool;

    /** Key this field uses in the Artisan::call() input array. */
    abstract public function inputKey(): string;

    /** A single line describing the collected value in the "Filled fields" summary. */
    abstract public function summaryLine(mixed $value): string;

    /**
     * Tokens representing the collected value in the echoed command string.
     *
     * @return array<int, string>
     */
    abstract public function commandTokens(mixed $value): array;

    /** Verb used in the selection menu (e.g. "Fill argument"). */
    abstract protected function menuVerb(): string;

    /** Verb used in the value prompt (e.g. "Fill argument"). */
    abstract protected function promptVerb(): string;

    /** Render a scalar as-is and an array as a comma-separated list. */
    protected function asList(mixed $value): string
    {
        return is_array($value) ? implode(', ', $value) : (string) $value;
    }
}
