<?php

namespace Egal\CodeGenerator\Commands;

use Egal\Model\Metadata\ModelMetadata;
use Exception;
use Illuminate\Support\Str;

class MigrationCreateMakeCommand extends MakeCommand
{

    protected $signature = 'egal:make:migration-create
                            {model-name : Название модели по которой генерируем миграцию}
                           ';

    protected $description = 'Генерация класса миграции по существующей модели';

    protected string $stubFileBaseName = 'migration.create';

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
        // todo
        // $relations = $modelMetadata->getRelations();

        $this->className = 'Create' . Str::plural($modelName) . 'Table';
        $this->fileBaseName = Str::snake(date('Y_m_d_His') . $this->className);
        $this->filePath = base_path('database/migrations') . '/' . $this->fileBaseName . '.php';

        $this->passVariables();
        $this->writeFile();
    }

    private function passVariables()
    {
        $this->setFileContents('{{ class }}', $this->className);
        $this->setFileContents('{{ table }}', $this->tableName);

        $this->parseFields();
        $body = implode("\n\t\t\t", $this->tableFields);
        $this->setFileContents('{{ body }}', $body);
    }

    /**
     * Формируем массив всех полей с типами и правилами для создания новой таблицы.
     */
    private function parseFields()
    {
        foreach ($this->fieldsTypes as $field => $type) {

            // Типы полей из валидации в приоритете!
            $this->parseFieldsByValidationRules($field);

            // Если из правил валидации ничего не взяли, то берем тип поля из типа указанного в "@property"
            if ($type && !isset($this->tableFields[$field])) {
                $this->parseFieldsByProperties($field, $type);
            }

            // Добавляем правила валидации для полей
            $this->parseValidationRules($field);

            // Добавление primary для поля
            if (isset($this->tableFields[$field]) && in_array($field, $this->primaryKeys)) {
                $this->tableFields[$field] = str_replace(';', '->primary();', $this->tableFields[$field]);
            }
        }
    }

    /**
     * Вытаскиваем типы полей из правил валидации
     *
     * @param string $field
     */
    private function parseFieldsByValidationRules(string $field)
    {
        if (empty($this->validationRules[$field])) {
            return;
        }

        if (in_array('string', $this->validationRules[$field])) {
            $this->tableFields[$field] = '$table->string(\'' . $field . '\');';
        } elseif (in_array('integer', $this->validationRules[$field])) {
            $this->tableFields[$field] = (str_contains($field, '_id'))
                ? '$table->unsignedBigInteger(\'' . $field . '\');'
                : '$table->bigInteger(\'' . $field . '\');';
        } elseif (in_array('numeric', $this->validationRules[$field])) {
            $this->tableFields[$field] = '$table->double(\'' . $field . '\');';
        } elseif (in_array('boolean', $this->validationRules[$field])) {
            $this->tableFields[$field] = '$table->boolean(\'' . $field . '\');';
        } elseif (str_contains(implode(' ___ ', $this->validationRules[$field]), 'date')) {
            $this->tableFields[$field] = '$table->timestamp(\'' . $field . '\');';
        } elseif (in_array('uuid', $this->validationRules[$field])) {
            $this->tableFields[$field] = '$table->uuid(\'' . $field . '\');';
        }
    }

    /**
     * Заполняем поле из типов полей указанным в "@property"
     *
     * @param string $field
     * @param string $type
     */
    private function parseFieldsByProperties(string $field, string $type)
    {
        switch ($type) {
            case 'int':
                $this->tableFields[$field] = (str_contains($field, '_id'))
                    ? '$table->unsignedBigInteger(\'' . $field . '\');'
                    : '$table->bigInteger(\'' . $field . '\');';
                break;
            case 'float':
                $this->tableFields[$field] = '$table->double(\'' . $field . '\');';
                break;
            case 'string':
                $this->tableFields[$field] = '$table->string(\'' . $field . '\');';
                break;
            case 'bool':
                $this->tableFields[$field] = '$table->boolean(\'' . $field . '\');';
                break;
            case '\Carbon':
                $this->tableFields[$field] = '$table->timestamp(\'' . $field . '\');';
                break;
        }
    }

    /**
     * Заполняем правила для полей.
     *
     * @param string $field
     */
    private function parseValidationRules(string $field)
    {
        if (empty($this->validationRules[$field])) {
            return;
        }

        if (in_array('unique:' . $this->tableName, $this->validationRules[$field])) {
            $this->tableFields[$field] = str_replace(';', '->unique();', $this->tableFields[$field]);
        }

        if (in_array('nullable', $this->validationRules[$field])) {
            $this->tableFields[$field] = str_replace(';', '->nullable();', $this->tableFields[$field]);
        }

//        // todo: max:255
//        if (in_array($this->validationRules[$field], ['string'])) {
//
//        }
    }

}
