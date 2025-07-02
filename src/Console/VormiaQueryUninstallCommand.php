<?php

namespace VormiaQueryPhp\Console;

use Illuminate\Console\Command;

class VormiaQueryUninstallCommand extends Command
{
    protected $signature = 'vormiaquery:uninstall';
    protected $description = 'Uninstall VormiaQuery integration (removes keys and CORS config)';

    public function handle()
    {
        $this->removeKeysFromEnv();
        $this->removeCorsConfig();
        $this->info('VormiaQuery keys and CORS config removed.');
        return 0;
    }

    protected function removeKeysFromEnv()
    {
        $this->removeKeyFromEnvFile(base_path('.env'));
        $this->removeKeyFromEnvFile(base_path('.env.example'));
    }

    protected function removeKeyFromEnvFile($file)
    {
        if (!file_exists($file)) return;
        $env = file_get_contents($file);
        $env = preg_replace('/^VORMIA_PRIVATE_KEY=.*[\r\n]?/m', '', $env);
        $env = preg_replace('/^VORMIA_PUBLIC_KEY=.*[\r\n]?/m', '', $env);
        file_put_contents($file, $env);
    }

    protected function removeCorsConfig()
    {
        $corsConfig = config_path('cors.php');
        if (file_exists($corsConfig)) {
            unlink($corsConfig);
            $this->info('CORS config removed.');
        }
    }
}
