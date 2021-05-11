<?php

namespace EgalFramework\CommandLine\Commands;

use EgalFramework\Common\Session;
use Illuminate\Console\Command;

class RegisterModel extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'reg:model {modelName?}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Register a model, register all found models without params';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modelManager = Session::getModelManager();
        $name = $this->argument('modelName');
        if (!is_null($name)) {
            $modelManager->register($name);
            return;
        }
        foreach ($modelManager->getModelFiles() as $model) {
            $modelManager->register($model);
        }
    }

}
