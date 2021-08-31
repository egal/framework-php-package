<?php

declare(strict_types=1);

namespace Egal\CodeGenerator\Commands;

/**
 * The class of the console command for generating the event.
 */
class EventServiceProviderMakeCommand extends MakeCommand
{

    /**
     * @var string
     */
    protected $signature = 'egal:make:event-service-provider';

    /**
     * @var string
     */
    protected $description = 'Event service provider class generating';

    protected string $stubFileBaseName = 'event_service_provider';

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $this->fileBaseName = 'EventServiceProvider';
        $this->filePath = base_path('app/Providers') . '/' . $this->fileBaseName . '.php';
        $this->writeFile();
    }

}
