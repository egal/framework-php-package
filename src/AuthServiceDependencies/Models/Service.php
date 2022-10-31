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

        if (!$config) return null;

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

    final public static function actionLogin(string $service_name, string $key): string
    {
        $service = static::find($service_name);

        if (!$service || $service->getKey() !== $key)
            throw new LoginException('Incorrect key or service name!');

        Session::client()->mayOrFail('login', $service);

        $smt = new ServiceMasterToken();
        $smt->setSigningKey(config('app.service_key'));
        $smt->setSub(['name' => $service->name]);

        return $smt->generateJWT();
    }

    final public static function actionLoginToService(string $token, string $service_name): string
    {
        /** @var \Egal\Auth\Tokens\ServiceMasterToken $smt */
        $smt = ServiceMasterToken::fromJWT($token, config('app.service_key'));

        /** @var \Egal\AuthServiceDependencies\Models\Service $senderService */
        $senderService = static::find($smt->getSub()['name']);

        if (!$senderService) throw new ServiceNotFoundAuthException();
        Session::client()->mayOrFail('loginToService', $senderService);

        $recipientService = static::find($service_name);
        if (!$recipientService) throw new ServiceNotFoundAuthException();

        $sst = new ServiceServiceToken();
        $sst->setSigningKey($recipientService->key);
        $sst->setSub(['name' => $senderService->getName()]);

        return $sst->generateJWT();
    }

}
