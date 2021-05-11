<?php

namespace EgalFramework\CommandLine\Tests\Stubs;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyleInterface;

class ConsoleOutputFormatter implements OutputFormatterInterface
{

    public function setDecorated(bool $decorated)
    {
        // TODO: Implement setDecorated() method.
    }

    public function isDecorated()
    {
        // TODO: Implement isDecorated() method.
    }

    public function setStyle(string $name, OutputFormatterStyleInterface $style)
    {
        // TODO: Implement setStyle() method.
    }

    public function hasStyle(string $name)
    {
        // TODO: Implement hasStyle() method.
    }

    public function getStyle(string $name)
    {
        // TODO: Implement getStyle() method.
    }

    public function format(?string $message)
    {
        // TODO: Implement format() method.
    }

}
