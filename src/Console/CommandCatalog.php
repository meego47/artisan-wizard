<?php

declare(strict_types=1);

namespace Antcode\ArtisanWizard\Console;

use Symfony\Component\Console\Application;

/**
 * The set of artisan commands the wizard can run. Wraps the console
 * application so the command never touches the Artisan facade directly and
 * resolves the command list only once.
 */
class CommandCatalog
{
    /** @var array<string, \Symfony\Component\Console\Command\Command>|null */
    private ?array $commands = null;

    public function __construct(private readonly Application $application) {}

    /**
     * @return array<string, \Symfony\Component\Console\Command\Command>
     */
    public function all(): array
    {
        return $this->commands ??= $this->application->all();
    }

    /**
     * Command name => "name - description", sorted alphabetically by name.
     *
     * @return array<string, string>
     */
    public function labels(): array
    {
        $labels = [];

        foreach ($this->all() as $name => $command) {
            $description = $command->getDescription() ?: 'No description provided';
            $labels[$name] = "{$name} - {$description}";
        }

        ksort($labels);

        return $labels;
    }

    public function schemaFor(string $name): CommandSchema
    {
        return CommandSchema::fromCommand($this->all()[$name]);
    }
}
