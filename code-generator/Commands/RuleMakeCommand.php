<?php

declare(strict_types=1);

namespace Egal\CodeGenerator\Commands;

/**
 * Класс консольной комманды генерации события.
 */
class RuleMakeCommand extends MakeCommand
{

    /**
     * Сигнатура конгсольной команды.
     *
     * @var string
     */
    protected $signature = 'egal:make:rule
                            {name : Название класса}
                           ';

    /**
     * Описание консольной окманды.
     *
     * @var string
     */
    protected $description = 'Генерация класса правила валидации';

    /**
     * Базовое названия файла-заглушки.
     */
    protected string $stubFileBaseName = 'rule';

    /**
     * Действие консольной команды.
     *
     * @throws \Exception
     */
    public function handle(): void
    {
        $fileBaseName = (string) $this->argument('name');
        $extends = 'Rule';
        $this->fileBaseName = str_ends_with($fileBaseName, $extends)
            ? $fileBaseName
            : $fileBaseName . $extends;
        $this->filePath = base_path('app/Rules') . '/' . $this->fileBaseName . '.php';
        $this->setFileContents('{{ class }}', $this->fileBaseName);
        $this->writeFile();
    }

}
