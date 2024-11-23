<?php

declare(strict_types=1);

namespace Antcode\ArtisanWizard;

use Antcode\ArtisanWizard\Commands\ArtisanWizardCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ArtisanWizardServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('artisan-wizard')
            ->hasCommand(ArtisanWizardCommand::class);
    }
}
