<?php

namespace EgalFramework\CommandLine\Tests\Stubs;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutput implements OutputInterface
{

    public function write($messages, bool $newline = false, int $options = 0)
    {
        // TODO: Implement write() method.
    }

    public function writeln($messages, int $options = 0)
    {
        // TODO: Implement writeln() method.
    }

    public function setVerbosity(int $level)
    {
        // TODO: Implement setVerbosity() method.
    }

    public function getVerbosity()
    {
        return self::VERBOSITY_QUIET;
    }

    public function isQuiet()
    {
        // TODO: Implement isQuiet() method.
    }

    public function isVerbose()
    {
        // TODO: Implement isVerbose() method.
    }

    public function isVeryVerbose()
    {
        // TODO: Implement isVeryVerbose() method.
    }

    public function isDebug()
    {
        // TODO: Implement isDebug() method.
    }

    public function setDecorated(bool $decorated)
    {
        // TODO: Implement setDecorated() method.
    }

    public function isDecorated()
    {
        // TODO: Implement isDecorated() method.
    }

    public function setFormatter(OutputFormatterInterface $formatter)
    {
        // TODO: Implement setFormatter() method.
    }

    public function getFormatter()
    {
        return new ConsoleOutputFormatter;
    }

}
