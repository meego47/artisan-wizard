<?php

namespace Antcode\ArtisanWizard\Tests\Console;

use Antcode\ArtisanWizard\Console\Argument;
use Antcode\ArtisanWizard\Console\CollectedInput;
use Antcode\ArtisanWizard\Console\CommandSchema;
use Antcode\ArtisanWizard\Console\Option;

function schema(array $arguments = [], array $options = []): CommandSchema
{
    $args = [];
    foreach ($arguments as $argument) {
        $args[$argument->name()] = $argument;
    }

    $opts = [];
    foreach ($options as $option) {
        $opts[$option->name()] = $option;
    }

    return new CommandSchema($args, $opts);
}

// --- isFilled: the empty-string guard for required fields -----------------------

it('does not count an empty string as a filled value', function () {
    $argument = new Argument('user', 'd', true, false);
    $input = new CollectedInput;
    $input->fill($argument, '');

    expect($input->isFilled($argument))->toBeFalse();
    expect($input->allRequiredFilled(schema([$argument])))->toBeFalse();
});

it('counts a non-empty value as filled', function () {
    $argument = new Argument('user', 'd', true, false);
    $input = new CollectedInput;
    $input->fill($argument, 'bob');

    expect($input->allRequiredFilled(schema([$argument])))->toBeTrue();
});

it('treats an empty array field as unfilled and a populated one as filled', function () {
    $argument = new Argument('roles', 'd', true, true);
    $schema = schema([$argument]);

    $input = new CollectedInput;
    expect($input->allRequiredFilled($schema))->toBeFalse();

    $input->fill($argument, 'admin');
    expect($input->allRequiredFilled($schema))->toBeTrue();
});

// --- remainingFields: array fields stay, scalar ones drop -----------------------

it('keeps array fields selectable but removes filled scalar fields', function () {
    $scalarArg = new Argument('user', 'The user', false, false);
    $arrayOpt = new Option('id', 'Ids', false, true, true);
    $schema = schema([$scalarArg], [$arrayOpt]);

    $input = new CollectedInput;
    $input->fill($scalarArg, 'bob');
    $input->fill($arrayOpt, '1');

    $remaining = $input->remainingFields($schema);

    expect($remaining)->not->toHaveKey('user');
    expect($remaining)->toHaveKey('id');
});

// --- fill: append vs replace vs flag --------------------------------------------

it('appends values for array fields and replaces them for scalar fields', function () {
    $arrayOpt = new Option('id', 'Ids', false, true, true);
    $scalarOpt = new Option('queue', 'Queue', false, false, true);

    $input = new CollectedInput;
    $input->fill($arrayOpt, '1');
    $input->fill($arrayOpt, '2');
    $input->fill($scalarOpt, 'high');
    $input->fill($scalarOpt, 'low');

    $built = $input->toArtisanInput(schema([], [$arrayOpt, $scalarOpt]));

    expect($built['--id'])->toBe(['1', '2']);
    expect($built['--queue'])->toBe('low');
});

// --- toArtisanInput / describe --------------------------------------------------

it('builds the Artisan input with flags, arrays, scalars and forced ANSI', function () {
    $userArg = new Argument('user', 'd', false, false);
    $forceOpt = new Option('force', 'd', false, false, false);
    $idOpt = new Option('id', 'd', false, true, true);
    $schema = schema([$userArg], [$forceOpt, $idOpt]);

    $input = new CollectedInput;
    $input->fill($userArg, 'bob');
    $input->fill($forceOpt, true);
    $input->fill($idOpt, '1');
    $input->fill($idOpt, '2');

    expect($input->toArtisanInput($schema))->toBe([
        'user' => 'bob',
        '--force' => true,
        '--id' => ['1', '2'],
        '--ansi' => true,
    ]);
});

it('describes the command including a variadic argument without crashing', function () {
    $recipientsArg = new Argument('recipients', 'd', false, true);
    $forceOpt = new Option('force', 'd', false, false, false);
    $schema = schema([$recipientsArg], [$forceOpt]);

    $input = new CollectedInput;
    $input->fill($recipientsArg, 'a@x.test');
    $input->fill($recipientsArg, 'b@x.test');
    $input->fill($forceOpt, true);

    expect($input->describe('mail:send', $schema))
        ->toBe('mail:send a@x.test b@x.test --force --ansi');
});

it('lists summary lines for filled fields only', function () {
    $userArg = new Argument('user', 'd', false, false);
    $unsetArg = new Argument('other', 'd', false, false);
    $schema = schema([$userArg, $unsetArg]);

    $input = new CollectedInput;
    $input->fill($userArg, 'bob');

    expect($input->summaryLines($schema))->toBe(['Argument: user=bob']);
});
