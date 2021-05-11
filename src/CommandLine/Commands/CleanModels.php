<?php

namespace EgalFramework\CommandLine\Commands;

use EgalFramework\Common\Session;
use Illuminate\Console\Command;

class CleanModels extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'clean:models';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Remove models which does not exist anymore';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modelManager = Session::getModelManager();
        $modelManager->clean();
    }

}
