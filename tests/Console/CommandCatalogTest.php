<?php

namespace Antcode\ArtisanWizard\Tests\Console;

use Antcode\ArtisanWizard\Console\CommandCatalog;
use Antcode\ArtisanWizard\Console\CommandSchema;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

function applicationWith(SymfonyCommand ...$commands): Application
{
    $application = new Application;
    $application->setAutoExit(false);
    $application->addCommands($commands);

    return $application;
}

it('labels commands as "name - description" sorted by name', function () {
    $zebra = (new SymfonyCommand('zebra:run'))->setDescription('Runs zebras');
    $alpha = (new SymfonyCommand('alpha:run'))->setDescription('Runs alphas');

    $labels = (new CommandCatalog(applicationWith($zebra, $alpha)))->labels();

    expect($labels['alpha:run'])->toBe('alpha:run - Runs alphas');
    expect($labels['zebra:run'])->toBe('zebra:run - Runs zebras');

    $keys = array_keys($labels);
    expect(array_search('alpha:run', $keys))->toBeLessThan(array_search('zebra:run', $keys));
});

it('falls back to a placeholder when a command has no description', function () {
    $bare = new SymfonyCommand('bare:run');

    $labels = (new CommandCatalog(applicationWith($bare)))->labels();

    expect($labels['bare:run'])->toBe('bare:run - No description provided');
});

it('builds a schema for a named command', function () {
    $command = (new SymfonyCommand('demo:run'))->setDescription('Demo');
    $command->addArgument('user');

    $schema = (new CommandCatalog(applicationWith($command)))->schemaFor('demo:run');

    expect($schema)->toBeInstanceOf(CommandSchema::class);
    expect($schema->field('user'))->not->toBeNull();
});
