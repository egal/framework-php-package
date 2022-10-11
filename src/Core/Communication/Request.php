<?php

declare(strict_types=1);

namespace Egal\Core\Communication;

use Carbon\Carbon;
use Egal\Core\ActionCaller\ActionCaller;
use Egal\Core\Bus\Bus;
use Egal\Core\Exceptions\RequestException;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\Message;
use Egal\Core\Session\Session;
use Illuminate\Support\Facades\Cache;

class Request extends ActionMessage
{

    /**
     * Exhibited after {@see \Egal\Core\Communication\Request::call()}
     */
    private Response $response;

    private string $authServiceName;

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

        $this->authServiceName = config('auth.auth_service_name') ?? 'auth';
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
        $response = new Response();
        $response->setActionMessage($this);
        $mustDieAt = microtime(true) + config('app.request.wait_reply_message_ttl');
        $bus = Bus::instance();

        if ($this->isServiceAuthorizationEnabled()) {
            $this->authorizeService();
        }

        $bus->startConsumeReplyMessages(static fn(Message $message) => $response->collectReplyMessage($message));
        $this->send();

        while (microtime(true) < $mustDieAt && !$response->isReplyMessagesCollected()) {
            $bus->consumeReplyMessages($mustDieAt - microtime(true));
        }

        $bus->stopConsumeReplyMessages();
        $response->collect();
        $this->response = $response;

        return $this->getResponse();
    }

    public function send(): void
    {
        $this->publish();
    }

    public function getServiceServiceToken(): string
    {
        $token = $this->getServiceServiceTokenFromCache();

        $tokenFromCachePayload = $token !== null
            ? explode('.', $token)[1]
            : null;
        $tokenFromCacheAliveUntil = $token !== null
            ? json_decode(base64_decode($tokenFromCachePayload), true)['alive_until']
            : null;

        if (!$token || Carbon::now('UTC') >= Carbon::parse($tokenFromCacheAliveUntil)) {
            $token = config('app.service_name') === $this->authServiceName
                ? $this->getItselfServiceServiceTokenFromAuth()
                : $this->getServiceServiceTokenFromAuthService();

            $this->putServiceServiceTokenToCache($token);
        }

        return $token;
    }

    private function authorizeService(): void
    {
        if ($this->isTokenExist()) {
            throw new RequestException('Token already exists! Service authorization is impossible!');
        }

        $token = $this->getServiceServiceToken();

        $this->setToken($token);
    }

    private function getItselfServiceServiceTokenFromAuth(): string
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

    private function getServiceServiceTokenFromAuthService(): string
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

    /**
     * @return array
     */
    private function getServiceServiceTokenFromCache(): ?string
    {
        $sstCacheKey = $this->getServiceServiceTokenCacheKey();

        return Cache::get($sstCacheKey);
    }

    private function putServiceServiceTokenToCache(string $token): void
    {
        $sstCacheKey = $this->getServiceServiceTokenCacheKey();

        Cache::put($sstCacheKey, $token);
    }

    private function getServiceServiceTokenCacheKey(): string
    {
        return 'sst.to.' . $this->serviceName;
    }

}
