<?php

namespace Egal\Core\Traits;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait PcntlSignal
{

    private array $interceptionSignals = [SIGTERM, SIGINT, SIGHUP, SIGQUIT];

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->interceptionSignals as $signal) {
            pcntl_signal($signal, static fn() => $this->stop());
        }
        return parent::execute($input, $output);
    }

    abstract protected function stop(): void;

}
