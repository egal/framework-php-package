<?php

declare(strict_types=1);

namespace Egal\CodeGenerator\Commands;

use Illuminate\Support\Str;

class MigrationDeleteMakeCommand extends MakeCommand
{

    /**
     * @var string
     */
    protected $signature = 'egal:make:migration-delete
                            {model-name : The name of the model by which the table drop migration is generated}
                           ';

    /**
     * @var string
     */
    protected $description = 'Generating of a table drop migration class';

    protected string $stubFileBaseName = 'migration.delete';

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $modelName = trim((string) $this->argument('model-name'));
        $tableName = Str::snake(Str::plural($modelName));

        $className = 'Delete' . Str::plural($modelName) . 'Table';
        $this->fileBaseName = Str::snake(date('Y_m_d_His') . $className);
        $this->filePath = base_path('database/migrations') . '/' . $this->fileBaseName . '.php';

        $this->setFileContents('{{ class }}', $className);
        $this->setFileContents('{{ table }}', $tableName);

        $this->writeFile();
    }

}
