<?php

namespace EgalFramework\Common\Interfaces;

interface CommandManagerInterface
{

    public function register(string $path): void;

    public function clean(): void;

    public function getScripts(): array;

}
