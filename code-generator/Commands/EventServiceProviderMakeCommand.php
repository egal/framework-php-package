<?php /** @noinspection ALL */

namespace Egal\CodeGenerator\Commands;

use Exception;

/**
 * Класс консольной комманды генерации события.
 *
 * @package Egal\Model
 */
class EventServiceProviderMakeCommand extends MakeCommand
{

    /**
     * Сигнатура конгсольной команды.
     *
     * @var string
     */
    protected $signature = 'egal:make:event-service-provider
                           ';

    /**
     * Описание консольной окманды.
     *
     * @var string
     */
    protected $description = 'Генерация';

    /**
     * Базовое названия файла-заглушки.
     *
     * @var string
     */
    protected string $stubFileBaseName = 'event_service_provider';

    /**
     * Действие консольной команды.
     *
     * @throws Exception
     */
    public function handle(): void
    {
        $this->fileBaseName = 'EventServiceProvider';
        $this->filePath = base_path('app/Providers') . '/' . $this->fileBaseName . '.php';
        $this->writeFile();
    }

}
