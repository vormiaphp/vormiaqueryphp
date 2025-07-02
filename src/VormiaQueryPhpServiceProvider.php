<?php

namespace VormiaQueryPhp;

use Illuminate\Support\ServiceProvider;

class VormiaQueryPhpServiceProvider extends ServiceProvider
{
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \VormiaQueryPhp\Console\VormiaQueryInstallCommand::class,
                \VormiaQueryPhp\Console\VormiaQueryUninstallCommand::class,
                \VormiaQueryPhp\Console\VormiaQueryUpdateCommand::class,
            ]);
        }
    }

    public function boot()
    {
        // Optionally publish config, migrations, etc.
    }
}
