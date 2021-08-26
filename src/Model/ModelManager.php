<?php

namespace Egal\Model;

use Egal\Core\Exceptions\ModelNotFoundException;
use Egal\Model\Exceptions\LoadModelImpossiblyException;
use Egal\Model\Metadata\ModelMetadata;
use Exception;
use ReflectionException;

/**
 * Класс-одиночка менеджера моделей.
 *
 * Содержит в себе все {@see ModelMetadata} каждой {@see Model} хранящейся в app/Models.
 *
 * @package Egal\Model
 */
class ModelManager
{

    /**
     * Ассоциативный массив всех метаданных моделей
     *
     * @var ModelMetadata[]
     */
    protected array $modelsMetadata = [];

    /**
     * ModelManager конструктор.
     *
     * Автоматически сканирует все модели с помощью {@see ModelManager::scanModels()}
     *
     * @throws ReflectionException
     */
    public function __construct()
    {
        $this->scanModels();
    }

    /**
     * @statuses-access guest,logged
     * TODO: Сделать доступным для вызова
     */
    public static function actionGetAllModelsMetadata(): array
    {
        $result = [];
        foreach (ModelManager::getInstance()->modelsMetadata as $modelName => $modelMetadata) {
            $result[$modelName] = $modelMetadata->toArray();
        }
        return $result;
    }

    /**
     * Получение метаданных модели.
     *
     * @param string $model Название модели либо короткое название модели.
     * @return ModelMetadata
     * @throws Exception
     */
    public static function getModelMetadata(string $model): ModelMetadata
    {
        if (class_exists($model)) {
            return ModelManager::getInstance()->modelsMetadata[get_class_short_name($model)];
        } elseif (isset(ModelManager::getInstance()->modelsMetadata[$model])) {
            return ModelManager::getInstance()->modelsMetadata[$model];
        } else {
            throw new ModelNotFoundException();
        }
    }

    /**
     * Сканирование директории app/Models, формирование метаданных найденных моделей.
     *
     * @param string|null $dir
     * @throws ReflectionException
     */
    protected function scanModels(string $dir = null)
    {
        $baseDir = base_path('app/Models/');
        if (is_null($dir)) $dir = $baseDir;
        $modelsNamespace = 'App\Models\\';

        foreach (scandir($dir) as $dirItem) {
            $itemPath = str_replace('//', '/', $dir . '/' . $dirItem);

            if ($dirItem === '.' || $dirItem === '..') continue;
            if (is_dir($itemPath)) {
                $this->scanModels($itemPath);
            }
            if (!str_contains($dirItem, '.php')) continue;

            $classShortName = str_replace('.php', '', $dirItem);
            $class = str_replace($baseDir, '', $itemPath);
            $class = str_replace($dirItem, $classShortName, $class);
            $class = str_replace('/', '\\', $class);
            $class = $modelsNamespace . $class;
            $this->modelsMetadata[$classShortName] = new ModelMetadata($class);
        }
    }

    /**
     * @param string $class
     * @throws \Egal\Model\Exceptions\LoadModelImpossiblyException
     * @throws \ReflectionException
     */
    public static function loadModel(string $class): void
    {
        $instance = static::getInstance();
        $classShortName = get_class_short_name($class);
        if (isset($instance->modelsMetadata[$classShortName])) {
            throw new LoadModelImpossiblyException();
        }
        $instance->modelsMetadata[$classShortName] = new ModelMetadata($class);
    }

    /**
     * Получение экземпляра класса-одиночки.
     *
     * @return ModelManager
     */
    public static function getInstance(): ModelManager
    {
        return app(ModelManager::class);
    }

    /**
     * @return ModelMetadata[]
     */
    public function getModelsMetadata(): array
    {
        return $this->modelsMetadata;
    }

}
