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

    /**
     * @return string[]
     */
    abstract protected function getRoles(): array;

    /**
     * @return string[]
     */
    abstract protected function getPermissions(): array;

    final public static function actionLoginToService(string $token, string $service_name): string
    {
        $umt = UserMasterToken::fromJWT($token, config('app.service_key'));
        $model = new static();
        /** @var \Egal\AuthServiceDependencies\Models\User $user */
        $user = $model->query()->find($umt->getSub()['key']);

        Session::client()->mayOrFail('loginToService', $user);

        $service = Service::find($service_name);

        if (!$user) throw new UserNotIdentifiedException();
        if (!$service) throw new LoginException('Service not found!');

        $ust = new UserServiceToken();
        $ust->setSigningKey($service->getKey());
        $ust->setSub($user->generateUserServiceTokenSub());
        $ust->setAud($service_name);

        return $ust->generateJWT();
    }

    final public static function actionRefreshUserMasterToken(string $token): array
    {
        $oldUmrt = UserMasterRefreshToken::fromJWT($token, config('app.service_key'));
        $model = new static();
        $user = $model->query()->find($oldUmrt->getSub()[$model->primaryKey]);

        if (!$user) throw new UserNotIdentifiedException();

        Session::client()->mayOrFail('refreshUserMasterToken', $user);

        return $user->generateLoginResult();
    }

    protected function generateUserServiceTokenSub(): array
    {
        return array_merge(
            [
                'key' => $this->getKey(),
                'roles' => $this->getRoles(),
                'permissions' => $this->getPermissions(),
            ],
            $this->fresh()->toArray(),
        );
    }

    final protected function generateLoginResult(): array
    {
        $umt = new UserMasterToken();
        $umt->setSigningKey(config('app.service_key'));
        $umt->setSub(['key' => $this->getKey()]);

        $umrt = new UserMasterRefreshToken();
        $umrt->setSigningKey(config('app.service_key'));
        $umrt->setSub(['key' => $this->getKey()]);

        return [
            'user_master_token' => $umt->generateJWT(),
            'user_master_refresh_token' => $umrt->generateJWT()
        ];
    }

}
