<?php /** @noinspection PhpMissingFieldTypeInspection */

declare(ticks=1);

namespace Egal\Core\Commands;

use Egal\Core\Traits\PcntlSignal;
use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class EgalRunCommand extends Command
{
    # TODO: In case of destruction, throw the entire queue in Exchange

    use PcntlSignal;

    protected $signature = 'egal:run
                            {--l|listeners=1 : Number of queue handlers}
                            {--s|sync-code-base : Enabling automatic restart of listeners when updating the codebase}
                           ';

    protected $description = 'Start service';

    /**
     * @var Process[]
     */
    private array $listeners = [];

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        // Называем master текущий процесс
        $masterProcessName = words_to_separated_lower_case(config('app.service_name'), 'master');
        cli_set_process_title($masterProcessName);

        $this->startNewListeners();

        while (true) {
            usleep(150000);
            if ($this->option('sync-code-base')) {
                $this->line('Codebase sync is not stable! The functionality is disabled!');
            }
            $this->restartDeadListeners();
        }
    }

    public function startNewListeners()
    {
        for ($listenerNumber = 1; $listenerNumber <= $this->option('listeners'); $listenerNumber++) {
            $this->startNewListener();
        }
    }

    public function startNewListener()
    {
        $artisan = base_path('artisan');
        $command = "php $artisan egal:listener:run";
        $process = Process::fromShellCommandline($command);
        $process->start();
        $this->listeners[] = $process;
        $this->info('Start new Listener!');
    }

    public function syncCodeBase()
    {
        $getBasePathCurrentShaSum = function () {
            $pathToSum = base_path();
            return shell_exec("
                # shellcheck disable=SC2005
                # shellcheck disable=SC2046
                echo $(
                (
                    find \"$pathToSum\" -type f -name '*.php' -print0 | sort -z | xargs -0 sha1sum
                    find \"$pathToSum\" \( -type f -o -type d \) -print0 | sort -z |
                    xargs -0 stat -c '%n %a'
                ) |
                    sha1sum
                )
            ");
        };

        if (!isset($this->basePathShaSum)) $this->basePathShaSum = $getBasePathCurrentShaSum();

        $newBasePathShaSum = $getBasePathCurrentShaSum();
        if ($this->basePathShaSum !== $newBasePathShaSum) {
            $this->warn('Code base updated!');
            $this->restartListeners();
            $this->basePathShaSum = $newBasePathShaSum;
        }
    }

    public function restartDeadListeners()
    {
        foreach ($this->listeners as $key => $listener) {
            if (!$listener->isRunning()) {
                $this->warn('Listener is dead!');
                unset($this->listeners[$key]);
                $this->startNewListener();
            }
        }
    }

    public function restartListeners()
    {
        $this->stopListeners();
        $this->startNewListeners();
    }

    public function stopListeners()
    {
        foreach ($this->listeners as $key => $listener) {
            $this->warn('Killing Listener! ' . $this->listeners[$key]->getPid());
            $this->listeners[$key]->stop();
            unset($this->listeners[$key]);
        }
    }

    public function stopCommand()
    {
        $this->info('Stopping daemon!');
        $this->stopListeners();
        exit;
    }
}
