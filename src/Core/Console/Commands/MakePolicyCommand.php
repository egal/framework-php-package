<?php

namespace Egal\Core\Console\Commands;

class MakePolicyCommand extends MakeCommand
{
    protected $signature = 'egal:make:policy
                            {model-name : Model name}
                           ';

    protected $description = 'Policy class generating';

    protected string $stubFileBaseName = 'policy';

    public function handle(): void
    {
        $model = (string)$this->argument('model-name');
        $this->fileBaseName = $model . 'Policy';
        $this->filePath = base_path('app/Http/Policies') . '/' . $this->fileBaseName . '.php';
        $this->fileContents = str_replace('{{ class }}', $this->fileBaseName, $this->fileContents);
        $this->fileContents = str_replace('{{ model }}', $model, $this->fileContents);
        $this->writeFile();
    }

}
