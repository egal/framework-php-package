<?php

declare(strict_types=1);

namespace Egal\CodeGenerator\Commands;

use Egal\Model\Metadata\ModelMetadata;
use Illuminate\Support\Str;

class MigrationUpdateMakeCommand extends MakeCommand
{

    /**
     * @var string
     */
    protected $signature = 'egal:make:migration-update
                            {model-name : The name of the model by which the migration is generated}
                           ';

    /**
     * @var string
     */
    protected $description = 'Generating of a migration class from an existing model';

    protected string $stubFileBaseName = 'migration.update';

    private string $className;

    private string $tableName;

    /**
     * @var mixed[] Array of prepared fields for creating migration.
     */
    private array $tableFields = [];

    /**
     * @var string[] Array: field => type from the "{@property}" tag of the model.
     */
    private array $fieldsTypes = [];

    /**
     * @var mixed[] Array of validation rules from the "{@validation-rules}" tag of the model.
     */
    private array $validationRules;

    /**
     * @var string[] Array of key fields from the "{@primary-key}" tag of the model.
     */
    private array $primaryKeys = [];

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $modelName = trim((string) $this->argument('model-name'));
        $this->tableName = Str::snake(Str::plural($modelName));

        $modelMetadata = new ModelMetadata('App\\Models\\' . $modelName);

        $this->validationRules = $modelMetadata->getValidationRules();
        $this->fieldsTypes = $modelMetadata->getFieldsWithTypes();
        $this->primaryKeys = $modelMetadata->getPrimaryKeys();

        $this->className = 'Update' . Str::plural($modelName) . 'Table';
        $this->fileBaseName = Str::snake(date('Y_m_d_His') . $this->className);
        $this->filePath = base_path('database/migrations') . '/' . $this->fileBaseName . '.php';

        $this->passVariables();
        $this->writeFile();
    }

    private function passVariables(): void
    {
        $this->setFileContents('{{ class }}', $this->className);
        $this->setFileContents('{{ table }}', $this->tableName);

        $body = implode("\n\t\t\t", $this->tableFields);
        $this->setFileContents('{{ body }}', $body);
    }

}
