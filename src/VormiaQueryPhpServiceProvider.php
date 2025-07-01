<?php

namespace VormiaQueryPhp;

use Illuminate\Support\ServiceProvider;

class VormiaQueryPhpServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register command
        $this->commands([
            \VormiaQueryPhp\Console\VormiaQueryInstallCommand::class,
        ]);
    }

    public function boot()
    {
        // Optionally publish config, migrations, etc.
    }
}
