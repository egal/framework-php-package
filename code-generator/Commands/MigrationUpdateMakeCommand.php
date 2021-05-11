<?php

namespace Egal\CodeGenerator\Commands;

use Egal\Model\Metadata\ModelMetadata;
use Exception;
use Illuminate\Support\Str;

/**
 * TODO: Нужно реализовать и добавить в ServiceProvider.php
 * @package Egal\Model\Commands
 */
class MigrationUpdateMakeCommand extends MakeCommand
{

    protected $signature = 'egal:make:migration-update
                            {model-name : Название модели по которой генерируем миграцию}
                           ';

    protected $description = 'Генерация класса миграции по существующей модели';

    protected string $stubFileBaseName = 'migration.update';

    private string $className;
    private string $tableName;

    /** @var array Массив подготовленных полей для создания миграции */
    private array $tableFields = [];

    /** @var array Массив: поле => тип из тега "@property" модели */
    private array $fieldsTypes = [];

    /** @var array Массив правил валидации из тега "{@validation-rules }" модели */
    private array $validationRules;

    /** @var array Массив полей ключей из тега "{@primary-key}" модели */
    private array $primaryKeys = [];

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $modelName = trim((string)$this->argument('model-name'));
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

    private function passVariables()
    {
        $this->setFileContents('{{ class }}', $this->className);
        $this->setFileContents('{{ table }}', $this->tableName);

        $body = implode("\n\t\t\t", $this->tableFields);
        $this->setFileContents('{{ body }}', $body);
    }

}
