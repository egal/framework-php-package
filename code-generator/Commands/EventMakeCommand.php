<?php

declare(strict_types=1);

namespace Egal\CodeGenerator\Commands;

/**
 * The class of the console command for generating the event.
 */
class EventMakeCommand extends MakeCommand
{

    /**
     * @var string
     */
    protected $signature = 'egal:make:event
                            {event-name : Event name}
                            {--g|global : Generate global event}
                           ';

    /**
     * @var string
     */
    protected $description = 'Event class generating';

    protected string $stubFileBaseName = 'event';

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $fileBaseName = (string) $this->argument('event-name');
        $extends = $this->option('global')
            ? 'GlobalEvent'
            : 'Event';
        $this->fileBaseName = str_ends_with($fileBaseName, $extends)
            ? $fileBaseName
            : $fileBaseName . $extends;
        $this->filePath = base_path('app/Events') . '/' . $this->fileBaseName . '.php';
        $this->setFileContents('{{ class }}', $this->fileBaseName);
        $this->setFileContents('{{ extends }}', $extends);
        $this->writeFile();
    }

}
