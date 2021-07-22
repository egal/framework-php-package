<?php

declare(strict_types=1);

namespace Egal\Core\Communication;

use Egal\Core\Exceptions\ImpossibilityDeterminingStatusOfResponseException;
use Egal\Core\Exceptions\RequestException;
use Egal\Core\Exceptions\UnableDetermineMessageTypeException;
use Egal\Core\Exceptions\UnsupportedMessageTypeException;
use Egal\Core\Messages\ActionErrorMessage;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\MessageType;
use Egal\Core\Messages\StartProcessingMessage;
use Exception;
use Illuminate\Support\Carbon;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Connectors\RabbitMQConnector;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

/**
 * Class Request
 */
class Request extends ActionMessage
{

    /**
     * Response of Request.
     *
     * Exhibited after {@see \Egal\Core\Communication\Request::call()}
     */
    private Response $response;

    /**
     * Connection to RabbitMQ queue.
     */
    private RabbitMQQueue $connection;

    /**
     * Mark connection is opened or not.
     */
    private bool $isConnectionOpened;

    /**
     * Auth service name.
     */
    private string $authServiceName = 'auth';

    /**
     * Mark is need service authorization or not.
     */
    private bool $serviceAuthorization = true;

    /**
     * Request constructor.
     *
     * @param mixed[] $parameters
     */
    public function __construct(string $serviceName, string $modelName, string $actionName, array $parameters = [])
    {
        parent::__construct($serviceName, $modelName, $actionName, $parameters);

        $this->isConnectionOpened = false;
    }

    /**
     * Setter for {@see Request::$authServiceName}.
     */
    public function setAuthServiceName(string $authServiceName): void
    {
        $this->authServiceName = $authServiceName;
    }

    /**
     * Disable service authorization.
     */
    public function disableServiceAuthorization(): void
    {
        $this->serviceAuthorization = false;
    }

    /**
     * Enable service authorization.
     */
    public function enableServiceAuthorization(): void
    {
        $this->serviceAuthorization = true;
    }

    /**
     * Is service authorization enabled.
     */
    public function isServiceAuthorizationEnabled(): bool
    {
        return $this->serviceAuthorization;
    }

    /**
     * @throws \Egal\Core\Exceptions\RequestException|\Exception
     */
    public function openConnection(): void
    {
        $this->isConnectionNotOpenedOrFail();
        $connector = new RabbitMQConnector(app('events'));
        $this->connection = $connector->connect(config('queue.connections.rabbitmq'));
        $this->isConnectionOpened = true;
    }

    /**
     * @throws \Egal\Core\Exceptions\RequestException|\Exception
     */
    public function reopenConnection(): void
    {
        if ($this->isConnectionOpened) {
            $this->connection->close();
            $this->isConnectionOpened = false;
        }

        $this->openConnection();
    }

    /**
     * @throws \PhpAmqpLib\Exception\AMQPProtocolChannelException|\Exception
     */
    public function closeConnection(): void
    {
        $this->connection->deleteQueue($this->uuid);
        $this->connection->getChannel()->close();
        $this->connection->close();
        $this->isConnectionOpened = false;
    }

    /**
     * @throws \Egal\Core\Exceptions\RequestException|\PhpAmqpLib\Exception\AMQPProtocolChannelException|\Egal\Core\Exceptions\ImpossibilityDeterminingStatusOfResponseException
     */
    public function waitReplyMessages(): void
    {
        $this->isConnectionOpenedOrFail();

        $this->response = new Response();
        $this->response->setActionMessage($this);

        $startedAt = Carbon::now('UTC');
        $mustDieAt = (clone $startedAt)->addSeconds(10);

        try {
            while (Carbon::now('UTC') < $mustDieAt) {
                $this->collectRabbitMessageIntoResponse();

                if ($this->response->getActionResultMessage()) {
                    break;
                }

                if ($this->response->getActionErrorMessage()) {
                    break;
                }

                usleep(100);
            }
        } catch (Exception $exception) {
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

    /**
     * @throws \Egal\Core\Exceptions\RequestException|\Egal\Core\Exceptions\ResponseException|\Egal\Core\Exceptions\RequestException|\PhpAmqpLib\Exception\AMQPProtocolChannelException|\Egal\Core\Exceptions\ImpossibilityDeterminingStatusOfResponseException|\PhpAmqpLib\Exception\AMQPProtocolChannelException
     */
    public function call(): Response
    {
        if ($this->isServiceAuthorizationEnabled()) {
            $this->authorizeService();
        }

        if (!$this->isConnectionOpened) {
            $this->openConnection();
        }

        $this->publish();
        $this->waitReplyMessages();
        $this->closeConnection();

        return $this->response;
    }

    /**
     * @throws \PhpAmqpLib\Exception\AMQPProtocolChannelException|\Egal\Core\Exceptions\ResponseException|\Egal\Core\Exceptions\RequestException|\Egal\Core\Exceptions\ImpossibilityDeterminingStatusOfResponseException
     */
    public function send(): void
    {
        if ($this->isServiceAuthorizationEnabled()) {
            $this->authorizeService();
        }

        if (!$this->isConnectionOpened) {
            $this->openConnection();
        }

        $this->publish();
        $this->closeConnection();
    }

    /**
     * @throws \PhpAmqpLib\Exception\AMQPProtocolChannelException|\Egal\Core\Exceptions\ImpossibilityDeterminingStatusOfResponseException|\Egal\Core\Exceptions\ResponseException|\Egal\Core\Exceptions\RequestException
     */
    private function authorizeService(): void
    {
        if ($this->isTokenExist()) {
            throw new RequestException('Token already exists! Service autorization is imposible!');
        }

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
        $serviceServiceToken = $serviceServiceTokenResponse->getActionResultMessage()->getData();

        $this->setToken($serviceServiceToken);
    }

    /**
     * @throws \Egal\Core\Exceptions\RequestException
     */
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

}
