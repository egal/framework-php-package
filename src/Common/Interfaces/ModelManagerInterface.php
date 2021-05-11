<?php

namespace EgalFramework\Common\Interfaces;

interface ModelManagerInterface
{

    /**
     * @param string $name
     * @return string
     * @throws ExceptionInterface
     */
    public function getMetadataPath(string $name): string;

    /**
     * @param string $name
     * @return string
     * @throws ExceptionInterface
     */
    public function getModelPath(string $name): string;

    public function flushCache(string $modelName, int $id): void;

    public function hasMetadata(string $name): bool;

    public function register(
        string $name,
        string $modelNamespace = '\\App\\PublicModels',
        string $metadataNamespace = '\\App\\Metadata'
    ): void;

    public function getModelFiles(): array;

    public function getModels(): array;

    public function clean(): void;

}
