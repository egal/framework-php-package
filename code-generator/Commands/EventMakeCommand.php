<?php /** @noinspection ALL */

namespace Egal\CodeGenerator\Commands;

use Exception;

/**
 * Класс консольной комманды генерации события.
 *
 * @package Egal\Model
 */
class EventMakeCommand extends MakeCommand
{

    /**
     * Сигнатура конгсольной команды.
     *
     * @var string
     */
    protected $signature = 'egal:make:event
                            {event-name : Название события}
                            {--g|global : Генерировать глобальное событие}
                           ';

    /**
     * Описание консольной окманды.
     *
     * @var string
     */
    protected $description = 'Генерация класса события';

    /**
     * Базовое названия файла-заглушки.
     *
     * @var string
     */
    protected string $stubFileBaseName = 'event';

    /**
     * Действие консольной команды.
     *
     * @throws Exception
     */
    public function handle(): void
    {
        $fileBaseName = (string)$this->argument('event-name');
        $extends = $this->option('global') ? 'GlobalEvent' : 'Event';
        $this->fileBaseName = str_ends_with($fileBaseName, $extends) ? $fileBaseName : $fileBaseName . $extends;
        $this->filePath = base_path('app/Events') . '/' . $this->fileBaseName . '.php';
        $this->setFileContents('{{ class }}', $this->fileBaseName);
        $this->setFileContents('{{ extends }}', $extends);
        $this->writeFile();
    }

}
