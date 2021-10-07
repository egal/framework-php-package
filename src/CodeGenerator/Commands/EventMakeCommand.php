<?php

declare(strict_types=1);

namespace Egal\CodeGenerator\Commands;

use Egal\CodeGenerator\Exceptions\EventMakeException;

/**
 * The class of the console command for generating the event.
 */
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

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        if ($this->option('global') && $this->option('centrifugo')) {
            EventMakeException::make('Unacceptable to specify simultaneously flags --g and --с');
        }

        $fileBaseName = (string) $this->argument('event-name');
        $flagForExtends = $this->option('global') ?? $this->option('centrifugo');

        switch ($flagForExtends) {
            case 'a':
                $extends = 'CentrifugoEvent';
                break;

            case 'b':
                $extends = 'GlobalEvent';
                break;

            default:
                $extends = 'Event';
                break;
        }

        $this->fileBaseName = str_ends_with($fileBaseName, $extends)
            ? $fileBaseName
            : $fileBaseName . $extends;
        $this->filePath = base_path('app/Events') . '/' . $this->fileBaseName . '.php';
        $this->setFileContents('{{ class }}', $this->fileBaseName);
        $this->setFileContents('{{ extends }}', $extends);
        $this->writeFile();
    }

}
