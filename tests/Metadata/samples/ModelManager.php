<?php

namespace EgalFramework\Metadata\Tests\Samples;

use EgalFramework\Common\Interfaces\ModelManagerInterface;

class ModelManager implements ModelManagerInterface
{

    public function getMetadataPath(string $name): string
    {
        return '\\EgalFramework\\Metadata\\Tests\\Samples\\' . $name . 'Metadata';
    }

    public function getModelPath(string $name): string
    {
        // TODO: Implement getModelPath() method.
    }

    public function flushCache(string $modelName, int $id): void
    {
        // TODO: Implement flushCache() method.
    }

    public function hasMetadata(string $name): bool
    {
        return $name != 'Fault';
    }

    public function register(string $name, string $modelNamespace = '\\App\\PublicModels', string $metadataNamespace = '\\App\\Metadata'): void
    {
        // TODO: Implement register() method.
    }

    public function getModelFiles(): array
    {
        // TODO: Implement getModelFiles() method.
    }

    public function getModels(): array
    {
        return ['Metadata', 'Test'];
    }

    public function clean(): void
    {
        // TODO: Implement clean() method.
    }
}
