<?php

namespace EgalFramework\Model\Tests\Samples\Stubs;

use EgalFramework\Common\Interfaces\ModelManagerInterface;

class ModelManager implements ModelManagerInterface
{

    public function getMetadataPath(string $name): string
    {
        return '\\EgalFramework\\Model\\Tests\\Samples\\Metadata\\' . $name;
    }

    public function getModelPath(string $name): string
    {
        return '\\EgalFramework\\Model\\Tests\\Samples\\Models\\' . $name;
    }

    public function flushCache(string $modelName, int $id): void
    {
    }

    public function hasMetadata(string $name): bool
    {
    }

    public function register(string $name, string $modelNamespace = '\\App\\PublicModels', string $metadataNamespace = '\\App\\Metadata'): void
    {
    }

    public function getModelFiles(): array
    {
    }

    public function getModels(): array
    {
    }

    public function clean(): void
    {
    }

}
