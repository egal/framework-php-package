<?php

declare(strict_types=1);

namespace Egal\AuthServiceDependencies\Models;

use Egal\Auth\Tokens\UserMasterRefreshToken;
use Egal\Auth\Tokens\UserMasterToken;
use Egal\Auth\Tokens\UserServiceToken;
use Egal\AuthServiceDependencies\Exceptions\LoginException;
use Egal\AuthServiceDependencies\Exceptions\UserNotIdentifiedException;
use Egal\Core\Session\Session;
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

    public static function actionLoginToService(string $token, string $service_name): string
    {
        Session::client()->mayOrFail('loginToService', static::class);

        /** @var \Egal\Auth\Tokens\UserMasterToken $umt */
        $umt = UserMasterToken::fromJWT($token, config('app.service_key'));
        $umt->isAliveOrFail();

        /** @var \Egal\AuthServiceDependencies\Models\User $user */
        $user = static::find($umt->getAuthIdentification());
        $service = Service::find($service_name);
        if (!$user) {
            throw new UserNotIdentifiedException();
        }

        if (!$service) {
            throw new LoginException('Service not found!');
        }

        $ust = new UserServiceToken();
        $ust->setSigningKey($service->getKey());
        $ust->setAuthInformation($user->generateAuthInformation());
        $ust->setTargetServiceName($service_name);

        return $ust->generateJWT();
    }

    public static function actionRefreshUserMasterToken(string $token): array
    {
        Session::client()->mayOrFail('refreshUserMasterToken', static::class);

        $oldUmrt = UserMasterRefreshToken::fromJWT($token, config('app.service_key'));
        $oldUmrt->isAliveOrFail();

        /** @var \Egal\AuthServiceDependencies\Models\User $user */
        $user = static::find($oldUmrt->getAuthIdentification());

        if (!$user) {
            throw new UserNotIdentifiedException();
        }

        $umt = new UserMasterToken();
        $umt->setSigningKey(config('app.service_key'));
        $umt->setAuthIdentification($oldUmrt->getAuthIdentification());

        $umrt = new UserMasterRefreshToken();
        $umrt->setSigningKey(config('app.service_key'));
        $umrt->setAuthIdentification($oldUmrt->getAuthIdentification());

        return [
            'user_master_token' => $umt->generateJWT(),
            'user_master_refresh_token' => $umrt->generateJWT(),
        ];
    }

    // TODO: Переделать наполнение токена на основе спецификации протокола
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
