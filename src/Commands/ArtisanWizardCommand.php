<?php

namespace Antcode\ArtisanWizard\Commands;

use Antcode\ArtisanWizard\Console\CollectedInput;
use Antcode\ArtisanWizard\Console\CommandCatalog;
use Antcode\ArtisanWizard\Console\CommandSchema;
use Antcode\ArtisanWizard\Console\Field;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class ArtisanWizardCommand extends Command
{
    /**
     * Sentinel choices in the menus. The `__` prefix keeps them from colliding
     * with real command, argument, or option names.
     */
    private const RUN = '__run';

    private const BACK = '__back';

    private const EXIT = '__exit';

    public $signature = 'artisan-wizard:run';

    public $description = 'List and execute any artisan command interactively';

    public function handle(): int
    {
        $application = $this->getApplication();

        if ($application === null) {
            $this->error('The console application is not available.');

            return self::FAILURE;
        }

        $catalog = new CommandCatalog($application);

        while (true) {
            $choices = $catalog->labels();
            $choices[self::EXIT] = 'Exit the wizard';

            $commandName = select(
                label: 'Select an artisan command to execute:',
                options: $choices,
                scroll: 50,
            );

            if ($commandName === self::EXIT) {
                return self::SUCCESS;
            }

            $this->configureAndRun($commandName, $catalog->schemaFor($commandName));
        }
    }

    /**
     * Let the user fill the command's fields and optionally run it. Returns the
     * command exit code, or null when the user goes back to the command list.
     */
    private function configureAndRun(string $commandName, CommandSchema $schema): ?int
    {
        $input = new CollectedInput;

        while (true) {
            $this->displaySummary($input, $schema);

            $menu = $input->remainingFields($schema);
            if ($input->allRequiredFilled($schema)) {
                $menu[self::RUN] = 'Run command with current settings';
            }
            $menu[self::BACK] = 'Back to command selection';

            $selected = select(
                label: 'Select a field to fill (or run the command):',
                options: $menu,
                scroll: 50,
            );

            if ($selected === self::RUN) {
                return $this->runSelectedCommand($commandName, $schema, $input);
            }

            if ($selected === self::BACK) {
                return null;
            }

            $field = $schema->field($selected);
            if ($field !== null) {
                $this->collectField($field, $input);
            }
        }
    }

    private function collectField(Field $field, CollectedInput $input): void
    {
        if (! $field->needsValuePrompt()) {
            $input->fill($field, true); // Boolean flag

            return;
        }

        $input->fill($field, text(
            label: $field->promptLabel(),
            required: $field->isRequired(),
        ));
    }

    private function displaySummary(CollectedInput $input, CommandSchema $schema): void
    {
        $this->info("\nFilled fields:");

        $lines = $input->summaryLines($schema);

        foreach ($lines as $line) {
            $this->line('  '.$line);
        }

        if ($lines === []) {
            $this->line('  (None yet)');
        }
    }

    private function runSelectedCommand(string $commandName, CommandSchema $schema, CollectedInput $input): int
    {
        $this->line("\n<info>Executing:</info> artisan ".$input->describe($commandName, $schema));

        try {
            $exitCode = Artisan::call($commandName, $input->toArtisanInput($schema));
        } catch (Throwable $e) {
            $this->error("\nCommand failed: ".$e->getMessage());

            return self::FAILURE;
        }

        $this->output->write(Artisan::output());

        if ($exitCode === self::SUCCESS) {
            $this->info("\nCommand executed successfully!");
        } else {
            $this->error("\nCommand exited with code {$exitCode}.");
        }

        return $exitCode;
    }
}
