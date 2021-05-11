<?php

namespace EgalFramework\CommandLine\Commands;

use EgalFramework\Common\Session;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeMetadata extends Command
{

    /** @var string */
    private string $metaDataPath;

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'mk:metadata {modelName}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Generate new metadata file';

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->metaDataPath = Session::getRegistry()->get('AppPath') . '/Metadata/';
    }

    /**
     * Execute the console command.
     * @throws Exception
     */
    public function handle()
    {
        $modelPath = $this->argument('modelName');
        $nameArray = array_filter(explode('/', $modelPath));
        $name = array_pop($nameArray);
        $metadataNamespace = 'App\\Metadata';
        if (!empty($nameArray)) {
            $metadataNamespace .= '\\' . implode('\\', $nameArray);
        }

        if (!file_exists($this->metaDataPath . '/' . implode('/', $nameArray))) {
            mkdir($this->metaDataPath . '/' . implode('/', $nameArray), 0750, true);
        }
        $this->createFile($name, implode('/', $nameArray), $metadataNamespace);

        Session::getModelManager()->register($modelPath);
        $this->info('Metadata ' . $name . ' successfully created');
    }

    /**
     * @param string $name
     * @param string $path
     * @param string $metadataNamespace
     * @throws Exception
     */
    public function createFile(string $name, string $path, string $metadataNamespace): void
    {
        $file = $this->metaDataPath . '/' . $path . '/' . $name . '.php';
        if (file_exists($file)) {
            throw new Exception('MetaData is already exists');
        }
        $data = file_get_contents(__DIR__ . '/../../../stubs/Metadata.stub');
        $data = str_replace('MetadataNamespace', $metadataNamespace, $data);
        $data = str_replace('metadata_table_name', Str::snake(Str::plural($name)),
            str_replace('MetadataClassName', $name, $data)
        );
        file_put_contents($file, $data);
    }

}
