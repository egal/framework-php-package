<?php

namespace Egal\CodeGenerator\Commands;

use Illuminate\Support\Str;
use Exception;

class MigrationDeleteMakeCommand extends MakeCommand
{

    protected $signature = 'egal:make:migration-delete
                            {model-name : Название модели по которой генерируем миграцию удаления таблицы}
                           ';

    protected $description = 'Генерация класса миграции удаления таблицы по миграции';

    protected string $stubFileBaseName = 'migration.delete';

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $modelName = trim((string)$this->argument('model-name'));
        $tableName = Str::snake(Str::plural($modelName));

        $className = 'Delete' . Str::plural($modelName) . 'Table';
        $this->fileBaseName = Str::snake(date('Y_m_d_His') . $className);
        $this->filePath = base_path('database/migrations') . '/' . $this->fileBaseName . '.php';

        $this->setFileContents('{{ class }}', $className);
        $this->setFileContents('{{ table }}', $tableName);

        $this->writeFile();
    }

}
