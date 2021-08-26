<?php

declare(strict_types=1);

namespace Egal\CodeGenerator\Commands;

/**
 * Класс консольной комманды генерации события.
 */
class RuleMakeCommand extends MakeCommand
{

    /**
     * @var string
     */
    protected $signature = 'egal:make:rule
                            {name : Class name}
                           ';

    /**
     * @var string
     */
    protected $description = 'Generating a validation rule class';

    protected string $stubFileBaseName = 'rule';

    /**
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
