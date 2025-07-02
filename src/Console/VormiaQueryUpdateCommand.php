<?php

namespace VormiaQueryPhp\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class VormiaQueryUpdateCommand extends Command
{
    protected $signature = 'vormiaquery:update';
    protected $description = 'Update VormiaQuery integration (re-run setup steps)';

    public function handle()
    {
        $this->checkSanctum();
        $this->addKeysToEnv();
        $this->publishCorsConfig();
        $this->info('VormiaQuery integration updated!');
        return 0;
    }

    protected function checkSanctum()
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        $hasSanctum = isset($composer['require']['laravel/sanctum']) || isset($composer['require-dev']['laravel/sanctum']);
        if (!$hasSanctum) {
            if ($this->confirm('Laravel Sanctum is not installed. Would you like to run "php artisan install:api" now?', true)) {
                $this->call('install:api');
                $this->info('Sanctum API features installed.');
            } else {
                $this->warn('You must run "php artisan install:api" to enable Sanctum API features.');
            }
        } else {
            $this->info('Laravel Sanctum detected.');
        }
    }

    protected function addKeysToEnv()
    {
        $this->addKeyToEnvFile(base_path('.env'), false);
        $this->addKeyToEnvFile(base_path('.env.example'), true);
    }

    protected function addKeyToEnvFile($file, $example = false)
    {
        if (!file_exists($file)) return;
        $env = file_get_contents($file);
        $changed = false;
        if (!Str::contains($env, 'VORMIA_PRIVATE_KEY')) {
            $env .= "\nVORMIA_PRIVATE_KEY=" . ($example ? '<private-key-here>' : '') . "\n";
            $changed = true;
        }
        if (!Str::contains($env, 'VORMIA_PUBLIC_KEY')) {
            $env .= "VORMIA_PUBLIC_KEY=" . ($example ? '<public-key-here>' : '') . "\n";
            $changed = true;
        }
        if ($changed) file_put_contents($file, $env);
    }

    protected function publishCorsConfig()
    {
        $corsConfig = config_path('cors.php');
        if (!file_exists($corsConfig)) {
            if ($this->confirm('CORS config is not published. Would you like to publish it now?', true)) {
                $this->call('vendor:publish', ['--tag' => 'cors']);
            } else {
                $this->warn('CORS config was not published. You can do it later with: php artisan vendor:publish --tag=cors');
            }
        } else {
            $this->info('CORS config already published.');
        }
    }
}
