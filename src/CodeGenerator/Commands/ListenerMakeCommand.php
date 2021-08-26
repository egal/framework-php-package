<?php

declare(strict_types=1);

namespace Egal\CodeGenerator\Commands;

/**
 * The class of the console command for generating the event.
 */
class ListenerMakeCommand extends MakeCommand
{

    /**
     * @var string
     */
    protected $signature = 'egal:make:listener
                            {name : Listener name}
                            {--g|global : Global event listener}
                           ';

    /**
     * @var string
     */
    protected $description = 'Event listener class generating';

    protected string $stubFileBaseName = 'listener';

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $fileBaseName = (string) $this->argument('name');
        $extends = $this->option('global')
            ? 'GlobalEventListener'
            : 'EventListener';
        $this->fileBaseName = str_ends_with($fileBaseName, 'Listener')
            ? $fileBaseName
            : $fileBaseName . 'Listener';
        $this->filePath = base_path('app/Listeners') . '/' . $this->fileBaseName . '.php';
        $this->setFileContents('{{ class }}', $this->fileBaseName);
        $this->setFileContents('{{ extends }}', $extends);
        $this->setFileContents(
            '{{ handle_parameters }}',
            $this->option('global') ? 'array $data' : ''
        );
        $this->writeFile();
    }

}
