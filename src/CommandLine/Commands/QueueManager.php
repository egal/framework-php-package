<?php

namespace EgalFramework\CommandLine\Commands;

use EgalFramework\Common\Session;
use Illuminate\Console\Command;

class QueueManager extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'mq:pool
    {--l|list : List listener pools}
    {--k|kill= : Delete pool}
    {--c|create= : Create pool}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Generate new model';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $kill = $this->option('kill');
        if ($kill) {
            $this->kill($kill);
        }
        $create = $this->option('create');
        if ($create) {
            $this->create($create);
        }
        $list = $this->option('list');
        if ($list) {
            $this->list();
        }
    }

    /**
     * @param string $name
     */
    private function kill(string $name): void
    {
        Session::getQueue()->deletePool($name);
    }

    public function create(string $name): void
    {
        Session::getQueue()->createPool($name);
    }

    private function list(): void
    {
        foreach (Session::getQueue()->getPools() as $pool) {
            echo $pool . PHP_EOL;
        }
    }
}
