<?php

namespace Egal\Core\Communication;

use Egal\Core\Bus\Bus;
use Egal\Core\Exceptions\RequestException;
use Egal\Core\Exceptions\ResponseException;
use Egal\Core\Messages\ActionErrorMessage;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\MessageType;
use Egal\Core\Messages\StartProcessingMessage;
use Exception;
use Illuminate\Support\Carbon;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPProtocolChannelException as AMQPProtocolChannelExceptionAlias;

/**
 * Class Request
 * @package Egal\Core\Communication
 */
class Request extends ActionMessage
{
    public Response $response;

    private AbstractConnection $connection;

    private bool $isConnectionOpened;

    private string $authServiceName = 'auth';

    private bool $serviceAuthorization = true;

    public function __construct(string $serviceName, string $modelName, string $actionName, array $parameters = [])
    {
        parent::__construct($serviceName, $modelName, $actionName, $parameters);
        $this->isConnectionOpened = false;
    }

    /**
     * @return void
     * @throws RequestException
     */
    private function isConnectionNotOpenedOrFail(): void
    {
        if ($this->isConnectionOpened) {
            throw new RequestException('The connection is already open!');
        }
    }

    /**
     * @throws RequestException
     */
    private function isConnectionOpenedOrFail(): void
    {
        if (!$this->isConnectionOpened) {
            throw new RequestException('The connection not open!');
        }
    }

    /**
     * @throws Exception
     */
    public function openConnection()
    {
        $this->isConnectionNotOpenedOrFail();
        Bus::getInstance()->connect();
        $this->connection = Bus::getInstance()->getConnection();
        $this->isConnectionOpened = true;
    }

    /**
     * @throws Exception
     */
    public function reopenConnection(): void
    {
        if ($this->isConnectionOpened) {
            $this->connection->close();
            $this->isConnectionOpened = false;
        }
        $this->openConnection();
    }

    public function closeConnection()
    {
        $this->connection->channel()->queue_delete($this->uuid, true, true);
        $this->connection->channel()->close();
        $this->connection->close();
        $this->isConnectionOpened = false;
    }

    /**
     * @throws Exception
     */
    public function waitReplyMessages()
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

    private function setResponseStatusCode()
    {
        if (
            !$this->response->getStartProcessingMessage()
            && !$this->response->getActionResultMessage()
            && !$this->response->getActionErrorMessage()
        ) {
            $this->response->setStatusCode(500);
            $this->response->setErrorMessage('Service not responding!');
        } elseif (
            !$this->response->getActionResultMessage()
            && $this->response->getStartProcessingMessage()
            && !$this->response->getActionErrorMessage()
        ) {
            $this->response->setStatusCode(500);
            $this->response->setErrorMessage(
                'The service responded, but did not process the request within the allotted time!'
            );
        } elseif (
            !$this->response->getActionResultMessage()
            && $this->response->getStartProcessingMessage()
            && $this->response->getActionErrorMessage()
        ) {
            $this->response->setStatusCode($this->response->getActionErrorMessage()->getCode());
            $this->response->setErrorMessage($this->response->getActionErrorMessage()->getMessage());
        } else {
            $this->response->setStatusCode(200);
        }
    }

    /**
     * Gets data from rabbit channel and sets it into response
     *
     * @throws Exception
     */
    private function collectRabbitMessageIntoResponse()
    {
        $result = null;
        try {
            $result = $this->connection->channel()->basic_get($this->uuid);
        } catch (\Exception $exception) {
            $this->connection->reconnect();
        }
        if (is_null($result)) {
            return;
        }
        $bodyArray = json_decode($result->getBody(), true);

        if (array_key_exists('type', $bodyArray)) {
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
            }
        }
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @param $key
     * @return array
     */
    public function getParameter($key): array
    {
        return $this->parameters[$key];
    }

    /**
     * @throws AMQPProtocolChannelExceptionAlias
     * @throws Exception
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
     * @throws AMQPProtocolChannelExceptionAlias
     * @throws Exception
     */
    public function send()
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
     * @throws AMQPProtocolChannelException
     * @throws ResponseException
     * @throws RequestException
     */
    private function authorizeService()
    {
        if ($this->isTokenExist()) {
            throw new RequestException('Token already exists! Service autorization is imposible!');
        }

        // Service Master Token (SMT) getting block
        $serviceMasterTokenRequest = new Request(
            $this->authServiceName,
            'Service',
            'login',
            [
                'service_name' => config('app.service_name'),
                'key' => config('app.service_key')
            ]
        );

        $serviceMasterTokenRequest->disableServiceAuthorization();
        $serviceMasterTokenResponse = $serviceMasterTokenRequest->call();
        $serviceMasterTokenResponse->throwActionErrorMessageIfExists();
        $serviceMasterToken = $serviceMasterTokenResponse->getActionResultMessage()->getData();

        // Service Service Token (SST) getting block
        $serviceServiceTokenRequest = new Request(
            $this->authServiceName,
            'Service',
            'loginToService',
            [
                'service_name' => $this->serviceName,
                'token' => $serviceMasterToken
            ]
        );

        $serviceServiceTokenRequest->disableServiceAuthorization();
        $serviceServiceTokenResponse = $serviceServiceTokenRequest->call();
        $serviceServiceTokenResponse->throwActionErrorMessageIfExists();
        $serviceServiceToken = $serviceServiceTokenResponse->getActionResultMessage()->getData();

        $this->setToken($serviceServiceToken);
    }

    /**
     * @param string $authServiceName
     */
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

}
