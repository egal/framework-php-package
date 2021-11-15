<?php

declare(strict_types=1);

namespace Egal\Core\Commands;

use Egal\Core\Bus\Bus;
use Egal\Core\Traits\PcntlSignal;
use Illuminate\Console\Command;

/**
 * Class EgalListenerRunCommand
 *
 * @package Egal\Core\Commands
 */
class EgalListenerRunCommand extends Command
{

    use PcntlSignal;

    protected $signature = 'egal:listener:run';

    protected $description = 'Start service queue listener';

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $this->info('Starting Listener!');

        cli_set_process_title(
            words_to_separated_lower_case(config('app.service_name'), 'listener#' . getmypid())
        );

        Bus::getInstance()->startProcessingMessages();
        Bus::getInstance()->processMessages();
    }

    public function stopCommand(): void
    {
        $this->info('Killing Listener!');
        Bus::getInstance()->stopProcessingMessages();
        exit;
    }

}
