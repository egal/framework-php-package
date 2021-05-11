<?php

namespace EgalFramework\CommandLine\Commands;

use EgalFramework\Common\FieldType;
use EgalFramework\Common\Interfaces\MetadataInterface;
use EgalFramework\Common\Session;
use Exception;
use Illuminate\Console\Command;

class MakeModel extends Command
{

    private string $modelPath;

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'mk:model {modelName}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Generate new model';

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->modelPath = Session::getRegistry()->get('AppPath') . '/PublicModels';
    }

    /**
     * Execute the console command.
     * @throws Exception
     */
    public function handle()
    {
        $modelPath = $this->argument('modelName');
        $nameArray = array_filter(explode('/', $modelPath));
        $name = array_pop($nameArray);
        $modelNamespace = 'App\\PublicModels';
        if (!empty($nameArray)) {
            $modelNamespace .= '\\' . implode('\\', $nameArray);
        }
        if (!file_exists($this->modelPath . '/' . implode('/', $nameArray))) {
            mkdir($this->modelPath . '/' . implode('/', $nameArray), 0750, true);
        }
        $this->makeModel($modelPath, $name, $nameArray, $modelNamespace);
    }

    /**
     * @param string $modelPath
     * @param string $name
     * @param string[] $nameArray
     * @param string $modelNamespace
     * @throws Exception
     */
    private function makeModel(
        string $modelPath,
        string $name,
        array $nameArray,
        string $modelNamespace
    )
    {
        $file = $this->modelPath . '/' . implode('/', $nameArray) . '/' . $name . '.php';
        if (file_exists($file)) {
            throw new Exception('Model is already exists');
        }
        $data = file_get_contents(__DIR__ . '/../../../stubs/Model.stub');
        $data = str_replace('ModelClassName', $name, $data);
        $data = str_replace('ModelNamespace', $modelNamespace, $data);
        $metadataPath = Session::getModelManager()->getMetadataPath(trim(implode('/', $nameArray) . '/' . $name, '/'));
        /** @var MetadataInterface $metadata */
        $metadata = new $metadataPath;
        $data = str_replace('ModelNamespace', $modelNamespace, $data);
        $data = str_replace('Properties', implode(PHP_EOL, $this->getProps($metadata)), $data);
        $data = str_replace('Guarded', implode(', ', $this->getGuarded($metadata)), $data);
        $data = str_replace('ExtraFields', $this->getExtraProps($metadata), $data);
        file_put_contents($file, $data);
        Session::getModelManager()->register($modelPath);
        $this->info('Model ' . $name . ' successfully created');
    }

    /**
     * @param MetadataInterface $metadata
     * @return string[]
     */
    private function getProps(MetadataInterface $metadata)
    {
        $fields = [];
        foreach ($metadata->getFields() as $name => $field) {
            $fields[] = ' * @property ' . $this->getFieldType($field->getType()) . ' $' . $name;
        }
        return $fields;
    }

    /**
     * @param string $name
     * @return string
     */
    private function getFieldType(string $name)
    {
        switch ($name) {
            case FieldType::PASSWORD:
            case FieldType::EMAIL:
            case FieldType::FILE:
            case FieldType::DATE:
            case FieldType::TIME:
            case FieldType::DATETIME:
            case FieldType::IMAGE:
            case FieldType::TEXT:
                return FieldType::STRING;
            case FieldType::LIST:
            case FieldType::RELATION:
            case FieldType::PK:
                return FieldType::INT;
            case FieldType::JSON:
                return 'array';
            default:
                return $name;
        }
    }

    /**
     * @param MetadataInterface $metadata
     * @return string[]
     */
    private function getGuarded(MetadataInterface $metadata): array
    {
        $fields = [];
        foreach ($metadata->getFields() as $name => $field) {
            if ($field->getType() == FieldType::PK) {
                $fields[] = '\'' . $name . '\'';
            }
            if (in_array($name, ['created_at', 'updated_at'])) {
                $fields[] = '\'' . $name . '\'';
            }
            if ($field->getReadonly()) {
                $fields[] = '\'' . $name . '\'';
            }
        }
        return array_values(array_unique($fields));
    }

    private function getExtraProps(MetadataInterface $metadata): string
    {
        $result = '';
        $casts = [];
        foreach ($metadata->getFields() as $name => $field) {
            if ($field->getType() == FieldType::JSON) {
                $casts[$name] = 'array';
            }
        }
        if (!empty($casts)) {
            $result .= PHP_EOL . PHP_EOL . '    protected $casts = [';
            foreach ($casts as $field => $cast) {
                $result .= PHP_EOL . '        \'' . $field . '\' => \'' . $cast . '\',';
            }
            $result .= PHP_EOL . '    ];';
        }
        return $result;
    }

}
