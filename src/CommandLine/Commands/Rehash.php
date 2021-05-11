<?php

namespace EgalFramework\CommandLine\Commands;

use EgalFramework\Common\Interfaces\MetadataInterface;
use EgalFramework\Common\Interfaces\ModelInterface;
use EgalFramework\Common\Session;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class Rehash extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'rehash {modelName?}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Rehash database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modelName = $this->argument('modelName');
        if ($modelName) {
            $this->rehashModel($modelName);
        } else {
            $models = Session::getModelManager()->getModels();
            foreach ($models as $modelName) {
                $this->rehashModel($modelName);
            }
        }
    }

    public function rehashModel(string $modelName): void
    {
        $modelPath = Session::getModelManager()->getModelPath($modelName);
        if (!is_subclass_of($modelPath, ModelInterface::class)) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        if (!$this->tableExist($modelName)) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        /** @var ModelInterface $model */
        $model = new $modelPath;
        $id = 0;
        do {
            $id = $this->rehashItems($model, $id);
        } while ($id);
    }

    public function tableExist(string $modelName): bool
    {
        $metadataPath = Session::getModelManager()->getMetadataPath($modelName);
        /** @var MetadataInterface $metadata */
        $metadata = new $metadataPath;
        return Schema::hasTable($metadata->getTable());
    }

    private function rehashItems(ModelInterface $model, int $id): ?int
    {
        /** @var ModelInterface[] $items */
        $items = $model->newModelQuery()->where('id', '>', $id)->orderBy('id')->limit(100)->get()->all();
        $id = null;
        foreach ($items as $item) {
            $id = $item->id;
            /** @noinspection PhpUndefinedMethodInspection */
            $model->newModelQuery()->where('id', $id)->update(['hash' => $item->makeHash()]);
        }
        return $id;
    }

}
