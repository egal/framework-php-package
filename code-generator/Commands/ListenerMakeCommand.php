<?php /** @noinspection ALL */

namespace Egal\CodeGenerator\Commands;

use Exception;

/**
 * Класс консольной комманды генерации события.
 *
 * @package Egal\Model
 */
class ListenerMakeCommand extends MakeCommand
{

    /**
     * Сигнатура конгсольной команды.
     *
     * @var string
     */
    protected $signature = 'egal:make:listener
                            {name : Название обработчика}
                            {--g|global : Обработчик глобального события}
                           ';

    /**
     * Описание консольной окманды.
     *
     * @var string
     */
    protected $description = 'Генерация класса обработчика события';

    /**
     * Базовое названия файла-заглушки.
     *
     * @var string
     */
    protected string $stubFileBaseName = 'listener';

    /**
     * Действие консольной команды.
     *
     * @throws Exception
     */
    public function handle(): void
    {
        $fileBaseName = (string)$this->argument('name');
        $extends = $this->option('global') ? 'GlobalEventListener' : 'EventListener';
        $this->fileBaseName = str_ends_with($fileBaseName, 'Listener') ? $fileBaseName : $fileBaseName . 'Listener';
        $this->filePath = base_path('app/Listeners') . '/' . $this->fileBaseName . '.php';
        $this->setFileContents('{{ class }}', $this->fileBaseName);
        $this->setFileContents('{{ extends }}', $extends);
        $this->setFileContents(
            '{{ handle_parameters }}',
            $this->option('global') ? 'array $data' : ''
        );
        $this->writeFile();
    }

}
