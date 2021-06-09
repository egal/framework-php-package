<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace Egal\Core\Commands;

use Egal\Core\Bus\Bus;
use Egal\Core\Bus\RabbitMQBus;
use Egal\Core\Exceptions\QueueProcessingException;
use Egal\Core\Traits\PcntlSignal;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class EgalListenerRunCommand extends Command
{

    use PcntlSignal;

    # TODO: Разобраться сколько надо prefetch-count по стандарту
    protected $signature = 'egal:listener:run
                                {--p|prefetch-count=1 : По сколько сообщений делается выборка из очереди}
                                {--m|listening-method=consume : Способ получения сообщений}
                                {--consume-sleep= : Кол-во миллисекунд задержки между выборками сообщений из очереди}
                           ';

    protected $description = 'Запуск слушателя очереди сервиса';

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $listenerName = words_to_separated_lower_case(config('app.service_name'), 'listener' . '#' . getmypid());
        cli_set_process_title($listenerName);

        Bus::getInstance()->constructEnvironment();

        switch (get_class(Bus::getInstance())) {
            case RabbitMQBus::class:
                $this->listenRabbitMQQueue();
                break;
        }
    }

    /**
     * @throws Exception
     */
    private function listenRabbitMQQueue()
    {
        /** @var RabbitMQBus $bus */
        $bus = Bus::getInstance();

        switch ($this->option('listening-method')) {
            case 'consume':
                Artisan::call('rabbitmq:consume', [
                    '--queue' => $bus->queueName,
                    '--prefetch-count' => $this->option('prefetch-count'),
                    '--sleep' => ($this->option('consume-sleep')
                            ?? config('queue.connections.rabbitmq.options.consume.sleep')) / 1000,
                    '--timeout' => 0,
                ]);
                break;
            case 'get':
                Artisan::call('queue:work', [
                    '--queue' => $bus->queueName,
                ]);
                break;
            default:
                throw new QueueProcessingException(
                    'Unsupported queue listening type - ' . $this->option('listening-method') . '!'
                );
        }
    }

    public function stopCommand()
    {
        $this->info('Killing Listener!');
        Bus::getInstance()->destructEnvironment();
        exit;
    }

}
