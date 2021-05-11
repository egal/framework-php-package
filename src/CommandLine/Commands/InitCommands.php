<?php

namespace EgalFramework\CommandLine\Commands;

use EgalFramework\Common\Session;
use Illuminate\Console\Command;
use ReflectionClass;
use ReflectionException;

class InitCommands extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'init_commands';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Init artisan commands';

    /**
     * Execute the console command.
     * @throws ReflectionException
     */
    public function handle()
    {
        foreach (scandir(__DIR__, SCANDIR_SORT_NONE) as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            $name = preg_replace('/\.php$/', '', $file);
            if ((new ReflectionClass($this))->getShortName() === $name) {
                continue;
            }
            Session::getCommandManager()->register('\\EgalFramework\\CommandLine\\Commands\\' . $name);
        }
    }

}
