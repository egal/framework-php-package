<?php

namespace EgalFramework\CommandLine\Commands;

use EgalFramework\CommandLine\ProcessManager;
use EgalFramework\CommandLine\QueueProcessor;
use EgalFramework\Common\Session;
use EgalFramework\Common\Settings;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Class ProcessMainQueue
 *
 * This class should be tested manually
 *
 * @codeCoverageIgnore
 * @package App\Console\Commands
 */
class ProcessMainQueue extends Command
{

    const PID_FILE = 'service_mq_processor.pid';

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'mq:run {queue_name}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Run message queue processing';

    private ProcessManager $processManager;

    /**
     * ProcessMainQueue constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->processManager = new ProcessManager;
    }

    /**
     * Destructor: actions on exit from the app
     */
    public function __destruct()
    {
        if (!Storage::exists(self::PID_FILE) || Storage::get(self::PID_FILE) != getmypid()) {
            return;
        }
        Storage::delete(self::PID_FILE);
    }


    /**
     * Execute the console command.
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $this->setTermProcessor();
        if ($this->argument('queue_name') == '_') {
            cli_set_process_title(Settings::getAppName() . ' main queue');
            $this->processPID();
            $this->publishAPI();
            $this->listenPools();
        } else {
            $this->listenQueue($this->argument('queue_name'));
        }
    }

    /**
     * Create PID file, there is need the app to be started just once
     * @throws Exception
     */
    private function processPID()
    {
        $pid = getmypid();
        if (Storage::exists(self::PID_FILE)) {
            Log::error('Process is already running! PID ' . Storage::get(self::PID_FILE));
            exit(1);
        } else {
            Storage::put(self::PID_FILE, $pid);
        }
    }

    /**
     * Set terminate actions
     */
    private function setTermProcessor()
    {
        $closure = function ($sigNo, $sigInfo) {
            $this->terminate($sigNo, $sigInfo);
        };
        $closure->bindTo($this);
        pcntl_signal_dispatch();
        pcntl_async_signals(TRUE);
        foreach ([SIGINT, SIGTERM] as $signal) {
            pcntl_signal($signal, $closure);
        }
    }

    /**
     * What to do on ctrl+C, SIGTERM
     * @param int $sigNo
     * @param mixed $sigInfo
     */
    public function terminate(int $sigNo, $sigInfo)
    {
        Log::alert('Got signal: ' . $sigNo . ' ' . json_encode($sigInfo));
        if (isset($this->processManager)) {
            Log::alert('Exiting main process');
            $this->processManager->killAll($sigNo);
        } else {
            Log::alert('Exiting child process');
        }
        Session::getQueue()->quit();
        exit;
    }

    /**
     * Publish API in redis DB
     */
    private function publishAPI()
    {
        Session::getApiStorage()->removeAll();
        foreach (Session::getModelManager()->getModels() as $model) {
            $modelName = Session::getModelManager()->getModelPath($model);
            if (!class_exists($modelName)) {
                continue;
            }
            $modelData = Session::getApiParser()->extract(
                $modelName, Settings::getDebugMode() && Settings::getDisableAuth()
            );
            if (empty($modelData->getMethods())) {
                continue;
            }
            Session::getApiStorage()->save($modelData);
        }
    }

    /**
     * Listen pools which should be started dynamically
     */
    private function listenPools()
    {
        while (TRUE) {
            $pools = Session::getQueue()->getPools();
            $this->launchPoolProcessors($pools);
            $this->killPoolProcessors($pools);
            $this->checkProcesses($pools);
            usleep(250000);
        }
    }

    /**
     * Launch processors if it is not launched yet
     * @param array $pools
     * @throws Exception
     */
    private function launchPoolProcessors(array $pools)
    {
        foreach ($pools as $key) {
            if (!preg_match('/^[_a-z0-9]+$/i', $key) || $this->processManager->hasPoolProcessor($key)
            ) {
                continue;
            }
            if (($pid = pcntl_fork()) == 0) {
                unset($this->processManager);
                $this->listenQueue($key);
            } else {
                $this->processManager->set($pid, $key);
            }
        }
    }

    /**
     * @param string[] $pools
     * @throws Exception
     */
    private function checkProcesses(array $pools)
    {
        if ($this->processManager->empty()) {
            return;
        }
        $pid = $this->processManager->getDiedPid();
        if (is_null($pid)) {
            return;
        }
        $queueName = $this->processManager->getByPid($pid);
        if (!in_array($queueName, $pools)) {
            $this->processManager->deleteByPid($pid);
            return;
        }
        $callback = Session::getQueueFaultCallback();
        if (!is_null($callback)) {
            $callback($queueName);
        }
        if (($newPid = pcntl_fork()) == 0) {
            $this->listenQueue($queueName);
        } else {
            $this->processManager->set($newPid, $queueName);
        }
    }

    /**
     * Stop pool processors if there is no need in it more
     * @param array $pools
     */
    private function killPoolProcessors(array $pools)
    {
        foreach ($this->processManager->getMap() as $key => $pid) {
            if (!in_array($key, $pools)) {
                $this->processManager->killProcess($pid);
            }
        }
    }

    /**
     * @param string $queueName
     * @throws Exception
     */
    private function listenQueue(string $queueName)
    {
        cli_set_process_title(Settings::getAppName() . ' queue ' . $queueName);
        Session::getQueue()->restartQueue();
        DB::reconnect();

        $queueProcessor = new QueueProcessor($queueName);
        $queueProcessor->run();
    }

}
