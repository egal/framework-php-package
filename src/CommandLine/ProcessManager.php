<?php

namespace EgalFramework\CommandLine;

use Illuminate\Support\Facades\Log;

class ProcessManager
{

    /** @var string[] */
    private array $pidToMap;

    /** @var string[] */
    private array $mapToPid;

    public function __construct()
    {
        $this->pidToMap = $this->mapToPid = [];
    }

    public function set(int $pid, string $queueName): void
    {
        if (isset($this->mapToPid[$queueName])) {
            unset($this->pidToMap[$this->mapToPid[$queueName]]);
        }
        $this->mapToPid[$queueName] = $pid;
        $this->pidToMap[$pid] = $queueName;
    }

    public function killAll(int $signal): void
    {
        foreach ($this->mapToPid as $queue => $pid) {
            posix_kill($pid, $signal);
            pcntl_waitpid($pid, $status);
        }
    }

    /**
     * @return string[]
     */
    public function getMap(): array
    {
        return $this->mapToPid;
    }

    public function hasPoolProcessor(string $name): bool
    {
        return isset($this->mapToPid[$name]);
    }

    public function empty(): bool
    {
        return empty($this->mapToPid);
    }

    public function getByPid(int $pid): ?string
    {
        return isset($this->pidToMap[$pid])
            ? $this->pidToMap[$pid]
            : null;
    }

    public function getByPool(string $name): ?int
    {
        return isset($this->mapToPid[$name])
            ? $this->mapToPid[$name]
            : null;
    }

    public function killProcess(int $pid): void
    {
        posix_kill($pid, SIGTERM);
    }

    /**
     * Polling function, it checks with special kill -0 signal that checks if the child can get signals
     * @return ?int
     */
    public function getDiedPid(): ?int
    {
        foreach ($this->mapToPid as $pid) {
            switch (pcntl_waitpid($pid, $status, WNOHANG | WUNTRACED)) {
                case $pid:
                    return $pid;
                    break;
                case 0:
                    if (!pcntl_wifstopped($status)) {
                        break;
                    }
                    Log::error(
                        posix_kill($pid, SIGKILL)
                            ? 'Killed zombie ' . $pid
                            : 'Failed to kill ' . $pid . ': ' . posix_strerror(posix_get_last_error())
                    );
                    return $pid;
                    break;
                default:
                    Log::error('Something went terribly wrong with process ' . $pid);
            }
        }
        return null;
    }

    public function deleteByPid(int $pid): void
    {
        if (!isset($this->pidToMap[$pid])) {
            return;
        }
        $queueName = $this->pidToMap[$pid];
        unset($this->pidToMap[$pid]);
        unset($this->mapToPid[$queueName]);
    }

}
