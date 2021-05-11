<?php /** @noinspection PhpMissingFieldTypeInspection */


namespace Egal\CodeGenerator\Commands;

use Egal\CodeGenerator\Exceptions\ConfigMakeException;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class QueueConfigMakeCommand extends Command
{

    protected $signature = 'egal:make:config {config_name : Название конфигурации, которую надо сгенерировать}
                           ';

    protected $description = 'Генерация конфигураций';

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $makeFunction = Str::camel('make_' . $this->argument('config_name'));
        try {
            $this->$makeFunction();
        } catch (Exception $exception) {
            throw new ConfigMakeException();
        }
    }

    /**
     * @throws Exception
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function makeQueue()
    {
        $this->copyConfig(
            __DIR__ . '/../../stubs/config.queue.stub',
            base_path('config/queue.php')
        );
    }

    /**
     * @param string $from
     * @param string $to
     * @throws Exception
     */
    private function copyConfig(string $from, string $to)
    {
        if (
            file_exists($to)
            && !$this->confirm('Файл конфигурации уже существует. Заменить?', false)
        ) {
            $this->warn('Операция отменена!');
            return;
        }
        if (!copy($from, $to)) {
            throw new ConfigMakeException("File copy error! From $from to $to.");
        }
    }

}
