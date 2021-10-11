<?php

declare(strict_types=1);

namespace Egal\AuthServiceDependencies\Models;

use Egal\Auth\Tokens\UserMasterToken;
use Egal\Auth\Tokens\UserServiceToken;
use Egal\AuthServiceDependencies\Exceptions\LoginException;
use Egal\AuthServiceDependencies\Exceptions\UserNotIdentifiedException;
use Egal\Model\Model;

abstract class User extends Model
{

    abstract protected function getRoles(): array;

    abstract protected function getPermissions(): array;

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName(): string
    {
        return $this->getKeyName();
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getAttribute($this->getAuthIdentifierName());
    }

    public static function actionLoginToService(string $token, string $serviceName): string
    {
        /** @var \Egal\Auth\Tokens\UserMasterToken $umt */
        $umt = UserMasterToken::fromJWT($token, config('app.service_key'));
        $umt->isAliveOrFail();

        /** @var \Egal\AuthServiceDependencies\Models\User $user */
        $user = static::query()->find($umt->getAuthIdentification());
        $service = Service::find($serviceName);

        if (!$user) {
            throw new UserNotIdentifiedException();
        }

        if (!$service) {
            throw new LoginException('Service not found!');
        }

        $ust = new UserServiceToken();
        $ust->setSigningKey($service->getKey());
        $ust->setAuthInformation($user->generateAuthInformation());

        return $ust->generateJWT();
    }

    protected function generateAuthInformation(): array
    {
        return array_merge(
            $this->fresh()->toArray(),
            [
                'auth_identification' => $this->getAuthIdentifier(),
                'roles' => $this->getRoles(),
                'permissions' => $this->getPermissions(),
            ]
        );
    }

}
