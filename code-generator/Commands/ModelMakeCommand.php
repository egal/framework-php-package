<?php /** @noinspection ALL */

namespace Egal\CodeGenerator\Commands;

use Egal\Model\Model;
use Exception;

/**
 * Класс консольной комманды генерации.
 *
 * Генерируется файл {@see Model}.
 *
 * @package Egal\Model
 */
class ModelMakeCommand extends MakeCommand
{

    /**
     * Сигнатура конгсольной команды.
     *
     * @var string
     */
    protected $signature = 'egal:make:model
                            {model-name : Название модели}
                           ';

    /**
     * Описание консольной окманды.
     *
     * @var string
     */
    protected $description = 'Генерация класса модели';

    /**
     * Базовое названия файла-заглушки.
     *
     * @var string
     */
    protected string $stubFileBaseName = 'model';

    /**
     * Действие консольной команды.
     *
     * Записывает файл {@see Model} с названием указанным при вызове команды в директорию PROJECT_PATH/app/Models
     * содержанием {@see model.stub}.
     *
     * В содержании конечного файла буду заменены строки '{{ class }}' на название указанное при вызове команды.
     *
     * @throws Exception
     */
    public function handle(): void
    {
        $this->fileBaseName = (string)$this->argument('model-name');
        $this->filePath = base_path('app/Models') . '/' . $this->fileBaseName . '.php';
        $this->fileContents = str_replace('{{ class }}', $this->fileBaseName, $this->fileContents);
        $this->writeFile();
    }

}
