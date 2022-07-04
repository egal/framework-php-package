<?php

namespace Egal\Core\Console\Commands;

use Egal\Core\Database\Model;
use Illuminate\Support\Str;

class MakeCreateMigrationCommand extends MakeCommand
{
    protected $signature = 'egal:make:create-migration
                            {model-name : Model name}
                           ';

    protected $description = 'Generating of a migration class from an existing model';

    protected string $stubFileBaseName = 'migration.create';

    private string $className;
    private string $tableName;
    private array $tableFields = [];
    private array $fieldsTypes = [];
    private array $validationRules;
    private ?string $primaryKey;

    public function handle(): void
    {
        $modelName = $this->argument('model-name');
        $modelClass = 'App\\Models\\' . trim((string)$modelName);

        /** @var Model $model */
        $model = new $modelClass();
        $this->tableName = $model->getTable();

        $metadata = $model->getMetadata();

        $this->className = 'Create' . Str::plural($modelName) . 'Table';
        $this->fileBaseName = Str::snake(date('Y_m_d_His') . $this->className);
        $this->filePath = base_path('database/migrations') . '/' . $this->fileBaseName . '.php';

    }

}
