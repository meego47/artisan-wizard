<?php

namespace Antcode\ArtisanWizard\Tests\Console;

use Antcode\ArtisanWizard\Console\Argument;
use Antcode\ArtisanWizard\Console\Option;

function valueOption(bool $isRequired = false, bool $isArray = false, bool $needsValue = true): Option
{
    return new Option('queue', 'Queue name', $isRequired, $isArray, $needsValue);
}

// --- Argument -------------------------------------------------------------------

it('labels a required argument with a [Required] prefix', function () {
    $argument = new Argument('user', 'The user', true, false);

    expect($argument->menuLabel())->toBe('[Required] Fill argument: user - The user');
    expect($argument->promptLabel())->toBe('Fill argument: user - The user');
});

it('labels an array argument as "Add value to" but prompts with "Fill"', function () {
    $argument = new Argument('roles', 'The roles', false, true);

    expect($argument->menuLabel())->toBe('Add value to argument: roles - The roles');
    expect($argument->promptLabel())->toBe('Fill argument: roles - The roles');
    expect($argument->acceptsMultipleValues())->toBeTrue();
});

it('renders argument values for the summary and the echoed command', function () {
    $scalar = new Argument('user', 'The user', false, false);
    $array = new Argument('roles', 'The roles', false, true);

    expect($scalar->summaryLine('bob'))->toBe('Argument: user=bob');
    expect($scalar->commandTokens('bob'))->toBe(['bob']);

    expect($array->summaryLine(['admin', 'editor']))->toBe('Argument: roles=admin, editor');
    expect($array->commandTokens(['admin', 'editor']))->toBe(['admin', 'editor']);
});

it('uses the bare name as the input key for an argument', function () {
    expect((new Argument('user', 'd', false, false))->inputKey())->toBe('user');
});

// --- Option ---------------------------------------------------------------------

it('renders a boolean flag without a value', function () {
    $flag = new Option('force', 'Force it', false, false, false);

    expect($flag->needsValuePrompt())->toBeFalse();
    expect($flag->menuLabel())->toBe('Append option: force - Force it');
    expect($flag->summaryLine(true))->toBe('Option: --force');
    expect($flag->commandTokens(true))->toBe(['--force']);
});

it('renders a scalar option value', function () {
    $option = valueOption();

    expect($option->needsValuePrompt())->toBeTrue();
    expect($option->summaryLine('high'))->toBe('Option: --queue=high');
    expect($option->commandTokens('high'))->toBe(['--queue=high']);
    expect($option->inputKey())->toBe('--queue');
});

it('renders an array option as a comma list in summary but repeats it in the command', function () {
    $option = valueOption(isArray: true);

    expect($option->menuLabel())->toBe('Add value to option: queue - Queue name');
    expect($option->summaryLine(['high', 'low']))->toBe('Option: --queue=high, low');
    expect($option->commandTokens(['high', 'low']))->toBe(['--queue=high', '--queue=low']);
});

it('labels a required value option with a [Required] prefix', function () {
    expect(valueOption(isRequired: true)->menuLabel())
        ->toBe('[Required] Fill option with value: queue - Queue name');
});
