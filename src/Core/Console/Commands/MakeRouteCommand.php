<?php

namespace Egal\Core\Console\Commands;

use Illuminate\Support\Str;

class MakeRouteCommand extends MakeCommand
{
    protected $signature = 'egal:make:route
                            {model-name : Model name}
                           ';

    protected $description = 'Generating of a rest routes from an existing model';

    protected string $stubFileBaseName = 'route';

    public function handle(): void
    {
        $this->filePath = base_path('routes/api.php');
        $model = (string)$this->argument('model-name');

        if (!is_dir(dirname($this->filePath))) {
            mkdir(dirname($this->filePath));
        }

        $this->fileContents = PHP_EOL . str_replace('{{ model }}', $model, $this->fileContents);
        file_put_contents($this->filePath, $this->fileContents, FILE_APPEND);

        $file = Str::replaceFirst(base_path() . '/', '', $this->filePath);
        $this->line('<info>Result File:</info> ' . $file);    }

}
