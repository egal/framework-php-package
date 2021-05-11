<?php

namespace EgalFramework\CommandLine;

use EgalFramework\CommandLine\Exceptions\Exception;
use EgalFramework\Common\Interfaces\CommandManagerInterface;
use EgalFramework\Common\Session;

class CommandManager implements CommandManagerInterface
{

    private string $commandFile;

    /** @var string[] */
    public array $commands;

    /**
     * CommandManager constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->commandFile = Session::getRegistry()->get('AppPath') . '/commands.json';
        $this->load();
    }

    /**
     * @throws Exception
     */
    private function load(): void
    {
        if (!file_exists($this->commandFile)) {
            $this->commands = [];
            return;
        }
        $data = json_decode(file_get_contents($this->commandFile), true);
        if (json_last_error()) {
            throw new Exception('JSON can\'t be decoded in commands.json: ' . json_last_error_msg());
        }
        $this->commands = $data;
    }

    public function register(string $path): void
    {
        $this->commands[] = $path;
        $this->save();
    }

    private function save(): void
    {
        file_put_contents(
            $this->commandFile, json_encode(
                array_values(array_unique($this->commands)), JSON_PRETTY_PRINT
            )
        );
    }

    public function clean(): void
    {
        $changed = false;
        foreach ($this->commands as $key => $command) {
            if (!class_exists($command)) {
                unset($this->commands[$key]);
                $changed = true;
            }
        }
        if (!$changed) {
            return;
        }
        $this->save();
    }

    public function getScripts(): array
    {
        return $this->commands;
    }

}
