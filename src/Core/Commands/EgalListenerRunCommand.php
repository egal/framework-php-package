<?php

declare(strict_types=1);

namespace Egal\Core\Commands;

/**
 * @deprecated Since all the logic of this command goes to {@see EgalRunCommand}
 */
class EgalListenerRunCommand extends EgalRunCommand
{

    protected $signature = 'egal:listener:run';

    protected $description = 'Start service [deprecated]';

    public function handle(): void
    {
        $this->warn('This command was deprecated!');

        parent::handle();
    }

}
