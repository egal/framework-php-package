<?php

namespace EgalFramework\CommandLine\Tests;

use EgalFramework\CommandLine\CommandManager;
use EgalFramework\CommandLine\ModelManager;
use EgalFramework\CommandLine\Tests\Stubs\ConsoleInput;
use EgalFramework\CommandLine\Tests\Stubs\ConsoleOutput;
use EgalFramework\CommandLine\Tests\Stubs\Queue;
use EgalFramework\Common\Registry;
use EgalFramework\Common\Session;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use PHPUnit\Framework\TestCase;

class CommandTestCase extends TestCase
{

    protected string $tmpDir;

    protected ConsoleInput $input;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpDir = __DIR__ . '/../tmp/';
        if (!file_exists($this->tmpDir)) {
            mkdir($this->tmpDir, 0750);
        }
        if (!file_exists($this->tmpDir . DIRECTORY_SEPARATOR . 'app')) {
            mkdir($this->tmpDir . DIRECTORY_SEPARATOR . 'app', 0750);
        }
        if (!file_exists($this->tmpDir . DIRECTORY_SEPARATOR . 'database/migrations')) {
            mkdir($this->tmpDir . DIRECTORY_SEPARATOR . 'database/migrations', 0750, true);
        }
        Session::setRegistry(new Registry);
        Session::getRegistry()->set('BasePath', $this->tmpDir);
        Session::getRegistry()->set('AppPath', $this->tmpDir . DIRECTORY_SEPARATOR . 'app/');
        Session::getRegistry()->set('DBPath', $this->tmpDir . DIRECTORY_SEPARATOR . 'database/');
        Session::getRegistry()->set('FactoryPath', $this->tmpDir . DIRECTORY_SEPARATOR . 'factory/');
        Session::getRegistry()->set('SeedPath', $this->tmpDir . DIRECTORY_SEPARATOR . 'seed/');

        Session::setModelManager(new ModelManager);
        Session::setCommandManager(new CommandManager);
        Session::setQueue(new Queue);
        $this->input = new ConsoleInput;
    }

    protected function tearDown(): void
    {
        $this->rmdir($this->tmpDir);
        parent::tearDown();
    }

    private function rmdir(string $path): void
    {
        $dir = opendir($path);
        while ($file = readdir($dir)) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            if (is_file($path . DIRECTORY_SEPARATOR . $file)) {
                unlink($path . DIRECTORY_SEPARATOR . $file);
                continue;
            }
            $this->rmdir($path . DIRECTORY_SEPARATOR . $file);
        }
        rmdir($path);
    }

    protected function getCommand(string $className): Command
    {
        $command = new $className;
        $output = new ConsoleOutput();
        $command->setInput($this->input);
        $command->setOutput(new OutputStyle($this->input, $output));
        return $command;
    }

    protected function getAsset(string $name): string
    {
        return file_get_contents(__DIR__ . '/assets/' . $name);
    }

    protected function loadModel(string $name): void
    {
        $path = __DIR__ . '/../tmp/app/PublicModels/' . $name . '.php';
        file_put_contents(
            $path,
            str_replace(
                'EgalFramework\\Model',
                'EgalFramework\\CommandLine\\Tests\\Stubs',
                file_get_contents($path)
            )
        );
        require_once $path;
    }

    protected function loadMetadata(string $name): void
    {
        $path = __DIR__ . '/../tmp/app/Metadata/' . $name . '.php';
        file_put_contents(
            $path,
            str_replace(
                'EgalFramework\\Metadata',
                'EgalFramework\\CommandLine\\Tests\\Stubs',
                file_get_contents($path)
            )
        );
        require_once $path;
    }

}
