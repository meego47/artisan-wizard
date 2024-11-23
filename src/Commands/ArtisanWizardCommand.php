<?php

namespace Antcode\ArtisanWizard\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class ArtisanWizardCommand extends Command
{
    public $signature = 'artisan-wizard:run';

    public $description = 'List and execute any artisan command interactively';

    public function handle(): void
    {
        while (true) {
            // Step 1: Retrieve all available commands with descriptions
            $commands = $this->getAllCommands();

            // Step 2: Let the user select a command
            $commandChoice = select(
                label: 'Select an artisan command to execute:',
                options: array_keys($commands),
                scroll: 50,
            );

            // Extract command name
            $commandName = $commands[$commandChoice];

            // Step 3: Retrieve arguments and options for the selected command
            $commandDetails = $this->getCommandArgumentsAndOptions($commandName);

            $collectedArguments = [];
            $collectedOptions = [];

            // Step 4: Collect arguments and options interactively
            while (true) {
                $this->displayCollectedFields($collectedArguments, $collectedOptions);

                // Get the remaining fields to fill
                $remainingFields = $this->getRemainingFields($commandDetails, $collectedArguments, $collectedOptions);

                // Add the "Run Command" option (only if required fields are filled)
                if ($this->allRequiredFieldsFilled($commandDetails, $collectedArguments, $collectedOptions)) {
                    $remainingFields['run'] = 'Run command with current settings';
                }

                // Prompt user to choose a field
                $selectedField = select(
                    label: 'Select a field to fill (or run the command):',
                    options: $remainingFields,
                    scroll: 50
                );

                if ($selectedField === 'run') {
                    $this->executeCommand($commandName, $collectedArguments, $collectedOptions);

                    return; // Exit after running the command
                }

                // Handle the selected field
                $this->handleSelectedField($selectedField, $commandDetails, $collectedArguments, $collectedOptions);
            }
        }
    }

    private function getAllCommands(): array
    {
        $allCommands = Artisan::all();
        $commands = [];

        foreach ($allCommands as $name => $command) {
            $description = $command->getDescription() ?: 'No description provided';
            $commands["{$name} - {$description}"] = $name;
        }

        ksort($commands); // Sort commands alphabetically

        return $commands;
    }

    private function getCommandArgumentsAndOptions(string $commandName): array
    {
        $command = Artisan::all()[$commandName];
        $definition = $command->getDefinition();

        // Retrieve arguments
        $arguments = [];
        foreach ($definition->getArguments() as $argument) {
            $arguments[$argument->getName()] = [
                'description' => $argument->getDescription() ?: 'No description provided',
                'isRequired' => $argument->isRequired(),
                'isArray' => $argument->isArray(),
            ];
        }

        // Retrieve options
        $options = [];
        foreach ($definition->getOptions() as $option) {
            $options[$option->getName()] = [
                'description' => $option->getDescription() ?: 'No description provided',
                'isRequired' => $option->isValueRequired(),
                'isArray' => $option->isArray(),
                'needsValue' => $option->acceptValue(), // Add flag for options requiring a value
            ];
        }

        return compact('arguments', 'options');
    }

    private function displayCollectedFields(array $arguments, array $options): void
    {
        $this->info("\nFilled fields:");

        foreach ($arguments as $name => $value) {
            $this->line("  Argument: {$name}={$value}");
        }

        foreach ($options as $name => $value) {
            $formattedValue = is_array($value) ? implode(', ', $value) : $value;
            if ($formattedValue) {
                $this->line("  Option: --{$name}={$formattedValue}");
            } else {
                $this->line("  Option: --{$name}");
            }
        }

        if (empty($arguments) && empty($options)) {
            $this->line('  (None yet)');
        }
    }

    private function getRemainingFields(array $commandDetails, array $arguments, array $options): array
    {
        $remainingFields = [];

        // Process arguments
        foreach ($commandDetails['arguments'] as $name => $details) {
            if (! isset($arguments[$name])) {
                $prefix = $details['isRequired'] ? '[Required] ' : '';
                $remainingFields[$name] = "{$prefix}Fill argument: {$name} - {$details['description']}";
            }
        }

        // Process options
        foreach ($commandDetails['options'] as $name => $details) {
            if (isset($options[$name]) && ! $details['isArray']) {
                continue; // Skip non-array options already set
            }

            if ($details['needsValue']) {
                $prefix = $details['isRequired'] ? '[Required] ' : '';
                $remainingFields[$name] = "{$prefix}Fill option with value: {$name} - {$details['description']}";
            } else {
                $remainingFields[$name] = "Append option: {$name} - {$details['description']}";
            }
        }

        return $remainingFields;
    }

    private function handleSelectedField(
        string $selectedField,
        array $commandDetails,
        array &$collectedArguments,
        array &$collectedOptions
    ): void {
        if (isset($commandDetails['arguments'][$selectedField])) {
            $collectedArguments[$selectedField] = text(
                label: "Fill argument: {$selectedField} - {$commandDetails['arguments'][$selectedField]['description']}"
            );
        } elseif (isset($commandDetails['options'][$selectedField])) {
            $option = $commandDetails['options'][$selectedField];

            if ($option['needsValue']) {
                if ($option['isArray']) {
                    $collectedOptions[$selectedField][] = text(
                        label: "Fill option with value (array): {$selectedField} - {$option['description']}"
                    );
                } else {
                    $collectedOptions[$selectedField] = text(
                        label: "Fill option with value: {$selectedField} - {$option['description']}"
                    );
                }
            } else {
                $collectedOptions[$selectedField] = true; // For boolean flags
            }
        }
    }

    private function executeCommand(string $commandName, array $arguments, array $options): void
    {
        // Prepare arguments and options for Artisan::call
        $input = $arguments;

        foreach ($options as $key => $value) {
            if ($value === true) {
                // For boolean options
                $input["--{$key}"] = true;
            } elseif (is_array($value)) {
                // For options with multiple values (arrays)
                $input["--{$key}"] = $value;
            } else {
                // For regular options with single values
                $input["--{$key}"] = $value;
            }
        }
        $input['--ansi'] = true; // Enable ANSI colors in the output

        // Log the command being executed
        $this->line("\n<info>Executing:</info> artisan ".$this->buildCommandString($commandName, $input));

        // Call the command
        Artisan::call($commandName, $input);

        // Display the output of the command
        $this->output->write(Artisan::output());
        $this->info("\nCommand executed successfully!");
    }

    private function buildCommandString(string $commandName, array $input): string
    {
        $parts = [$commandName];

        foreach ($input as $key => $value) {
            if (str_starts_with($key, '--')) {
                if ($value === true) {
                    $parts[] = $key; // Boolean flag
                } elseif (is_array($value)) {
                    foreach ($value as $item) {
                        $parts[] = "{$key}={$item}";
                    }
                } else {
                    $parts[] = "{$key}={$value}";
                }
            } else {
                $parts[] = $value; // Positional argument
            }
        }

        return implode(' ', $parts);
    }

    private function allRequiredFieldsFilled(array $commandDetails, array $arguments, array $options): bool
    {
        foreach ($commandDetails['arguments'] as $name => $details) {
            if ($details['isRequired'] && ! isset($arguments[$name])) {
                return false;
            }
        }

        foreach ($commandDetails['options'] as $name => $details) {
            if ($details['isRequired'] && ! isset($options[$name])) {
                return false;
            }
        }

        return true;
    }
}
