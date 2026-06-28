<?php

namespace Antcode\ArtisanWizard\Tests\Console;

use Antcode\ArtisanWizard\Console\Argument;
use Antcode\ArtisanWizard\Console\CommandSchema;
use Antcode\ArtisanWizard\Console\Option;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

function demoCommand(): SymfonyCommand
{
    $command = new SymfonyCommand('demo:run');
    $command->addArgument('user', InputArgument::REQUIRED, 'The user');
    $command->addArgument('roles', InputArgument::IS_ARRAY, 'The roles');
    $command->addOption('queue', null, InputOption::VALUE_REQUIRED, 'Queue name');
    $command->addOption('force', null, InputOption::VALUE_NONE, 'Force it');
    $command->addOption('id', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Ids');

    return $command;
}

it('reads arguments from a command definition with required/array flags', function () {
    $schema = CommandSchema::fromCommand(demoCommand());

    $user = $schema->field('user');
    expect($user)->toBeInstanceOf(Argument::class);
    expect($user->isRequired())->toBeTrue();
    expect($user->acceptsMultipleValues())->toBeFalse();

    expect($schema->field('roles')->acceptsMultipleValues())->toBeTrue();
});

it('reads options including value/flag/array distinctions', function () {
    $schema = CommandSchema::fromCommand(demoCommand());

    $queue = $schema->field('queue');
    expect($queue)->toBeInstanceOf(Option::class);
    expect($queue->needsValuePrompt())->toBeTrue();

    expect($schema->field('force')->needsValuePrompt())->toBeFalse();
    expect($schema->field('id')->acceptsMultipleValues())->toBeTrue();
});

it('falls back to a placeholder description when none is given', function () {
    $command = new SymfonyCommand('demo:bare');
    $command->addArgument('thing');

    $schema = CommandSchema::fromCommand($command);

    expect($schema->field('thing')->menuLabel())->toContain('No description provided');
});

it('lists required fields and orders arguments before options', function () {
    $schema = CommandSchema::fromCommand(demoCommand());

    $required = array_map(static fn ($field) => $field->name(), $schema->requiredFields());
    expect($required)->toContain('user')->toContain('queue');

    // Arguments come first in the field ordering.
    expect(array_key_first($schema->fields()))->toBe('user');
});

it('returns null for an unknown field', function () {
    expect(CommandSchema::fromCommand(demoCommand())->field('nope'))->toBeNull();
});
