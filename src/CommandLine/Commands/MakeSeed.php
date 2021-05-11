<?php

namespace EgalFramework\CommandLine\Commands;

use EgalFramework\Common\Session;
use Exception;
use Illuminate\Console\Command;

class MakeSeed extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'mk:seed {modelName}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Create seed for a model';

    /**
     * Execute the console command.
     * @throws Exception
     */
    public function handle()
    {
        $modelName = $this->argument('modelName');
        $directory = dirname(Session::getRegistry()->get('SeedPath') . '/' . $modelName . '.php');
        if (!file_exists($directory)) {
            mkdir($directory, 0750, true);
        }
        if (file_exists(Session::getRegistry()->get('SeedPath') . '/' . $modelName . '.php')) {
            throw new Exception('Seed is already exist');
        }
        $data = file_get_contents(__DIR__ . '/../../../stubs/Seed.stub');
        file_put_contents(
            Session::getRegistry()->get('SeedPath') . '/' . $modelName . '.php',
            str_replace('{ModelName}',
                str_replace('/', '\\', $modelName),
                str_replace('{SeedName}', basename($modelName) . 'Seeder', $data)
            )
        );
    }

}
