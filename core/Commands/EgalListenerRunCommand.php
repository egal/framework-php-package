<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace Egal\Core\Commands;

use Egal\Core\Bus\Bus;
use Egal\Core\Bus\RabbitMQBus;
use Egal\Core\Exceptions\QueueProcessingException;
use Egal\Core\Exceptions\UnsupportedBusException;
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

        $listenMethodName = 'listen'
            . str_replace(get_class_short_name(Bus::class),
                '',
                get_class_short_name(Bus::getInstance())
            )
            . 'Queue';
        if (!method_exists($this, $listenMethodName)) {
            throw new UnsupportedBusException();
        }
        $this->$listenMethodName();
    }

    /**
     * @throws Exception
     * @noinspection PhpUnusedPrivateMethodInspection
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
                    '--sleep' => 0,
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
