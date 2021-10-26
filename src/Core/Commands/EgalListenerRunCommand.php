<?php

namespace Egal\Core\Commands;

use Egal\Core\Bus\Bus;
use Egal\Core\Traits\PcntlSignal;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Class EgalListenerRunCommand
 * @package Egal\Core\Commands
 */
class EgalListenerRunCommand extends Command
{

    use PcntlSignal;

    protected $signature = 'egal:listener:run';

    protected $description = 'Start service queue listener';

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $this->info('Starting Listener!');

        cli_set_process_title(
            words_to_separated_lower_case(config('app.service_name'), 'listener' . '#' . getmypid())
        );

        Bus::getInstance()->startProcessingMessages();
        Bus::getInstance()->processMessages();
    }

    public function stopCommand()
    {
        $this->info('Killing Listener!');
        Bus::getInstance()->stopProcessingMessages();
        exit;
    }

}
