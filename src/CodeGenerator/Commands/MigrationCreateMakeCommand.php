<?php

declare(strict_types=1);

namespace Egal\CodeGenerator\Commands;

use Egal\Model\Metadata\ModelMetadata;
use Exception;
use Illuminate\Support\Str;

class MigrationCreateMakeCommand extends MakeCommand
{

    /**
     * @var string
     */
    protected $signature = 'egal:make:migration-create
                            {model-name : The name of the model by which the migration is generated}
                           ';

    /**
     * @var string
     */
    protected $description = 'Generating of a migration class from an existing model';

    protected string $stubFileBaseName = 'migration.create';

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
     * @var mixed[] An array of validation rules from the "{@validation-rules}" tag of the model.
     */
    private array $validationRules;

    private ?string $primaryKey;

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
        $this->primaryKey = $modelMetadata->getPrimaryKey();
        $this->className = 'Create' . Str::plural($modelName) . 'Table';
        $this->fileBaseName = Str::snake(date('Y_m_d_His') . $this->className);
        $this->filePath = base_path('database/migrations') . '/' . $this->fileBaseName . '.php';

        $this->passVariables();
        $this->writeFile();
    }

    private function passVariables(): void
    {
        $this->setFileContents('{{ class }}', $this->className);
        $this->setFileContents('{{ table }}', $this->tableName);

        $this->parseFields();
        $body = implode("\n" . str_repeat(' ', 12), $this->tableFields);
        $this->setFileContents('{{ body }}', $body);
    }

    /**
     * Formation of an array of all fields with types and rules for creating a new table.
     */
    private function parseFields(): void
    {
        foreach ($this->fieldsTypes as $field => $type) {
            $this->parseFieldsByValidationRules($field);

            // If nothing was taken from the validation rules,
            // then take the field type from the type specified in "@property".
            if ($type && !isset($this->tableFields[$field])) {
                $this->parseFieldsByProperties($field, $type);
            }

            $this->parseValidationRules($field);

            // Adding primary for the field.
            if (!isset($this->tableFields[$field]) || $field !== $this->primaryKey) {
                continue;
            }

            $this->tableFields[$field] = str_replace(';', '->primary();', $this->tableFields[$field]);
        }
    }

    /**
     * Extracting field types from validation rules.
     */
    private function parseFieldsByValidationRules(string $field): void
    {
        if (!isset($this->validationRules[$field]) || count($this->validationRules[$field]) === 0) {
            return;
        }

        if (in_array('string', $this->validationRules[$field])) {
            $this->tableFields[$field] = '$table->string(\'' . $field . '\');';
        } elseif (in_array('integer', $this->validationRules[$field])) {
            $this->tableFields[$field] = str_contains($field, '_id')
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
     * Filling a field from the field types specified in "@property".
     *
     * @throws \Exception
     */
    private function parseFieldsByProperties(string $field, string $type): void
    {
        switch ($type) {
            case 'int':
                $this->tableFields[$field] = str_contains($field, '_id')
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
            default:
                throw new Exception('Invalid field type!');
        }
    }

    /**
     * Filling in the rules for the fields.
     */
    private function parseValidationRules(string $field): void
    {
        if (!isset($this->validationRules[$field]) || count($this->validationRules[$field]) === 0) {
            return;
        }

        if (in_array('unique:' . $this->tableName, $this->validationRules[$field])) {
            $this->tableFields[$field] = str_replace(';', '->unique();', $this->tableFields[$field]);
        }

        if (in_array('nullable', $this->validationRules[$field])) {
            $this->tableFields[$field] = str_replace(';', '->nullable();', $this->tableFields[$field]);
        }

        return;
    }

}
