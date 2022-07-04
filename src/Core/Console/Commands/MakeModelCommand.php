<?php

namespace Egal\Core\Console\Commands;

class MakeModelCommand extends MakeCommand
{
    protected $signature = 'egal:make:model
                            {model-name : Model name}
                           ';

    protected $description = 'Model class generating';

    protected string $stubFileBaseName = 'model';

    public function handle(): void
    {
        $this->fileBaseName = (string) $this->argument('model-name');
        $this->filePath = base_path('app/Models') . '/' . $this->fileBaseName . '.php';
        $this->fileContents = str_replace('{{ class }}', $this->fileBaseName, $this->fileContents);
        $this->writeFile();
    }

}
