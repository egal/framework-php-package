<?php

declare(strict_types=1);

namespace Egal\Core\Communication;

use Egal\Core\ActionCaller\ActionCaller;
use Egal\Core\Exceptions\ImpossibilityDeterminingStatusOfResponseException;
use Egal\Core\Exceptions\RequestException;
use Egal\Core\Exceptions\UnableDetermineMessageTypeException;
use Egal\Core\Exceptions\UnsupportedMessageTypeException;
use Egal\Core\Messages\ActionErrorMessage;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\MessageType;
use Egal\Core\Messages\StartProcessingMessage;
use Egal\Core\Session\Session;
use Illuminate\Support\Carbon;
use Throwable;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Connectors\RabbitMQConnector;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

/**
 * Class Request
 */
class Request extends ActionMessage
{

    /**
     * Exhibited after {@see \Egal\Core\Communication\Request::call()}
     */
    private Response $response;

    private RabbitMQQueue $connection;

    /**
     * Mark connection is opened or not.
     */
    private bool $isConnectionOpened;

    private string $authServiceName = 'auth';

    /**
     * Mark is need service authorization or not.
     */
    private bool $serviceAuthorization = true;

    /**
     * @param mixed[] $parameters
     */
    public function __construct(string $serviceName, string $modelName, string $actionName, array $parameters = [])
    {
        parent::__construct($serviceName, $modelName, $actionName, $parameters);

        $this->isConnectionOpened = false;
    }

    public function setAuthServiceName(string $authServiceName): void
    {
        $this->authServiceName = $authServiceName;
    }

    public function disableServiceAuthorization(): void
    {
        $this->serviceAuthorization = false;
    }

    public function enableServiceAuthorization(): void
    {
        $this->serviceAuthorization = true;
    }

    public function isServiceAuthorizationEnabled(): bool
    {
        return $this->serviceAuthorization;
    }

    public function openConnection(): void
    {
        $this->isConnectionNotOpenedOrFail();
        $connector = new RabbitMQConnector(app('events'));
        $this->connection = $connector->connect(config('queue.connections.rabbitmq'));
        $this->isConnectionOpened = true;
    }

    public function reopenConnection(): void
    {
        if ($this->isConnectionOpened) {
            $this->connection->close();
            $this->isConnectionOpened = false;
        }

        $this->openConnection();
    }

    public function closeConnection(): void
    {
        $this->connection->deleteQueue($this->uuid);
        $this->connection->getChannel()->close();
        $this->connection->close();
        $this->isConnectionOpened = false;
    }

    public function waitReplyMessages(): void
    {
        $this->isConnectionOpenedOrFail();

        $this->response = new Response();
        $this->response->setActionMessage($this);

        $startedAt = Carbon::now('UTC');
        $mustDieAt = (clone $startedAt)->addSeconds(config('app.request.wait_reply_message_ttl'));

        try {
            while (Carbon::now('UTC') < $mustDieAt) {
                $this->collectRabbitMessageIntoResponse();

                if ($this->response->getActionResultMessage() || $this->response->getActionErrorMessage()) {
                    break;
                }

                usleep(config('app.request.wait_reply_message_delay'));
            }
        } catch (Throwable $exception) {
            $this->closeConnection();

            throw $exception;
        }

        $this->setResponseStatusCode();
    }

    /**
     * Getter for {@see \Egal\Core\Communication\Request::$response}
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @param mixed $key
     * @return mixed[]
     */
    public function getParameter($key): array
    {
        return $this->parameters[$key];
    }

    public function call(): Response
    {
        $this->sendWithoutClosingConnection();
        $this->waitReplyMessages();
        $this->closeConnection();

        return $this->response;
    }

    public function send(): void
    {
        $this->sendWithoutClosingConnection();
        $this->closeConnection();
    }

    private function sendWithoutClosingConnection(): void
    {
        if ($this->isServiceAuthorizationEnabled()) {
            $this->authorizeService();
        }

        if (!$this->isConnectionOpened) {
            $this->openConnection();
        }

        $this->publish();
    }

    private function authorizeService(): void
    {
        if ($this->isTokenExist()) {
            throw new RequestException('Token already exists! Service autorization is imposible!');
        }

        $this->setToken(
            config('app.service_name') === $this->authServiceName
                ? $this->getItselfServiceServiceToken()
                : $this->getServiceServiceToken()
        );
    }

    private function isConnectionNotOpenedOrFail(): void
    {
        if ($this->isConnectionOpened) {
            throw new RequestException('The connection is already open!');
        }
    }

    /**
     * @throws \Egal\Core\Exceptions\RequestException
     */
    private function isConnectionOpenedOrFail(): void
    {
        if (!$this->isConnectionOpened) {
            throw new RequestException('The connection not open!');
        }
    }

    /**
     * @throws \Egal\Core\Exceptions\ImpossibilityDeterminingStatusOfResponseException
     */
    private function setResponseStatusCode(): void
    {
        $startProcessingMessage = $this->response->getStartProcessingMessage();
        $actionErrorMessage = $this->response->getActionErrorMessage();
        $actionResultMessage = $this->response->getActionResultMessage();

        switch ([$startProcessingMessage !== null, $actionErrorMessage !== null, $actionResultMessage !== null]) {
            case [true, false, true]:
                $this->response->setStatusCode(200);
                break;
            case [true, true, false]:
                $this->response->setStatusCode($this->response->getActionErrorMessage()->getCode());
                $this->response->setErrorMessage($this->response->getActionErrorMessage()->getMessage());
                break;
            case [true, false, false]:
                $this->response->setStatusCode(500);
                $this->response->setErrorMessage(
                    'The service responded, but did not process the request within the allotted time!'
                );
                break;
            case [false, false, false]:
                $this->response->setStatusCode(500);
                $this->response->setErrorMessage('Service not responding!');
                break;
            case [false, true, true]:
            case [false, true, false]:
            case [false, false, true]:
            case [true, true, true]:
            default:
                throw new ImpossibilityDeterminingStatusOfResponseException();
        }
    }

    /**
     * Gets data from rabbit channel and sets it into response
     *
     * @throws \Egal\Core\Exceptions\UnableDetermineMessageTypeException
     * @throws \Exception|\Egal\Core\Exceptions\UnsupportedMessageTypeException
     */
    private function collectRabbitMessageIntoResponse(): void
    {
        $result = $this->connection->getChannel()->basic_get($this->uuid);

        if ($result === null) {
            return;
        }

        $bodyArray = json_decode($result->getBody(), true);

        if (!array_key_exists('type', $bodyArray)) {
            throw new UnableDetermineMessageTypeException();
        }

        switch ($bodyArray['type']) {
            case MessageType::START_PROCESSING:
                $this->response->setStartProcessingMessage(StartProcessingMessage::fromArray($bodyArray));
                break;
            case MessageType::ACTION_RESULT:
                $this->response->setActionResultMessage(ActionResultMessage::fromArray($bodyArray));
                break;
            case MessageType::ACTION_ERROR:
                $this->response->setActionErrorMessage(ActionErrorMessage::fromArray($bodyArray));
                break;
            default:
                throw new UnsupportedMessageTypeException();
        }
    }

    private function getItselfServiceServiceToken(): string
    {
        $masterActionMessage = Session::isActionMessageExists()
            ? Session::getActionMessage()
            : null;

        $smtActionMessage = new ActionMessage(
            $this->authServiceName,
            'Service',
            'login',
            ['service_name' => config('app.service_name'), 'key' => config('app.service_key')]
        );
        Session::setActionMessage($smtActionMessage);
        $smtActionCaller = new ActionCaller(
            $smtActionMessage->getModelName(),
            $smtActionMessage->getActionName(),
            $smtActionMessage->getParameters()
        );
        $smt = $smtActionCaller->call();

        $sstActionMessage = new ActionMessage(
            $this->authServiceName,
            'Service',
            'loginToService',
            ['service_name' => $this->serviceName, 'token' => $smt]
        );
        Session::setActionMessage($sstActionMessage);
        $sstActionCaller = new ActionCaller(
            $sstActionMessage->getModelName(),
            $sstActionMessage->getActionName(),
            $sstActionMessage->getParameters()
        );
        $sst = $sstActionCaller->call();

        $masterActionMessage
            ? Session::setActionMessage($masterActionMessage)
            : Session::unsetActionMessage();

        return $sst;
    }

    private function getServiceServiceToken(): string
    {
        $serviceMasterTokenRequest = new Request(
            $this->authServiceName,
            'Service',
            'login',
            [
                'service_name' => config('app.service_name'),
                'key' => config('app.service_key'),
            ]
        );
        $serviceMasterTokenRequest->disableServiceAuthorization();
        $serviceMasterTokenResponse = $serviceMasterTokenRequest->call();
        $serviceMasterTokenResponse->throwActionErrorMessageIfExists();
        $serviceMasterToken = $serviceMasterTokenResponse->getActionResultMessage()->getData();

        $serviceServiceTokenRequest = new Request(
            $this->authServiceName,
            'Service',
            'loginToService',
            ['service_name' => $this->serviceName, 'token' => $serviceMasterToken]
        );
        $serviceServiceTokenRequest->disableServiceAuthorization();
        $serviceServiceTokenResponse = $serviceServiceTokenRequest->call();
        $serviceServiceTokenResponse->throwActionErrorMessageIfExists();

        return $serviceServiceTokenResponse->getActionResultMessage()->getData();
    }

}
