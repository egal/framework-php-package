<?php

declare(strict_types=1, ticks=1);

namespace Egal\Core\Commands;

use Egal\Core\Bus\Bus;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EgalRunCommand extends Command
{

    protected const INTERCEPTION_SIGNALS = [SIGTERM, SIGINT, SIGHUP, SIGQUIT];

    /**
     * @var string
     */
    protected $signature = 'egal:run';

    /**
     * @var string
     */
    protected $description = 'Start service';

    public function handle(): void
    {
        $this->info('Starting...');
        cli_set_process_title(config('app.service_name') . '_listener');
        Bus::instance()->startProcessingMessages();
        Bus::instance()->processMessages();
    }

    public function stop(): void
    {
        $this->info('Stopping...');
        Bus::instance()->stopProcessingMessages();
        exit;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach (self::INTERCEPTION_SIGNALS as $signal) {
            pcntl_signal($signal, [$this, 'stop']);
        }

        return parent::execute($input, $output);
    }

}
