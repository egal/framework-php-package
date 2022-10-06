<?php

declare(strict_types=1);

namespace Egal\CodeGenerator\Commands;

/**
 * A file is generated {@see Model}.
 */
class ModelMakeCommand extends MakeCommand
{

    /**
     * @var string
     */
    protected $signature = 'egal:make:model
                            {model-name : Model name}
                           ';

    /**
     * @var string
     */
    protected $description = 'Model class generating';

    protected string $stubFileBaseName = 'model';

    /**
     * Console command action.
     *
     * Writes the {@see Model} file with the name specified when the command is called into the PROJECT_PATH/app/Models
     * directory with the contents {@see model.stub}.
     *
     * In the content of the final file, the lines '{{ class }}' will be replaced with the name specified
     * when the command is called.
     *
     * @throws \Exception
     */
    public function handle(): void
    {
        $this->fileBaseName = (string) $this->argument('model-name');
        $this->filePath = base_path('app/Models') . '/' . $this->fileBaseName . '.php';
        $this->fileContents = str_replace('{{ class }}', $this->fileBaseName, $this->fileContents);
        $this->writeFile();
    }

}
