<?php

declare(strict_types=1);

namespace Egal\CodeGenerator\Commands;

use Egal\CodeGenerator\Exceptions\ConfigMakeException;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class QueueConfigMakeCommand extends Command
{

    /**
     * @var string
     */
    protected $signature = 'egal:make:config {config_name : The name of configuration to be generated}';

    /**
     * @var string
     */
    protected $description = 'Generating configuration';

    /**
     * @throws \Egal\CodeGenerator\Exceptions\ConfigMakeException
     */
    public function handle(): void
    {
        $makeFunction = Str::camel('make_' . $this->argument('config_name'));

        try {
            $this->$makeFunction();
        } catch (Exception $exception) {
            throw new ConfigMakeException();
        }
    }

    /**
     * @throws \Exception
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function makeQueue(): void
    {
        $this->copyConfig(
            __DIR__ . '/stubs/config.queue.stub',
            base_path('config/queue.php'),
        );
    }

    /**
     * @throws \Egal\CodeGenerator\Exceptions\ConfigMakeException
     */
    private function copyConfig(string $from, string $to): void
    {
        $isConfirmed = $this->confirm('Configuration file already exists. Replace?', false);

        if (file_exists($to) && !$isConfirmed) {
            $this->warn('Canceled!');

            return;
        }

        if (!copy($from, $to)) {
            throw new ConfigMakeException('File copy error! From ' . $from . ' to ' . $to . '.');
        }
    }

}
