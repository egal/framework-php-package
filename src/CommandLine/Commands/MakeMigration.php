<?php

namespace EgalFramework\CommandLine\Commands;

use EgalFramework\Common\Interfaces\MetadataInterface;
use EgalFramework\Common\Session;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeMigration extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'mk:migration {modelName}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Generate new migration based on metadata';

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @throws Exception
     */
    public function handle()
    {
        $name = $this->argument('modelName');
        if (!Session::getModelManager()->hasMetadata($name)) {
            throw new Exception('Metadata for model ' . $name . ' not found');
        }
        $modelPath = Session::getModelManager()->getMetadataPath($name);
        /** @var MetadataInterface $metadata */
        $metadata = new $modelPath;
        $file = file_get_contents(__DIR__ . '/../../../stubs/migration.stub');
        $file = str_replace('UCTableName', Str::ucfirst(Str::camel($metadata->getTable())), $file);
        $file = str_replace('TableName', $metadata->getTable(), $file);
        $file = str_replace(
            '// Fields',
            $this->getFieldsText(Session::getModelManager()->getMetadataPath($name)),
            $file
        );
        file_put_contents($this->getMigrationPath($metadata->getTable()), $file);
    }

    /**
     * @param string $metadataName
     * @return string
     */
    private function getFieldsText(string $metadataName)
    {
        /** @var MetadataInterface $metadata */
        $metadata = new $metadataName;
        $migration = $metadata->getMigration();
        return implode(
            PHP_EOL, array_map(
                function ($elem) {
                    return str_repeat(' ', 12) . $elem;
                },
                $migration
            )
        );
    }

    /**
     * @param string $name
     * @return string
     */
    private function getMigrationPath(string $name)
    {
        return Session::getRegistry()->get('DBPath') . '/migrations/' . sprintf(
                '%s_create_%s_table.php',
                date('Y_m_d_His'),
                $name
            );
    }

}
