<?php

namespace Antcode\ArtisanWizard\Tests\Commands;

use Antcode\ArtisanWizard\Tests\TestCase;

class ArtisanWizardCommandTest extends TestCase
{
    /** @test */
    public function it_displays_available_commands()
    {
        // Run the wizard command and simulate interactions
        $this->artisan('artisan-wizard:run')
            ->expectsQuestion('Select an artisan command to execute:', 'migrate - Run the database migrations')
            ->expectsQuestion('Select a field to fill (or run the command):', 'Run command with current settings')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_executes_a_command_with_arguments_and_options()
    {
        // Run the wizard command and simulate interactions
        $this->artisan('artisan-wizard:run')
            ->expectsQuestion('Select an artisan command to execute:', 'route:list - List all routes')
            ->expectsQuestion('Select a field to fill (or run the command):', '[Required] Fill option with value: method - HTTP method to filter by')
            ->expectsQuestion('Fill option with value: method - HTTP method to filter by', 'GET')
            ->expectsQuestion('Select a field to fill (or run the command):', '[Required] Fill option with value: path - Filter routes by path')
            ->expectsQuestion('Fill option with value: path - Filter routes by path', 'api')
            ->expectsQuestion('Select a field to fill (or run the command):', 'Run command with current settings')
            ->assertExitCode(0);
    }
}
