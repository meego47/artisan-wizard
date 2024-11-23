<?php

namespace Antcode\ArtisanWizard\Tests\Commands;

use Illuminate\Console\Command;

class DummyCommand extends Command
{
    protected $signature = 'dummy:command';
    protected $description = 'A dummy command for testing purposes.';

    public function handle()
    {
        $this->info('This is a dummy command!');
    }
}
