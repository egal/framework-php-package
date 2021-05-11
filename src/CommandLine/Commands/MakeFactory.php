<?php

namespace EgalFramework\CommandLine\Commands;

use EgalFramework\Common\Session;
use Exception;
use Illuminate\Console\Command;

class MakeFactory extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'mk:factory {modelName}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Create factory for a model';

    /**
     * Execute the console command.
     * @throws Exception
     */
    public function handle()
    {
        $modelName = $this->argument('modelName');
        $path = Session::getRegistry()->get('FactoryPath') . '/' . $modelName . 'Factory.php';
        $directory = dirname($path);
        if (!file_exists($directory)) {
            mkdir($directory, 0750, true);
        }
        if (file_exists($path)) {
            throw new Exception('Factory is already exist');
        }
        $data = file_get_contents(__DIR__ . '/../../../stubs/Factory.stub');
        $data = str_replace('{ModelName}', str_replace('/', '\\', $modelName), $data);
        $data = str_replace('{Fields}', Session::getMetadata($modelName)->getFactoryFields(), $data);
        file_put_contents($path, $data);
    }

}
