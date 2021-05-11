<?php

namespace EgalFramework\CommandLine\Commands;

use EgalFramework\Common\Session;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use ReflectionClass;
use ReflectionException;

/**
 * Class Update
 *
 * This class should be tested manually
 *
 * @codeCoverageIgnore
 * @package EgalFramework\CommandLine\Commands
 */
class Update extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'update
    {--f|framework : Update framework via composer}
    {--m|classes : Update class files (models and commands)}
    {--c|configuration : Update configuration}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Update framework, static configuration and class files (default all)';

    public function handle()
    {
        $chosen = false;
        if ($this->option('framework')) {
            $this->updateFramework();
            $chosen = true;
        }
        if ($this->option('classes')) {
            $this->updateClasses();
            $chosen = true;
        }
        if ($this->option('configuration')) {
            $this->updateConfiguration();
            $chosen = true;
        }
        if (!$chosen) {
            $this->updateFramework();
            $this->updateConfiguration();
            $this->updateClasses();
        }
    }

    public function updateFramework(): void
    {
        $packages = [];
        $composer = json_decode(file_get_contents(Session::getRegistry()->get('BasePath') . '/composer.json'), true);
        foreach (array_keys($composer['require']) as $package) {
            if (!preg_match('/^egal-framework\//', $package)) {
                continue;
            }
            $packages[] = $package;
        }
        $this->info('>>> Updating framework packages');
        shell_exec(
            'composer --working-dir=' . Session::getRegistry()->get('BasePath') . ' update ' . implode(' ', $packages)
        );
    }

    public function updateConfiguration(): void
    {
        $this->info('>>> Updating configuration');
        copy(
            __DIR__ . '/../../../stubs/bootstrap_egal.php',
            Session::getRegistry()->get('BasePath') . '/bootstrap/egal.php'
        );
    }

    public function updateClasses(): void
    {
        $this->info('>>> Updating models');
        Artisan::call('reg:model');
        Session::getModelManager()->clean();
        $this->info('>>> Updating console commands');
        Artisan::call('init_commands');
        Artisan::call('reg:command');
    }

}
