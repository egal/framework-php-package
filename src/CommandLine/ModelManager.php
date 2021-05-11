<?php

namespace EgalFramework\CommandLine;

use EgalFramework\CommandLine\Exceptions\ModelManagerException;
use EgalFramework\Common\Interfaces\ModelManagerInterface;
use EgalFramework\Common\Session;

/**
 * Class ModelManager
 * @package EgalFramework\CommandLine
 */
class ModelManager implements ModelManagerInterface
{

    /** @var string */
    private string $path;

    /** @var string */
    private string $modelsFile;

    /** @var string[] */
    private array $models;

    /** @var string[] */
    private array $metadata;

    /**
     * Initialize model container
     * @throws ModelManagerException
     */
    public function __construct()
    {
        $this->path = Session::getRegistry()->get('AppPath') . '/PublicModels/';
        $this->modelsFile = Session::getRegistry()->get('AppPath') . '/models.json';
        $this->load();
    }

    /**
     * Load data from file
     * @throws ModelManagerException
     */
    private function load()
    {
        $this->models = [];
        $this->metadata = [];
        $json = file_exists($this->modelsFile)
            ? file_get_contents($this->modelsFile)
            : '{}';
        $data = json_decode($json, true);
        if (false === $data || !is_array($data)) {
            throw new ModelManagerException('Model JSON file corrupted');
        }
        if (!empty($data['models'])) {
            $this->models = $data['models'];
        }
        if (!empty($data['metadata'])) {
            $this->metadata = $data['metadata'];
        }
    }

    /**
     * Register new model
     *
     * @param string $name Model name
     * @param string $modelNamespace Namespace for a model, $name = Model, $namespace = \\App\\PublicModels,
     * all together: \\App\\PublicModels\\Model
     * @param string $metadataNamespace Same as $modelNamespace
     */
    public function register(
        string $name,
        string $modelNamespace = '\\App\\PublicModels',
        string $metadataNamespace = '\\App\\Metadata'
    ): void
    {
        $this->models[$name] = $modelNamespace . '\\' . str_replace('/', '\\', $name);
        $this->metadata[$name] = $metadataNamespace . '\\' . str_replace('/', '\\', $name);
        self::save();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isRegistered(string $name)
    {
        return isset($this->models[$name]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasMetadata(string $name): bool
    {
        return isset($this->metadata[$name]);
    }

    /**
     * Unregister a model
     * @param string $name
     */
    public function unregister(string $name)
    {
        unset($this->models[$name]);
        unset($this->metadata[$name]);
        self::save();
    }

    /**
     * Save to file
     */
    private function save()
    {
        file_put_contents(
            $this->modelsFile, json_encode([
                'models' => $this->models,
                'metadata' => $this->metadata
            ], JSON_PRETTY_PRINT)
        );
    }

    /**
     * @param string $subdirectory
     * @return string[]
     */
    public function getModelFiles(string $subdirectory = ''): array
    {
        $models = [];
        $files = scandir($this->path . '/' . $subdirectory, SCANDIR_SORT_NONE);
        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            if (is_dir($this->path . '/' . $file)) {
                $models += $this->getModelFiles($file);
            } else {
                $models[] = (
                    empty($subdirectory)
                        ? ''
                        : $subdirectory . '/'
                    ) . preg_replace('/\.php$/', '', $file);
            }
        }
        return $models;
    }

    /**
     * @return string[]
     */
    public function getModels(): array
    {
        return array_keys($this->models);
    }

    /**
     * @param string $name
     * @return string
     * @throws ModelManagerException
     */
    public function getModelPath(string $name): string
    {
        if (!$this->isRegistered($name)) {
            throw new ModelManagerException(sprintf('Model "%s" not found', $name), 404);
        }
        return $this->models[$name];
    }

    /**
     * @param string $name
     * @return string
     * @throws ModelManagerException
     */
    public function getMetadataPath(string $name): string
    {
        if (!$this->isRegistered($name)) {
            throw new ModelManagerException(sprintf('Metadata "%s" not found', $name), 404);
        }
        return $this->metadata[$name];
    }

    public function flushCache(string $modelName, int $id): void
    {
        Session::getRequestCache()->tags([$modelName, $modelName . '_' . $id])->clear();
    }

    public function clean(): void
    {
        foreach ($this->models as $key => $model) {
            if (class_exists($model)) {
                continue;
            }
            unset($this->models[$key]);
        }
        foreach ($this->metadata as $key => $metadata) {
            if (class_exists($metadata)) {
                continue;
            }
            unset($this->metadata[$key]);
        }
        $this->save();
    }

}
