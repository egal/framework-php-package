<?php

declare(strict_types=1);

namespace Egal\AuthServiceDependencies\Models;

use Egal\Auth\Tokens\ServiceMasterToken;
use Egal\Auth\Tokens\ServiceServiceToken;
use Egal\AuthServiceDependencies\Exceptions\LoginException;
use Egal\AuthServiceDependencies\Exceptions\ServiceNotFoundAuthException;
use Egal\Core\Session\Session;

class Service
{

    protected string $name;

    protected string $key;

    public static function find(string $name): ?self
    {
        $config = config('app.services.' . $name);

        if (!$config) {
            return null;
        }

        $result = new static();
        $result->name = $name;
        $result->key = $config['key'];

        return $result;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function actionLogin(string $service_name, string $key): string
    {
        Session::client()->mayOrFail('login', Service::class);

        $service = static::find($service_name);

        if (!$service || $service->getKey() !== $key) {
            throw new LoginException('Incorrect key or service name!');
        }

        $smt = new ServiceMasterToken();
        $smt->setSigningKey(config('app.service_key'));
        $smt->setAuthIdentification($service->name);

        return $smt->generateJWT();
    }

    public static function actionLoginToService(string $token, string $service_name): string
    {
        Session::client()->mayOrFail('loginToService', Service::class);

        /** @var \Egal\Auth\Tokens\ServiceMasterToken $smt */
        $smt = ServiceMasterToken::fromJWT($token, config('app.service_key'));
        $smt->isAliveOrFail();

        /** @var \Egal\AuthServiceDependencies\Models\Service $senderService */
        $senderService = static::find($smt->getAuthIdentification());

        if (!$senderService) {
            throw new ServiceNotFoundAuthException();
        }

        $recipientService = static::find($service_name);

        if (!$recipientService) {
            throw new ServiceNotFoundAuthException();
        }

        $sst = new ServiceServiceToken();
        $sst->setSigningKey($recipientService->key);
        $sst->setAuthInformation($senderService->generateAuthInformation());

        return $sst->generateJWT();
    }

    protected function generateAuthInformation(): array
    {
        return [
            'auth_identification' => $this->getName(),
            'service' => $this->getName(),
        ];
    }

}
