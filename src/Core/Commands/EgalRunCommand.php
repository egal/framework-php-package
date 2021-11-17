<?php /** @noinspection PhpMissingFieldTypeInspection */

declare(ticks=1);

namespace Egal\Core\Commands;

use Egal\Core\Bus\Bus;
use Egal\Core\Traits\PcntlSignal;
use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class EgalRunCommand extends Command
{

    use PcntlSignal;

    protected $signature = 'egal:run';

    protected $description = 'Start service';

    public function handle(): void
    {
        $this->info('Starting Listener!');
        cli_set_process_title(config('app.service_name') . '_listener');
        Bus::instance()->startProcessingMessages();
        Bus::instance()->processMessages();
    }

    protected function stop(): void
    {
        $this->info('Killing Listener!');
        Bus::instance()->stopProcessingMessages();
        exit;
    }

}
