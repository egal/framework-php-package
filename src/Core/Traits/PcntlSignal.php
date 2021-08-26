<?php

namespace Egal\Core\Traits;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait PcntlSignal
{

    # TODO: Перенести в static класс
    private array $signalProcessingMethods = [
        SIGTERM => 'stopCommand',
        SIGINT => 'stopCommand',
        SIGHUP => 'stopCommand',
        SIGQUIT => 'stopCommand',
    ];

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        # TODO: Защита от использования в классах не являющимися SimphonyCommand
        foreach ($this->signalProcessingMethods as $signal => $processingMethod) {
            pcntl_signal($signal, [&$this, $processingMethod]);
        }
        /** @noinspection PhpMultipleClassDeclarationsInspection */
        return parent::execute($input, $output);
    }

}
