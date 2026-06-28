<?php

declare(strict_types=1);

namespace Antcode\ArtisanWizard\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;

/**
 * The arguments and options a single artisan command accepts, read once from
 * its input definition and exposed as {@see Field} value objects.
 */
class CommandSchema
{
    /**
     * @param  array<string, Argument>  $arguments
     * @param  array<string, Option>  $options
     */
    public function __construct(
        private readonly array $arguments,
        private readonly array $options,
    ) {}

    public static function fromCommand(SymfonyCommand $command): self
    {
        $definition = $command->getDefinition();

        $arguments = [];
        foreach ($definition->getArguments() as $argument) {
            $arguments[$argument->getName()] = new Argument(
                $argument->getName(),
                $argument->getDescription() ?: 'No description provided',
                $argument->isRequired(),
                $argument->isArray(),
            );
        }

        $options = [];
        foreach ($definition->getOptions() as $option) {
            $options[$option->getName()] = new Option(
                $option->getName(),
                $option->getDescription() ?: 'No description provided',
                $option->isValueRequired(),
                $option->isArray(),
                $option->acceptValue(),
            );
        }

        return new self($arguments, $options);
    }

    /**
     * All fields, arguments first, keyed by name. This is the order the wizard
     * presents fields and builds the command input in.
     *
     * @return array<string, Field>
     */
    public function fields(): array
    {
        return $this->arguments + $this->options;
    }

    public function field(string $name): ?Field
    {
        return $this->arguments[$name] ?? $this->options[$name] ?? null;
    }

    /**
     * @return array<int, Field>
     */
    public function requiredFields(): array
    {
        return array_values(array_filter(
            $this->fields(),
            static fn (Field $field): bool => $field->isRequired(),
        ));
    }
}
