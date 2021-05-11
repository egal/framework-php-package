<?php

namespace EgalFramework\CommandLine\Commands;

use EgalFramework\Common\Session;
use Illuminate\Console\Command;

class RegisterCommand extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'reg:command {commandName?}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Register command. If command is not specified - scans app/Console/Commands directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->hasArgument('commandName')) {
            $name = $this->argument('commandName');
            if (strpos($name, '\\') === false) {
                $name = '\\App\\Console\\Commands\\' . $name;
            }
            Session::getCommandManager()->register($name);
            return;
        }
        foreach (scandir(Session::getRegistry()->get('AppPath') . '/Console/Commands') as $file) {
            if (in_array($file, ['.', '..']) || !preg_match('/\.php$/', $file)) {
                continue;
            }
            $class = '\\App\\Console\\Commands\\' . preg_replace('/\.php$/', '', $file);
            if (!class_exists($class)) {
                continue;
            }
            Session::getCommandManager()->register($class);
        }
    }

}
