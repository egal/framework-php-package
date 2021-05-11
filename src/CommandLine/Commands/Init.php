<?php

namespace EgalFramework\CommandLine\Commands;

use EgalFramework\Common\Session;
use Illuminate\Console\Command;

class Init extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'init';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Init project .env file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dir = Session::getRegistry()->get('BasePath') . '/';
        $appName = $this->ask('What service name should be?', basename($dir));
        $this->info('Service name will be ' . $appName . '. You can change it in .env file, parameter APP_NAME');
        $dbName = $this->ask('What DB name should be?', $appName);
        $this->info('DB name will be ' . $appName . '. You can change it in .env file, parameter DB_DATABASE');
        file_put_contents(
            $dir . '.env',
            str_replace(['{APP_NAME}', '{DB_NAME}'], [$appName, $dbName],
                file_get_contents(__DIR__ . '/../../../stubs/env.stub')
            )
        );
    }

}
