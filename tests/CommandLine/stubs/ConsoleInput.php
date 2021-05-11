<?php

namespace EgalFramework\CommandLine\Tests\Stubs;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;

class ConsoleInput implements InputInterface
{

    private array $arguments;

    private array $options;

    public function getFirstArgument()
    {
        // TODO: Implement getFirstArgument() method.
    }

    public function hasParameterOption($values, bool $onlyParams = false)
    {
        // TODO: Implement hasParameterOption() method.
    }

    public function getParameterOption($values, $default = false, bool $onlyParams = false)
    {
        // TODO: Implement getParameterOption() method.
    }

    public function bind(InputDefinition $definition)
    {
        // TODO: Implement bind() method.
    }

    public function validate()
    {
        // TODO: Implement validate() method.
    }

    public function getArguments()
    {
        // TODO: Implement getArguments() method.
    }

    public function getArgument(string $name)
    {
        return $this->arguments[$name];
    }

    public function setArgument(string $name, $value)
    {
        $this->arguments[$name] = $value;
    }

    public function hasArgument($name)
    {
        return isset($this->arguments[$name]);
    }

    public function getOptions()
    {
        // TODO: Implement getOptions() method.
    }

    public function getOption(string $name)
    {
        return isset($this->options[$name])
            ? $this->options[$name]
            : null;
    }

    public function setOption(string $name, $value)
    {
        $this->options[$name] = $value;
    }

    public function hasOption(string $name)
    {
        // TODO: Implement hasOption() method.
    }

    public function option(string $name)
    {
        return $this->getOption($name);
    }

    public function isInteractive()
    {
        // TODO: Implement isInteractive() method.
    }

    public function setInteractive(bool $interactive)
    {
        return false;
    }

}
