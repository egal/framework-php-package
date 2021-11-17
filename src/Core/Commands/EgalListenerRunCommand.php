<?php

declare(strict_types=1);

namespace Egal\Core\Commands;

/**
 * @deprecated
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
