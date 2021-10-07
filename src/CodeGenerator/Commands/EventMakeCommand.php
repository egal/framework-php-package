<?php

declare(strict_types=1);

namespace Egal\CodeGenerator\Commands;

use Egal\CodeGenerator\Exceptions\EventMakeException;

class EventMakeCommand extends MakeCommand
{

    /**
     * @var string
     */
    protected $signature = 'egal:make:event
                            {event-name     : Event name}
                            {--g|global     : Generate global event}
                            {--c|centrifugo : Generate centrifugo event}
                           ';

    /**
     * @var string
     */
    protected $description = 'Event class generating';

    protected string $stubFileBaseName = 'event';

    public function handle(): void
    {
        if ($this->option('global') && $this->option('centrifugo')) {
            throw new EventMakeException('Unacceptable to specify simultaneously flags --global and --centrifugo');
        }

        $fileBaseName = (string) $this->argument('event-name');

        $extends = $this->option('global')
            ? 'GlobalEvent'
            : ($this->option('centrifugo') ? 'CentrifugoEvent' : 'Event');
        $use = $this->option('global')
            ? 'Egal\Core\Events\GlobalEvent'
            : ($this->option('centrifugo') ? 'Egal\Centrifugo\CentrifugoEvent' : 'Egal\Core\Events\Event');

        $this->fileBaseName = str_ends_with($fileBaseName, $extends)
            ? $fileBaseName
            : $fileBaseName . $extends;
        $this->filePath = base_path('app/Events') . '/' . $this->fileBaseName . '.php';
        $this->setFileContents('{{ class }}', $this->fileBaseName);
        $this->setFileContents('{{ extends }}', $extends);
        $this->setFileContents('{{ use }}', $use);
        $this->writeFile();
    }

}
